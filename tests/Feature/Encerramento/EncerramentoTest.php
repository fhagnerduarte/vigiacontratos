<?php

namespace Tests\Feature\Encerramento;

use App\Enums\EtapaEncerramento;
use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\StatusContrato;
use App\Enums\TipoEventoAlerta;
use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\Encerramento;
use App\Models\User;
use App\Services\EncerramentoService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class EncerramentoTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->admin = $this->createAdminUser();
        Queue::fake();
    }

    // ═══════════════════════════════════════════════════════════
    // ENUM EtapaEncerramento
    // ═══════════════════════════════════════════════════════════

    public function test_enum_etapa_encerramento_tem_6_cases(): void
    {
        $this->assertCount(6, EtapaEncerramento::cases());
    }

    public function test_enum_etapa_encerramento_labels_existem(): void
    {
        foreach (EtapaEncerramento::cases() as $etapa) {
            $this->assertNotEmpty($etapa->label());
            $this->assertNotEmpty($etapa->icone());
            $this->assertNotEmpty($etapa->cor());
            $this->assertIsInt($etapa->ordem());
        }
    }

    public function test_enum_etapa_encerramento_proxima(): void
    {
        $this->assertEquals(
            EtapaEncerramento::TermoProvisorio,
            EtapaEncerramento::VerificacaoFinanceira->proxima()
        );
        $this->assertEquals(
            EtapaEncerramento::AvaliacaoFiscal,
            EtapaEncerramento::TermoProvisorio->proxima()
        );
        $this->assertEquals(
            EtapaEncerramento::TermoDefinitivo,
            EtapaEncerramento::AvaliacaoFiscal->proxima()
        );
        $this->assertEquals(
            EtapaEncerramento::Quitacao,
            EtapaEncerramento::TermoDefinitivo->proxima()
        );
        $this->assertEquals(
            EtapaEncerramento::Encerrado,
            EtapaEncerramento::Quitacao->proxima()
        );
        $this->assertNull(EtapaEncerramento::Encerrado->proxima());
    }

    public function test_enum_etapa_encerramento_etapas_anteriores(): void
    {
        $this->assertCount(0, EtapaEncerramento::VerificacaoFinanceira->etapasAnteriores());
        $this->assertCount(3, EtapaEncerramento::TermoDefinitivo->etapasAnteriores());
        $this->assertCount(5, EtapaEncerramento::Encerrado->etapasAnteriores());
    }

    // ═══════════════════════════════════════════════════════════
    // MODEL Encerramento
    // ═══════════════════════════════════════════════════════════

    public function test_model_encerramento_pode_ser_criado(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $encerramento = Encerramento::create([
            'contrato_id' => $contrato->id,
            'etapa_atual' => EtapaEncerramento::VerificacaoFinanceira->value,
            'data_inicio' => now(),
        ]);

        $this->assertDatabaseHas('encerramentos', [
            'contrato_id' => $contrato->id,
            'etapa_atual' => 'verificacao_financeira',
        ]);
    }

    public function test_model_encerramento_cast_etapa(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $encerramento = Encerramento::factory()->create(['contrato_id' => $contrato->id]);

        $this->assertInstanceOf(EtapaEncerramento::class, $encerramento->etapa_atual);
    }

    public function test_model_encerramento_relacionamento_contrato(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $encerramento = Encerramento::factory()->create(['contrato_id' => $contrato->id]);

        $this->assertEquals($contrato->id, $encerramento->contrato->id);
    }

    public function test_model_encerramento_percentual_progresso(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $enc1 = Encerramento::factory()->etapa(EtapaEncerramento::VerificacaoFinanceira)->create(['contrato_id' => $contrato->id]);
        $this->assertEquals(0.0, $enc1->percentual_progresso);

        $enc1->update(['etapa_atual' => EtapaEncerramento::AvaliacaoFiscal->value]);
        $enc1->refresh();
        $this->assertGreaterThan(0, $enc1->percentual_progresso);

        $enc1->update(['etapa_atual' => EtapaEncerramento::Encerrado->value]);
        $enc1->refresh();
        $this->assertEquals(100.0, $enc1->percentual_progresso);
    }

    public function test_model_encerramento_contrato_relationship_inversa(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        Encerramento::factory()->create(['contrato_id' => $contrato->id]);

        $contrato->load('encerramento');
        $this->assertNotNull($contrato->encerramento);
        $this->assertInstanceOf(Encerramento::class, $contrato->encerramento);
    }

    // ═══════════════════════════════════════════════════════════
    // SERVICE: Iniciar
    // ═══════════════════════════════════════════════════════════

    public function test_service_iniciar_encerramento_contrato_vigente(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $enc = EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');

        $this->assertEquals(EtapaEncerramento::VerificacaoFinanceira, $enc->etapa_atual);
        $this->assertDatabaseHas('encerramentos', [
            'contrato_id' => $contrato->id,
            'etapa_atual' => 'verificacao_financeira',
        ]);
    }

    public function test_service_iniciar_encerramento_contrato_vencido(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $enc = EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');

        $this->assertEquals(EtapaEncerramento::VerificacaoFinanceira, $enc->etapa_atual);
    }

    public function test_service_iniciar_encerramento_contrato_encerrado_falha(): void
    {
        $contrato = Contrato::factory()->create(['status' => StatusContrato::Encerrado->value]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Apenas contratos vigentes ou vencidos');

        EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');
    }

    public function test_service_iniciar_encerramento_duplicado_falha(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ja iniciado');

        $contrato->refresh();
        $contrato->load('encerramento');
        EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');
    }

    // ═══════════════════════════════════════════════════════════
    // SERVICE: Verificacao Financeira
    // ═══════════════════════════════════════════════════════════

    public function test_service_verificar_financeiro_aprovado(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $enc = EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');

        $enc = EncerramentoService::verificarFinanceiro(
            $enc, true, 'Tudo regular', $this->admin, '127.0.0.1'
        );

        $this->assertEquals(EtapaEncerramento::TermoProvisorio, $enc->etapa_atual);
        $this->assertTrue($enc->verificacao_financeira_ok);
        $this->assertEquals('Tudo regular', $enc->verificacao_financeira_obs);
        $this->assertNotNull($enc->verificacao_financeira_em);
    }

    public function test_service_verificar_financeiro_com_ressalvas(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $enc = EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');

        $enc = EncerramentoService::verificarFinanceiro(
            $enc, false, 'Nota fiscal pendente', $this->admin, '127.0.0.1'
        );

        $this->assertEquals(EtapaEncerramento::TermoProvisorio, $enc->etapa_atual);
        $this->assertFalse($enc->verificacao_financeira_ok);
    }

    public function test_service_verificar_financeiro_etapa_incorreta_falha(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $enc = Encerramento::factory()->etapa(EtapaEncerramento::AvaliacaoFiscal)
            ->create(['contrato_id' => $contrato->id]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Etapa incorreta');

        EncerramentoService::verificarFinanceiro($enc, true, null, $this->admin, '127.0.0.1');
    }

    // ═══════════════════════════════════════════════════════════
    // SERVICE: Termo Provisorio
    // ═══════════════════════════════════════════════════════════

    public function test_service_registrar_termo_provisorio(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $enc = EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');
        $enc = EncerramentoService::verificarFinanceiro($enc, true, null, $this->admin, '127.0.0.1');

        $enc = EncerramentoService::registrarTermoProvisorio($enc, 15, $this->admin, '127.0.0.1');

        $this->assertEquals(EtapaEncerramento::AvaliacaoFiscal, $enc->etapa_atual);
        $this->assertEquals(15, $enc->termo_provisorio_prazo_dias);
        $this->assertNotNull($enc->termo_provisorio_em);
    }

    // ═══════════════════════════════════════════════════════════
    // SERVICE: Avaliacao Fiscal
    // ═══════════════════════════════════════════════════════════

    public function test_service_registrar_avaliacao_fiscal(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $enc = EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');
        $enc = EncerramentoService::verificarFinanceiro($enc, true, null, $this->admin, '127.0.0.1');
        $enc = EncerramentoService::registrarTermoProvisorio($enc, 15, $this->admin, '127.0.0.1');

        $enc = EncerramentoService::registrarAvaliacaoFiscal(
            $enc, 8.5, 'Bom desempenho', $this->admin, '127.0.0.1'
        );

        $this->assertEquals(EtapaEncerramento::TermoDefinitivo, $enc->etapa_atual);
        $this->assertEquals(8.5, (float) $enc->avaliacao_fiscal_nota);
        $this->assertEquals('Bom desempenho', $enc->avaliacao_fiscal_obs);
    }

    public function test_service_avaliacao_fiscal_nota_invalida_falha(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $enc = Encerramento::factory()->etapa(EtapaEncerramento::AvaliacaoFiscal)
            ->create(['contrato_id' => $contrato->id]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('entre 1 e 10');

        EncerramentoService::registrarAvaliacaoFiscal($enc, 11, null, $this->admin, '127.0.0.1');
    }

    public function test_service_avaliacao_fiscal_nota_zero_falha(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $enc = Encerramento::factory()->etapa(EtapaEncerramento::AvaliacaoFiscal)
            ->create(['contrato_id' => $contrato->id]);

        $this->expectException(\RuntimeException::class);

        EncerramentoService::registrarAvaliacaoFiscal($enc, 0, null, $this->admin, '127.0.0.1');
    }

    // ═══════════════════════════════════════════════════════════
    // SERVICE: Termo Definitivo
    // ═══════════════════════════════════════════════════════════

    public function test_service_registrar_termo_definitivo(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $enc = Encerramento::factory()->etapa(EtapaEncerramento::TermoDefinitivo)
            ->create(['contrato_id' => $contrato->id]);

        $enc = EncerramentoService::registrarTermoDefinitivo($enc, $this->admin, '127.0.0.1');

        $this->assertEquals(EtapaEncerramento::Quitacao, $enc->etapa_atual);
        $this->assertNotNull($enc->termo_definitivo_em);
    }

    // ═══════════════════════════════════════════════════════════
    // SERVICE: Quitacao (etapa final)
    // ═══════════════════════════════════════════════════════════

    public function test_service_registrar_quitacao_encerra_contrato(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $enc = Encerramento::factory()->etapa(EtapaEncerramento::Quitacao)
            ->create(['contrato_id' => $contrato->id]);

        $enc = EncerramentoService::registrarQuitacao(
            $enc, 'Todas as obrigacoes cumpridas', $this->admin, '127.0.0.1'
        );

        $contrato->refresh();

        $this->assertEquals(EtapaEncerramento::Encerrado, $enc->etapa_atual);
        $this->assertEquals(StatusContrato::Encerrado, $contrato->status);
        $this->assertFalse($contrato->is_irregular);
        $this->assertNotNull($enc->data_encerramento_efetivo);
    }

    public function test_service_quitacao_resolve_todos_alertas(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        // Criar alertas pendentes
        Alerta::factory()->count(3)->create([
            'contrato_id' => $contrato->id,
            'status' => StatusAlerta::Pendente->value,
        ]);

        $enc = Encerramento::factory()->etapa(EtapaEncerramento::Quitacao)
            ->create(['contrato_id' => $contrato->id]);

        EncerramentoService::registrarQuitacao(
            $enc, null, $this->admin, '127.0.0.1'
        );

        $alertasPendentes = Alerta::where('contrato_id', $contrato->id)
            ->where('status', StatusAlerta::Pendente->value)
            ->count();

        $alertasResolvidos = Alerta::where('contrato_id', $contrato->id)
            ->where('status', StatusAlerta::Resolvido->value)
            ->count();

        $this->assertEquals(0, $alertasPendentes);
        $this->assertEquals(3, $alertasResolvidos);
    }

    public function test_service_quitacao_contrato_vencido_irregular_fica_regular(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $enc = Encerramento::factory()->etapa(EtapaEncerramento::Quitacao)
            ->create(['contrato_id' => $contrato->id]);

        EncerramentoService::registrarQuitacao($enc, null, $this->admin, '127.0.0.1');

        $contrato->refresh();

        $this->assertEquals(StatusContrato::Encerrado, $contrato->status);
        $this->assertFalse($contrato->is_irregular);
    }

    // ═══════════════════════════════════════════════════════════
    // WORKFLOW COMPLETO
    // ═══════════════════════════════════════════════════════════

    public function test_workflow_completo_6_etapas(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        // Etapa 0: Iniciar
        $enc = EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');
        $this->assertEquals(EtapaEncerramento::VerificacaoFinanceira, $enc->etapa_atual);

        // Etapa 1: Verificacao Financeira
        $enc = EncerramentoService::verificarFinanceiro($enc, true, 'OK', $this->admin, '127.0.0.1');
        $this->assertEquals(EtapaEncerramento::TermoProvisorio, $enc->etapa_atual);

        // Etapa 2: Termo Provisorio
        $enc = EncerramentoService::registrarTermoProvisorio($enc, 15, $this->admin, '127.0.0.1');
        $this->assertEquals(EtapaEncerramento::AvaliacaoFiscal, $enc->etapa_atual);

        // Etapa 3: Avaliacao Fiscal
        $enc = EncerramentoService::registrarAvaliacaoFiscal($enc, 9.0, 'Excelente', $this->admin, '127.0.0.1');
        $this->assertEquals(EtapaEncerramento::TermoDefinitivo, $enc->etapa_atual);

        // Etapa 4: Termo Definitivo
        $enc = EncerramentoService::registrarTermoDefinitivo($enc, $this->admin, '127.0.0.1');
        $this->assertEquals(EtapaEncerramento::Quitacao, $enc->etapa_atual);

        // Etapa 5: Quitacao
        $enc = EncerramentoService::registrarQuitacao($enc, 'Finalizado', $this->admin, '127.0.0.1');
        $this->assertEquals(EtapaEncerramento::Encerrado, $enc->etapa_atual);

        // Verifica estado final
        $contrato->refresh();
        $this->assertEquals(StatusContrato::Encerrado, $contrato->status);
        $this->assertEquals(100.0, $enc->percentual_progresso);
        $this->assertTrue($enc->etapa_concluida);
    }

    // ═══════════════════════════════════════════════════════════
    // CONTROLLER: Rotas e Permissoes
    // ═══════════════════════════════════════════════════════════

    public function test_controller_show_encerramento_requer_autenticacao(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $this->get(route('tenant.contratos.encerramento.show', $contrato))
            ->assertRedirect();
    }

    public function test_controller_show_encerramento_pagina_carrega(): void
    {
        $this->actingAs($this->admin);
        $contrato = Contrato::factory()->vigente()->create();

        $response = $this->get(route('tenant.contratos.encerramento.show', $contrato));

        $response->assertStatus(200);
        $response->assertSee('Iniciar Processo de Encerramento');
    }

    public function test_controller_iniciar_encerramento(): void
    {
        $this->actingAs($this->admin);
        $contrato = Contrato::factory()->vigente()->create();

        $response = $this->post(route('tenant.contratos.encerramento.iniciar', $contrato));

        $response->assertRedirect(route('tenant.contratos.encerramento.show', $contrato));
        $this->assertDatabaseHas('encerramentos', [
            'contrato_id' => $contrato->id,
        ]);
    }

    public function test_controller_verificar_financeiro(): void
    {
        $this->actingAs($this->admin);
        $contrato = Contrato::factory()->vigente()->create();
        EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');

        $response = $this->post(
            route('tenant.contratos.encerramento.verificar-financeiro', $contrato),
            [
                'verificacao_financeira_ok' => 1,
                'verificacao_financeira_obs' => 'Tudo certo',
            ]
        );

        $response->assertRedirect(route('tenant.contratos.encerramento.show', $contrato));
        $this->assertDatabaseHas('encerramentos', [
            'contrato_id' => $contrato->id,
            'etapa_atual' => 'termo_provisorio',
        ]);
    }

    public function test_controller_termo_provisorio(): void
    {
        $this->actingAs($this->admin);
        $contrato = Contrato::factory()->vigente()->create();
        $enc = Encerramento::factory()->etapa(EtapaEncerramento::TermoProvisorio)
            ->create(['contrato_id' => $contrato->id]);

        $response = $this->post(
            route('tenant.contratos.encerramento.termo-provisorio', $contrato),
            ['termo_provisorio_prazo_dias' => 15]
        );

        $response->assertRedirect(route('tenant.contratos.encerramento.show', $contrato));
        $this->assertDatabaseHas('encerramentos', [
            'contrato_id' => $contrato->id,
            'etapa_atual' => 'avaliacao_fiscal',
        ]);
    }

    public function test_controller_avaliacao_fiscal(): void
    {
        $this->actingAs($this->admin);
        $contrato = Contrato::factory()->vigente()->create();
        Encerramento::factory()->etapa(EtapaEncerramento::AvaliacaoFiscal)
            ->create(['contrato_id' => $contrato->id]);

        $response = $this->post(
            route('tenant.contratos.encerramento.avaliacao-fiscal', $contrato),
            [
                'avaliacao_fiscal_nota' => 8.5,
                'avaliacao_fiscal_obs' => 'Bom trabalho',
            ]
        );

        $response->assertRedirect(route('tenant.contratos.encerramento.show', $contrato));
        $this->assertDatabaseHas('encerramentos', [
            'contrato_id' => $contrato->id,
            'etapa_atual' => 'termo_definitivo',
        ]);
    }

    public function test_controller_termo_definitivo(): void
    {
        $this->actingAs($this->admin);
        $contrato = Contrato::factory()->vigente()->create();
        Encerramento::factory()->etapa(EtapaEncerramento::TermoDefinitivo)
            ->create(['contrato_id' => $contrato->id]);

        $response = $this->post(
            route('tenant.contratos.encerramento.termo-definitivo', $contrato)
        );

        $response->assertRedirect(route('tenant.contratos.encerramento.show', $contrato));
        $this->assertDatabaseHas('encerramentos', [
            'contrato_id' => $contrato->id,
            'etapa_atual' => 'quitacao',
        ]);
    }

    public function test_controller_quitacao_encerra_contrato(): void
    {
        $this->actingAs($this->admin);
        $contrato = Contrato::factory()->vigente()->create();
        Encerramento::factory()->etapa(EtapaEncerramento::Quitacao)
            ->create(['contrato_id' => $contrato->id]);

        $response = $this->post(
            route('tenant.contratos.encerramento.quitacao', $contrato),
            ['quitacao_obs' => 'Finalizado com sucesso']
        );

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $contrato->refresh();
        $this->assertEquals(StatusContrato::Encerrado, $contrato->status);
    }

    public function test_controller_show_contrato_encerrado_mostra_historico(): void
    {
        $this->actingAs($this->admin);
        $contrato = Contrato::factory()->create(['status' => StatusContrato::Encerrado->value]);
        Encerramento::factory()->etapa(EtapaEncerramento::Encerrado)
            ->create([
                'contrato_id' => $contrato->id,
                'data_encerramento_efetivo' => now()->toDateString(),
            ]);

        $response = $this->get(route('tenant.contratos.encerramento.show', $contrato));

        $response->assertStatus(200);
        $response->assertSee('Historico do Encerramento');
    }

    // ═══════════════════════════════════════════════════════════
    // AUDITORIA
    // ═══════════════════════════════════════════════════════════

    public function test_auditoria_registrada_em_cada_etapa(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $enc = EncerramentoService::iniciar($contrato, $this->admin, '127.0.0.1');
        $enc = EncerramentoService::verificarFinanceiro($enc, true, null, $this->admin, '127.0.0.1');

        $this->assertDatabaseHas('historico_alteracoes', [
            'auditable_type' => Contrato::class,
            'auditable_id' => $contrato->id,
            'campo_alterado' => 'encerramento_iniciado',
        ]);

        $this->assertDatabaseHas('historico_alteracoes', [
            'auditable_type' => Contrato::class,
            'auditable_id' => $contrato->id,
            'campo_alterado' => 'encerramento_verificacao_financeira',
        ]);
    }
}
