<?php

namespace Tests\Feature\OcorrenciaRelatorioFiscal;

use App\Enums\StatusAlerta;
use App\Enums\StatusContrato;
use App\Enums\TipoEventoAlerta;
use App\Enums\TipoOcorrencia;
use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Ocorrencia;
use App\Models\RelatorioFiscal;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use App\Services\OcorrenciaService;
use App\Services\RelatorioFiscalService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class OcorrenciaRelatorioFiscalTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        Queue::fake();
        $this->seedBaseData();
    }

    private function criarContratoComFiscal(array $overrides = []): array
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createAdminUser();
        $user->secretarias()->attach($secretaria->id);
        $this->actingAs($user);

        $contrato = Contrato::factory()->create(array_merge([
            'secretaria_id' => $secretaria->id,
            'status' => StatusContrato::Vigente->value,
            'data_inicio' => now()->subMonths(6),
            'data_fim' => now()->addMonths(6),
        ], $overrides));

        $servidor = Servidor::factory()->create(['is_ativo' => true]);
        $fiscal = Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'servidor_id' => $servidor->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
            'data_inicio' => now()->subMonths(6),
        ]);

        return [$contrato, $fiscal, $user, $secretaria];
    }

    // ============================================================
    // ENUM: TipoOcorrencia
    // ============================================================

    public function test_tipo_ocorrencia_possui_6_cases(): void
    {
        $this->assertCount(6, TipoOcorrencia::cases());
    }

    public function test_tipo_ocorrencia_possui_label(): void
    {
        $this->assertEquals('Atraso na Execucao', TipoOcorrencia::Atraso->label());
        $this->assertEquals('Inconformidade Contratual', TipoOcorrencia::Inconformidade->label());
        $this->assertEquals('Notificacao ao Contratado', TipoOcorrencia::Notificacao->label());
        $this->assertEquals('Medicao/Avaliacao', TipoOcorrencia::Medicao->label());
        $this->assertEquals('Aplicacao de Penalidade', TipoOcorrencia::Penalidade->label());
        $this->assertEquals('Outros', TipoOcorrencia::Outros->label());
    }

    public function test_tipo_ocorrencia_possui_cor_e_icone(): void
    {
        foreach (TipoOcorrencia::cases() as $tipo) {
            $this->assertNotEmpty($tipo->cor());
            $this->assertNotEmpty($tipo->icone());
        }
    }

    // ============================================================
    // MODEL: Ocorrencia
    // ============================================================

    public function test_ocorrencia_pertence_a_contrato(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $ocorrencia = Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'registrado_por' => $user->id,
        ]);

        $this->assertEquals($contrato->id, $ocorrencia->contrato->id);
        $this->assertEquals($fiscal->id, $ocorrencia->fiscal->id);
        $this->assertEquals($user->id, $ocorrencia->registradoPor->id);
    }

    public function test_ocorrencia_cast_tipo_ocorrencia_enum(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $ocorrencia = Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'tipo_ocorrencia' => TipoOcorrencia::Atraso->value,
            'registrado_por' => $user->id,
        ]);

        $this->assertInstanceOf(TipoOcorrencia::class, $ocorrencia->tipo_ocorrencia);
        $this->assertEquals(TipoOcorrencia::Atraso, $ocorrencia->tipo_ocorrencia);
    }

    public function test_ocorrencia_scope_pendentes(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'resolvida' => false,
            'registrado_por' => $user->id,
        ]);
        Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'resolvida' => true,
            'resolvida_em' => now(),
            'resolvida_por' => $user->id,
            'registrado_por' => $user->id,
        ]);

        $this->assertEquals(1, Ocorrencia::where('contrato_id', $contrato->id)->pendentes()->count());
        $this->assertEquals(1, Ocorrencia::where('contrato_id', $contrato->id)->resolvidas()->count());
    }

    public function test_ocorrencia_scope_vencidas(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'resolvida' => false,
            'prazo_providencia' => now()->subDays(5),
            'registrado_por' => $user->id,
        ]);
        Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'resolvida' => false,
            'prazo_providencia' => now()->addDays(10),
            'registrado_por' => $user->id,
        ]);

        $this->assertEquals(1, Ocorrencia::where('contrato_id', $contrato->id)->vencidas()->count());
    }

    // ============================================================
    // MODEL: RelatorioFiscal
    // ============================================================

    public function test_relatorio_fiscal_pertence_a_contrato(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $relatorio = RelatorioFiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'registrado_por' => $user->id,
        ]);

        $this->assertEquals($contrato->id, $relatorio->contrato->id);
        $this->assertEquals($fiscal->id, $relatorio->fiscal->id);
    }

    public function test_relatorio_fiscal_scope_conformes(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        RelatorioFiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'conformidade_geral' => true,
            'registrado_por' => $user->id,
        ]);
        RelatorioFiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'conformidade_geral' => false,
            'registrado_por' => $user->id,
        ]);

        $this->assertEquals(1, RelatorioFiscal::where('contrato_id', $contrato->id)->conformes()->count());
        $this->assertEquals(1, RelatorioFiscal::where('contrato_id', $contrato->id)->naoConformes()->count());
    }

    // ============================================================
    // SERVICE: OcorrenciaService
    // ============================================================

    public function test_ocorrencia_service_registrar_cria_ocorrencia(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $resultado = OcorrenciaService::registrar($contrato, [
            'data_ocorrencia' => now()->format('Y-m-d'),
            'tipo_ocorrencia' => TipoOcorrencia::Atraso->value,
            'descricao' => 'Atraso de 15 dias na entrega do material contratado.',
            'providencia' => 'Notificar fornecedor.',
            'prazo_providencia' => now()->addDays(10)->format('Y-m-d'),
        ], $user);

        $this->assertInstanceOf(Ocorrencia::class, $resultado['ocorrencia']);
        $this->assertEquals($contrato->id, $resultado['ocorrencia']->contrato_id);
        $this->assertEquals($fiscal->id, $resultado['ocorrencia']->fiscal_id);
        $this->assertEquals(TipoOcorrencia::Atraso, $resultado['ocorrencia']->tipo_ocorrencia);
        $this->assertIsInt($resultado['vencidas_count']);
    }

    public function test_ocorrencia_service_resolver(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $ocorrencia = Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'resolvida' => false,
            'registrado_por' => $user->id,
        ]);

        $resolvida = OcorrenciaService::resolver($ocorrencia, $user, 'Providencia tomada com sucesso.');

        $this->assertTrue($resolvida->resolvida);
        $this->assertNotNull($resolvida->resolvida_em);
        $this->assertEquals($user->id, $resolvida->resolvida_por);
    }

    public function test_ocorrencia_service_resumo(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        Ocorrencia::factory()->atraso()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'resolvida' => false,
            'registrado_por' => $user->id,
        ]);
        Ocorrencia::factory()->inconformidade()->resolvida()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'registrado_por' => $user->id,
        ]);

        $resumo = OcorrenciaService::resumo($contrato);

        $this->assertEquals(2, $resumo['total']);
        $this->assertEquals(1, $resumo['pendentes']);
        $this->assertEquals(1, $resumo['resolvidas']);
        $this->assertArrayHasKey('por_tipo', $resumo);
        $this->assertArrayHasKey('atraso', $resumo['por_tipo']);
        $this->assertArrayHasKey('inconformidade', $resumo['por_tipo']);
    }

    // ============================================================
    // SERVICE: RelatorioFiscalService
    // ============================================================

    public function test_relatorio_fiscal_service_registrar(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $resultado = RelatorioFiscalService::registrar($contrato, [
            'periodo_inicio' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'periodo_fim' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            'descricao_atividades' => 'Acompanhamento das atividades contratuais durante o periodo.',
            'conformidade_geral' => true,
            'nota_desempenho' => 8,
        ], $user);

        $this->assertInstanceOf(RelatorioFiscal::class, $resultado['relatorio']);
        $this->assertEquals($contrato->id, $resultado['relatorio']->contrato_id);
        $this->assertTrue($resultado['relatorio']->conformidade_geral);
        $this->assertEquals(8, $resultado['relatorio']->nota_desempenho);
    }

    public function test_relatorio_fiscal_service_atualiza_data_ultimo_relatorio(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $periodoFim = now()->subMonth()->endOfMonth()->format('Y-m-d');

        RelatorioFiscalService::registrar($contrato, [
            'periodo_inicio' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'periodo_fim' => $periodoFim,
            'descricao_atividades' => 'Verificacao de conformidade e acompanhamento in loco.',
            'conformidade_geral' => true,
        ], $user);

        $fiscal->refresh();
        $this->assertEquals($periodoFim, $fiscal->data_ultimo_relatorio->format('Y-m-d'));
    }

    public function test_relatorio_fiscal_service_resolve_alerta_fiscal_sem_relatorio(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        // Cria alerta de FiscalSemRelatorio pendente
        Alerta::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::FiscalSemRelatorio->value,
            'mensagem' => 'Fiscal sem relatorio ha mais de 60 dias.',
            'status' => 'pendente',
        ]);

        $resultado = RelatorioFiscalService::registrar($contrato, [
            'periodo_inicio' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'periodo_fim' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            'descricao_atividades' => 'Relatorio de acompanhamento fiscal do periodo.',
            'conformidade_geral' => true,
        ], $user);

        $this->assertTrue($resultado['alerta_resolvido']);

        $alerta = Alerta::where('contrato_id', $contrato->id)
            ->where('tipo_evento', TipoEventoAlerta::FiscalSemRelatorio->value)
            ->first();
        $this->assertEquals(StatusAlerta::Resolvido, $alerta->status);
    }

    public function test_relatorio_fiscal_service_resumo(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        RelatorioFiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'conformidade_geral' => true,
            'nota_desempenho' => 8,
            'registrado_por' => $user->id,
        ]);
        RelatorioFiscal::factory()->naoConforme()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'registrado_por' => $user->id,
        ]);

        $resumo = RelatorioFiscalService::resumo($contrato);

        $this->assertEquals(2, $resumo['total']);
        $this->assertEquals(1, $resumo['conformes']);
        $this->assertEquals(1, $resumo['nao_conformes']);
        $this->assertNotNull($resumo['nota_media']);
    }

    // ============================================================
    // CONTROLLER: OcorrenciasController
    // ============================================================

    public function test_controller_store_ocorrencia_com_sucesso(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $response = $this->post(route('tenant.contratos.ocorrencias.store', $contrato), [
            'tipo_ocorrencia' => TipoOcorrencia::Notificacao->value,
            'data_ocorrencia' => now()->format('Y-m-d'),
            'descricao' => 'Notificacao formal ao contratado sobre irregularidade encontrada.',
        ]);

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('success');

        $ocorrencia = Ocorrencia::where('contrato_id', $contrato->id)
            ->where('tipo_ocorrencia', 'notificacao')
            ->first();
        $this->assertNotNull($ocorrencia, 'Ocorrencia deveria ter sido criada via controller');
    }

    public function test_controller_store_ocorrencia_valida_campos_obrigatorios(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $response = $this->post(route('tenant.contratos.ocorrencias.store', $contrato), []);

        $response->assertSessionHasErrors(['tipo_ocorrencia', 'data_ocorrencia', 'descricao']);
    }

    public function test_controller_resolver_ocorrencia(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $ocorrencia = Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'resolvida' => false,
            'registrado_por' => $user->id,
        ]);

        $response = $this->post(route('tenant.ocorrencias.resolver', $ocorrencia), [
            'observacoes' => 'Problema solucionado pelo fornecedor.',
        ]);

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('success');

        $ocorrencia->refresh();
        $this->assertTrue($ocorrencia->resolvida);
    }

    // ============================================================
    // CONTROLLER: RelatoriosFiscaisController
    // ============================================================

    public function test_controller_store_relatorio_fiscal_com_sucesso(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $response = $this->post(route('tenant.contratos.relatorios-fiscais.store', $contrato), [
            'periodo_inicio' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'periodo_fim' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            'descricao_atividades' => 'Acompanhamento fiscal completo do periodo contratual.',
            'conformidade_geral' => 1,
            'nota_desempenho' => 7,
        ]);

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('success');

        $relatorio = RelatorioFiscal::where('contrato_id', $contrato->id)->first();
        $this->assertNotNull($relatorio, 'Relatorio fiscal deveria ter sido criado via controller');
        $this->assertTrue($relatorio->conformidade_geral);
        $this->assertEquals(7, (int) $relatorio->nota_desempenho);
    }

    public function test_controller_store_relatorio_fiscal_valida_campos(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $response = $this->post(route('tenant.contratos.relatorios-fiscais.store', $contrato), []);

        $response->assertSessionHasErrors(['periodo_inicio', 'periodo_fim', 'descricao_atividades', 'conformidade_geral']);
    }

    public function test_controller_store_relatorio_fiscal_nota_fora_do_range(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $response = $this->post(route('tenant.contratos.relatorios-fiscais.store', $contrato), [
            'periodo_inicio' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'periodo_fim' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            'descricao_atividades' => 'Atividades de fiscalizacao realizadas no periodo.',
            'conformidade_geral' => 1,
            'nota_desempenho' => 15,
        ]);

        $response->assertSessionHasErrors('nota_desempenho');
    }

    // ============================================================
    // CONTRATO: Relationships
    // ============================================================

    public function test_contrato_tem_ocorrencias(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        Ocorrencia::factory()->count(3)->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'registrado_por' => $user->id,
        ]);

        $this->assertCount(3, $contrato->ocorrencias);
    }

    public function test_contrato_tem_relatorios_fiscais(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        RelatorioFiscal::factory()->count(2)->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'registrado_por' => $user->id,
        ]);

        $this->assertCount(2, $contrato->relatoriosFiscais);
    }

    // ============================================================
    // INTEGRACAO: Ocorrencias contadas no Relatorio Fiscal
    // ============================================================

    public function test_relatorio_fiscal_conta_ocorrencias_no_periodo(): void
    {
        [$contrato, $fiscal, $user] = $this->criarContratoComFiscal();

        $inicioMesPassado = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $fimMesPassado = now()->subMonth()->endOfMonth()->format('Y-m-d');

        // 2 ocorrencias no mes passado
        Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'data_ocorrencia' => now()->subMonth()->startOfMonth()->addDays(5)->format('Y-m-d'),
            'registrado_por' => $user->id,
        ]);
        Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'data_ocorrencia' => now()->subMonth()->startOfMonth()->addDays(15)->format('Y-m-d'),
            'registrado_por' => $user->id,
        ]);

        // 1 ocorrencia fora do periodo
        Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'data_ocorrencia' => now()->subMonths(3)->format('Y-m-d'),
            'registrado_por' => $user->id,
        ]);

        $resultado = RelatorioFiscalService::registrar($contrato, [
            'periodo_inicio' => $inicioMesPassado,
            'periodo_fim' => $fimMesPassado,
            'descricao_atividades' => 'Acompanhamento fiscal incluindo verificacao de ocorrencias.',
            'conformidade_geral' => false,
        ], $user);

        // Se nao foi passado explicitamente, deve contar automaticamente
        $this->assertEquals(2, $resultado['relatorio']->ocorrencias_no_periodo);
    }
}
