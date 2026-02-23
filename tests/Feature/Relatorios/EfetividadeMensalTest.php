<?php

namespace Tests\Feature\Relatorios;

use App\Enums\StatusAditivo;
use App\Enums\StatusContrato;
use App\Enums\TipoAditivo;
use App\Models\Aditivo;
use App\Models\Contrato;
use App\Models\Secretaria;
use App\Models\User;
use App\Services\RelatorioService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class EfetividadeMensalTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->admin = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
    }

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);
    }

    // ─── ACESSO ──────────────────────────────────

    public function test_acesso_tela_efetividade_com_permissao(): void
    {
        $response = $this->actAsAdmin()
            ->get(route('tenant.relatorios.efetividade-mensal'));

        $response->assertStatus(200);
        $response->assertSee('Relatorio de Efetividade Mensal');
    }

    public function test_acesso_negado_sem_permissao(): void
    {
        $userSemPermissao = User::factory()->create([
            'role_id' => null,
            'is_ativo' => true,
            'mfa_secret' => null,
        ]);

        $response = $this->actingAs($userSemPermissao)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.relatorios.efetividade-mensal'));

        $response->assertStatus(403);
    }

    // ─── SERVICE: CALCULO DE EFETIVIDADE ──────────
    // Uso meses especificos no passado (Jan-Jun 2022) para isolamento de dados

    public function test_contrato_regularizado_a_tempo(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => '2022-01-20',
            'data_inicio' => '2021-01-20',
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Prazo,
            'status' => StatusAditivo::Vigente,
            'nova_data_fim' => '2023-01-20',
            'created_at' => '2022-01-10', // 10 dias ANTES do vencimento
        ]);

        $dados = RelatorioService::dadosEfetividadeMensal(['mes' => 1, 'ano' => 2022]);

        $this->assertEquals(1, $dados['resumo']['total_elegiveis']);
        $this->assertEquals(1, $dados['resumo']['regularizados_a_tempo']);
        $this->assertEquals(0, $dados['resumo']['vencidos_sem_acao']);
    }

    public function test_contrato_vencido_sem_acao(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vencido,
            'data_fim' => '2022-02-15',
            'data_inicio' => '2021-02-15',
            'is_irregular' => true,
        ]);

        $dados = RelatorioService::dadosEfetividadeMensal(['mes' => 2, 'ano' => 2022]);

        $this->assertEquals(1, $dados['resumo']['total_elegiveis']);
        $this->assertEquals(0, $dados['resumo']['regularizados_a_tempo']);
        $this->assertEquals(1, $dados['resumo']['vencidos_sem_acao']);
    }

    public function test_contrato_regularizado_retroativamente(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => '2022-03-10',
            'data_inicio' => '2021-03-10',
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Prazo,
            'status' => StatusAditivo::Vigente,
            'nova_data_fim' => '2023-03-10',
            'justificativa_retroativa' => 'Justificativa de retroatividade com mais de cinquenta caracteres para atender a regra minima',
            'created_at' => '2022-03-15', // 5 dias DEPOIS do vencimento
        ]);

        $dados = RelatorioService::dadosEfetividadeMensal(['mes' => 3, 'ano' => 2022]);

        $this->assertEquals(1, $dados['resumo']['total_elegiveis']);
        $this->assertEquals(0, $dados['resumo']['regularizados_a_tempo']);
        $this->assertEquals(1, $dados['resumo']['regularizados_retroativos']);
    }

    public function test_taxa_efetividade_100_porcento(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $contrato = Contrato::factory()->create([
                'status' => StatusContrato::Vigente,
                'data_fim' => '2022-04-' . str_pad(10 + $i, 2, '0', STR_PAD_LEFT),
                'data_inicio' => '2021-04-10',
            ]);

            Aditivo::factory()->create([
                'contrato_id' => $contrato->id,
                'tipo' => TipoAditivo::Prazo,
                'status' => StatusAditivo::Vigente,
                'nova_data_fim' => '2023-04-10',
                'created_at' => '2022-04-01', // antes do vencimento
            ]);
        }

        $dados = RelatorioService::dadosEfetividadeMensal(['mes' => 4, 'ano' => 2022]);

        $this->assertEquals(3, $dados['resumo']['total_elegiveis']);
        $this->assertEquals(100.0, $dados['resumo']['taxa_efetividade']);
    }

    public function test_taxa_efetividade_zero_porcento(): void
    {
        for ($i = 0; $i < 2; $i++) {
            Contrato::factory()->create([
                'status' => StatusContrato::Vencido,
                'data_fim' => '2022-05-' . str_pad(10 + $i, 2, '0', STR_PAD_LEFT),
                'data_inicio' => '2021-05-10',
                'is_irregular' => true,
            ]);
        }

        $dados = RelatorioService::dadosEfetividadeMensal(['mes' => 5, 'ano' => 2022]);

        $this->assertEquals(2, $dados['resumo']['total_elegiveis']);
        $this->assertEquals(0.0, $dados['resumo']['taxa_efetividade']);
    }

    public function test_taxa_efetividade_parcial(): void
    {
        // 1 regularizado
        $contratoReg = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => '2022-06-15',
            'data_inicio' => '2021-06-15',
        ]);
        Aditivo::factory()->create([
            'contrato_id' => $contratoReg->id,
            'tipo' => TipoAditivo::Prazo,
            'status' => StatusAditivo::Vigente,
            'nova_data_fim' => '2023-06-15',
            'created_at' => '2022-06-01', // antes do vencimento
        ]);

        // 1 vencido
        Contrato::factory()->create([
            'status' => StatusContrato::Vencido,
            'data_fim' => '2022-06-20',
            'data_inicio' => '2021-06-20',
            'is_irregular' => true,
        ]);

        $dados = RelatorioService::dadosEfetividadeMensal(['mes' => 6, 'ano' => 2022]);

        $this->assertEquals(2, $dados['resumo']['total_elegiveis']);
        $this->assertEquals(50.0, $dados['resumo']['taxa_efetividade']);
    }

    public function test_tempo_medio_antecipacao(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => '2022-07-25',
            'data_inicio' => '2021-07-25',
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Prazo,
            'status' => StatusAditivo::Vigente,
            'nova_data_fim' => '2023-07-25',
            'created_at' => '2022-07-10', // 15 dias antes de data_fim
        ]);

        $dados = RelatorioService::dadosEfetividadeMensal(['mes' => 7, 'ano' => 2022]);

        $this->assertEquals(15.0, $dados['resumo']['tempo_medio_antecipacao']);
    }

    // ─── FILTRO POR SECRETARIA ──────────────────

    public function test_filtro_por_secretaria(): void
    {
        $sec1 = Secretaria::factory()->create(['nome' => 'Secretaria Saude']);
        $sec2 = Secretaria::factory()->create(['nome' => 'Secretaria Educacao']);

        Contrato::factory()->create([
            'secretaria_id' => $sec1->id,
            'status' => StatusContrato::Vencido,
            'data_fim' => '2022-08-15',
            'data_inicio' => '2021-08-15',
            'is_irregular' => true,
        ]);

        Contrato::factory()->create([
            'secretaria_id' => $sec2->id,
            'status' => StatusContrato::Vencido,
            'data_fim' => '2022-08-20',
            'data_inicio' => '2021-08-20',
            'is_irregular' => true,
        ]);

        $dados = RelatorioService::dadosEfetividadeMensal([
            'mes' => 8,
            'ano' => 2022,
            'secretaria_id' => $sec1->id,
        ]);

        $this->assertEquals(1, $dados['resumo']['total_elegiveis']);
        $this->assertEquals('Secretaria Saude', $dados['filtros']['secretaria']);
    }

    // ─── GERACAO PDF ──────────────────────────────

    public function test_geracao_pdf_efetividade(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vencido,
            'data_fim' => '2022-09-10',
            'data_inicio' => '2021-09-10',
            'is_irregular' => true,
        ]);

        $response = $this->actAsAdmin()
            ->post(route('tenant.relatorios.efetividade-mensal.pdf'), [
                'mes' => 9,
                'ano' => 2022,
            ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ─── EXPORTACAO EXCEL ─────────────────────────

    public function test_exportacao_excel_efetividade(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vencido,
            'data_fim' => '2022-10-10',
            'data_inicio' => '2021-10-10',
            'is_irregular' => true,
        ]);

        $response = $this->actAsAdmin()
            ->get(route('tenant.relatorios.efetividade-mensal.excel', [
                'mes' => 10,
                'ano' => 2022,
            ]));

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'spreadsheet',
            $response->headers->get('content-type')
        );
    }

    // ─── MES SEM DADOS ───────────────────────────

    public function test_mes_sem_dados_retorna_zerado(): void
    {
        $dados = RelatorioService::dadosEfetividadeMensal([
            'mes' => 1,
            'ano' => 2021,
        ]);

        $this->assertEquals(0, $dados['resumo']['total_elegiveis']);
        $this->assertEquals(0, $dados['resumo']['regularizados_a_tempo']);
        $this->assertEquals(0, $dados['resumo']['vencidos_sem_acao']);
        $this->assertEquals(0, $dados['resumo']['regularizados_retroativos']);
        $this->assertEquals(0.0, $dados['resumo']['taxa_efetividade']);
        $this->assertEquals(0.0, $dados['resumo']['tempo_medio_antecipacao']);
        $this->assertEmpty($dados['contratos']);
        $this->assertEmpty($dados['por_secretaria']);
    }

    // ─── ADITIVO CANCELADO NAO CONTA ────────────

    public function test_aditivo_cancelado_nao_conta_como_regularizado(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => '2022-11-20',
            'data_inicio' => '2021-11-20',
        ]);

        Aditivo::factory()->cancelado()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Prazo,
            'nova_data_fim' => '2023-11-20',
            'created_at' => '2022-11-01',
        ]);

        $dados = RelatorioService::dadosEfetividadeMensal(['mes' => 11, 'ano' => 2022]);

        $this->assertEquals(1, $dados['resumo']['total_elegiveis']);
        $this->assertEquals(0, $dados['resumo']['regularizados_a_tempo']);
        $this->assertEquals(1, $dados['resumo']['vencidos_sem_acao']);
    }

    // ─── ADITIVO PRAZO_E_VALOR CONTA ────────────

    public function test_aditivo_prazo_e_valor_conta_como_regularizado(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => '2022-12-20',
            'data_inicio' => '2021-12-20',
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::PrazoEValor,
            'status' => StatusAditivo::Vigente,
            'nova_data_fim' => '2023-12-20',
            'valor_acrescimo' => 50000,
            'created_at' => '2022-12-01', // antes do vencimento
        ]);

        $dados = RelatorioService::dadosEfetividadeMensal(['mes' => 12, 'ano' => 2022]);

        $this->assertEquals(1, $dados['resumo']['regularizados_a_tempo']);
    }

    // ─── DADOS POR SECRETARIA ──────────────────

    public function test_detalhamento_por_secretaria(): void
    {
        $sec1 = Secretaria::factory()->create(['nome' => 'Sec A']);
        $sec2 = Secretaria::factory()->create(['nome' => 'Sec B']);

        Contrato::factory()->count(2)->create([
            'secretaria_id' => $sec1->id,
            'status' => StatusContrato::Vencido,
            'data_fim' => '2023-01-15',
            'data_inicio' => '2022-01-15',
            'is_irregular' => true,
        ]);

        Contrato::factory()->create([
            'secretaria_id' => $sec2->id,
            'status' => StatusContrato::Vencido,
            'data_fim' => '2023-01-20',
            'data_inicio' => '2022-01-20',
            'is_irregular' => true,
        ]);

        $dados = RelatorioService::dadosEfetividadeMensal(['mes' => 1, 'ano' => 2023]);

        $this->assertCount(2, $dados['por_secretaria']);
        $secA = collect($dados['por_secretaria'])->where('secretaria', 'Sec A')->first();
        $this->assertEquals(2, $secA['total']);
        $this->assertEquals(2, $secA['vencidos']);
    }

    // ─── TELA WEB COM DADOS ─────────────────────

    public function test_tela_com_filtros_mostra_indicadores(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vencido,
            'data_fim' => '2023-02-10',
            'data_inicio' => '2022-02-10',
            'is_irregular' => true,
        ]);

        $response = $this->actAsAdmin()
            ->get(route('tenant.relatorios.efetividade-mensal', [
                'mes' => 2,
                'ano' => 2023,
            ]));

        $response->assertStatus(200);
        $response->assertSee('Contratos Monitorados');
        $response->assertSee('Regularizados a Tempo');
        $response->assertSee('Vencidos sem Acao');
        $response->assertSee('Taxa de Efetividade');
    }
}
