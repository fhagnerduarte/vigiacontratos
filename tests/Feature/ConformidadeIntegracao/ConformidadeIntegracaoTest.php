<?php

namespace Tests\Feature\ConformidadeIntegracao;

use App\Enums\NivelRisco;
use App\Enums\RegimeExecucao;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Enums\TipoEventoAlerta;
use App\Enums\TipoExecucaoFinanceira;
use App\Enums\TipoOcorrencia;
use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Ocorrencia;
use App\Models\RelatorioFiscal;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use App\Services\AlertaService;
use App\Services\ExecucaoFinanceiraService;
use App\Services\OcorrenciaService;
use App\Services\RelatorioFiscalService;
use App\Services\RiscoService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ConformidadeIntegracaoTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    private User $admin;
    private Secretaria $secretaria;
    private Contrato $contrato;
    private Fiscal $fiscal;
    private Servidor $servidor;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->seedBaseData();

        $this->setUpTenant();
        $this->secretaria = Secretaria::factory()->create();
        $this->admin = $this->createAdminUser();
        $this->admin->secretarias()->attach($this->secretaria->id);
        $this->actingAs($this->admin);

        $this->servidor = Servidor::factory()->create(['is_ativo' => true]);
    }

    private function criarContratoCompleto(array $overrides = []): Contrato
    {
        return Contrato::factory()->create(array_merge([
            'secretaria_id' => $this->secretaria->id,
            'tipo' => TipoContrato::Servico->value,
            'status' => StatusContrato::Vigente->value,
            'data_inicio' => now()->subMonths(6),
            'data_assinatura' => now()->subMonths(6)->subDays(5),
            'data_fim' => now()->addMonths(6),
            'valor_global' => 500000.00,
            'prazo_meses' => 12,
            'regime_execucao' => RegimeExecucao::PrecoGlobal->value,
            'data_publicacao' => now()->subMonths(6)->addDay(),
            'veiculo_publicacao' => 'Diario Oficial do Municipio',
            'link_transparencia' => 'https://transparencia.municipio.gov.br/contrato/123',
            'numero_processo' => '001/2025',
            'fundamento_legal' => 'Lei 14.133/2021, Art. 75',
        ], $overrides));
    }

    private function designarFiscal(Contrato $contrato): Fiscal
    {
        return Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'servidor_id' => $this->servidor->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
            'data_inicio' => now()->subMonths(6),
            'portaria_designacao' => 'Portaria 001/2025',
        ]);
    }

    // ============================================================
    // E2E: Ciclo Completo de Conformidade
    // ============================================================

    public function test_ciclo_completo_contrato_com_todos_campos_compliance(): void
    {
        $contrato = $this->criarContratoCompleto();

        // Verifica campos de compliance (IMP-049)
        $this->assertNotNull($contrato->data_assinatura);
        $this->assertNotNull($contrato->regime_execucao);
        $this->assertNotNull($contrato->data_publicacao);
        $this->assertNotNull($contrato->veiculo_publicacao);
        $this->assertNotNull($contrato->link_transparencia);
        $this->assertTrue($contrato->publicado);

        // Designar fiscal titular
        $fiscal = $this->designarFiscal($contrato);
        $contrato->refresh();
        $this->assertNotNull($contrato->fiscalAtual);
        $this->assertEquals('titular', $contrato->fiscalAtual->tipo_fiscal->value);
    }

    public function test_ciclo_registrar_ocorrencia_e_resolver(): void
    {
        $contrato = $this->criarContratoCompleto();
        $fiscal = $this->designarFiscal($contrato);

        // Registrar ocorrencia
        $resultado = OcorrenciaService::registrar($contrato, [
            'data_ocorrencia' => now()->format('Y-m-d'),
            'tipo_ocorrencia' => TipoOcorrencia::Atraso->value,
            'descricao' => 'Fornecedor atrasou entrega do material em 15 dias.',
            'providencia' => 'Notificar fornecedor formalmente.',
            'prazo_providencia' => now()->addDays(10)->format('Y-m-d'),
        ], $this->admin);

        $this->assertInstanceOf(Ocorrencia::class, $resultado['ocorrencia']);
        $this->assertEmpty($resultado['ocorrencia']->fresh()->resolvida);

        // Resumo deve ter 1 pendente
        $resumo = OcorrenciaService::resumo($contrato);
        $this->assertEquals(1, $resumo['pendentes']);

        // Resolver
        $resolvida = OcorrenciaService::resolver($resultado['ocorrencia'], $this->admin, 'Material entregue.');
        $this->assertTrue($resolvida->resolvida);

        // Resumo agora 0 pendentes
        $contrato->refresh();
        $resumo = OcorrenciaService::resumo($contrato);
        $this->assertEquals(0, $resumo['pendentes']);
        $this->assertEquals(1, $resumo['resolvidas']);
    }

    public function test_ciclo_registrar_relatorio_fiscal_resolve_alerta(): void
    {
        $contrato = $this->criarContratoCompleto();
        $fiscal = $this->designarFiscal($contrato);

        // Cria alerta de FiscalSemRelatorio
        Alerta::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::FiscalSemRelatorio->value,
            'mensagem' => 'Fiscal sem relatorio ha mais de 60 dias.',
            'status' => 'pendente',
        ]);

        // Registrar relatorio fiscal
        $resultado = RelatorioFiscalService::registrar($contrato, [
            'periodo_inicio' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'periodo_fim' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            'descricao_atividades' => 'Acompanhamento completo da execucao contratual no periodo.',
            'conformidade_geral' => true,
            'nota_desempenho' => 8,
        ], $this->admin);

        // Alerta deve ter sido resolvido
        $this->assertTrue($resultado['alerta_resolvido']);

        // Fiscal deve ter data_ultimo_relatorio atualizada
        $fiscal->refresh();
        $this->assertEquals(
            now()->subMonth()->endOfMonth()->format('Y-m-d'),
            $fiscal->data_ultimo_relatorio->format('Y-m-d')
        );
    }

    public function test_ciclo_execucao_financeira_com_tipo_e_saldo(): void
    {
        $contrato = $this->criarContratoCompleto([
            'valor_global' => 100000.00,
            'valor_empenhado' => 80000.00,
        ]);
        $fiscal = $this->designarFiscal($contrato);

        // Registrar pagamento
        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'tipo_execucao' => TipoExecucaoFinanceira::Pagamento->value,
            'descricao' => 'Pagamento da 1a parcela.',
            'valor' => 30000.00,
            'data_execucao' => now()->format('Y-m-d'),
            'numero_nota_fiscal' => 'NF-000001',
            'competencia' => now()->format('Y-m'),
        ], $this->admin);

        $contrato->refresh();
        $this->assertEquals(30.00, (float) $contrato->percentual_executado);
        $this->assertEquals(70000.00, (float) $contrato->saldo_contratual);

        // Registrar empenho adicional (nao altera percentual)
        ExecucaoFinanceiraService::registrar($contrato, [
            'tipo_execucao' => TipoExecucaoFinanceira::EmpenhoAdicional->value,
            'descricao' => 'Empenho adicional reforco.',
            'valor' => 20000.00,
            'data_execucao' => now()->format('Y-m-d'),
            'numero_empenho' => 'EMP-000002',
        ], $this->admin);

        $contrato->refresh();
        // Percentual nao deve mudar (EmpenhoAdicional nao conta como pagamento)
        $this->assertEquals(30.00, (float) $contrato->percentual_executado);
        // Empenho deve ser atualizado
        $this->assertEquals(100000.00, (float) $contrato->valor_empenhado);
    }

    // ============================================================
    // RISCO: Integracao com novos criterios IMP-054
    // ============================================================

    public function test_risco_operacional_aumenta_com_ocorrencias_vencidas(): void
    {
        $contrato = $this->criarContratoCompleto();
        $fiscal = $this->designarFiscal($contrato);

        // Score base (sem ocorrencias)
        $riscoBefore = RiscoService::calcularExpandido($contrato);
        $operacionalBefore = $riscoBefore['categorias']['operacional']['score'];

        // Criar 2 ocorrencias vencidas
        Ocorrencia::factory()->count(2)->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'resolvida' => false,
            'prazo_providencia' => now()->subDays(10),
            'registrado_por' => $this->admin->id,
        ]);

        $riscoAfter = RiscoService::calcularExpandido($contrato);
        $operacionalAfter = $riscoAfter['categorias']['operacional']['score'];

        // Score deve ter aumentado em 10pts (2 x 5pts)
        $this->assertEquals($operacionalBefore + 10, $operacionalAfter);
    }

    public function test_risco_operacional_aumenta_sem_relatorio_fiscal(): void
    {
        $contrato = $this->criarContratoCompleto();
        $fiscal = $this->designarFiscal($contrato);

        // Fiscal sem nenhum relatorio
        $risco = RiscoService::calcularExpandido($contrato);
        $criterios = $risco['categorias']['operacional']['criterios'];

        $this->assertContains(
            'Fiscal nunca registrou relatorio (+10pts)',
            $criterios
        );
    }

    public function test_risco_operacional_diminui_apos_relatorio_fiscal(): void
    {
        $contrato = $this->criarContratoCompleto();
        $fiscal = $this->designarFiscal($contrato);

        // Score com fiscal sem relatorio
        $riscoBefore = RiscoService::calcularExpandido($contrato);

        // Registrar relatorio (atualiza data_ultimo_relatorio)
        RelatorioFiscalService::registrar($contrato, [
            'periodo_inicio' => now()->subDays(15)->format('Y-m-d'),
            'periodo_fim' => now()->format('Y-m-d'),
            'descricao_atividades' => 'Fiscalizacao completa do periodo contratual.',
            'conformidade_geral' => true,
        ], $this->admin);

        $contrato->refresh();
        $contrato->load('fiscalAtual');

        $riscoAfter = RiscoService::calcularExpandido($contrato);

        // Criterio "Fiscal nunca registrou relatorio" deve ter saido
        $criteriosAfter = $riscoAfter['categorias']['operacional']['criterios'];
        $this->assertNotContains(
            'Fiscal nunca registrou relatorio (+10pts)',
            $criteriosAfter
        );
    }

    // ============================================================
    // NIVEL RISCO: Labels atualizados
    // ============================================================

    public function test_nivel_risco_labels_tce(): void
    {
        $this->assertEquals('Regular', NivelRisco::Baixo->label());
        $this->assertEquals('Atencao', NivelRisco::Medio->label());
        $this->assertEquals('Critico', NivelRisco::Alto->label());
    }

    public function test_nivel_risco_possui_icone(): void
    {
        foreach (NivelRisco::cases() as $nivel) {
            $this->assertNotEmpty($nivel->icone());
            $this->assertNotEmpty($nivel->descricao());
        }
    }

    public function test_nivel_risco_cores_mantidas(): void
    {
        $this->assertEquals('success', NivelRisco::Baixo->cor());
        $this->assertEquals('warning', NivelRisco::Medio->cor());
        $this->assertEquals('danger', NivelRisco::Alto->cor());
    }

    // ============================================================
    // CONTROLLER: Exibicao integrada no show do contrato
    // ============================================================

    public function test_show_contrato_exibe_abas_ocorrencias_e_relatorios(): void
    {
        $contrato = $this->criarContratoCompleto();
        $fiscal = $this->designarFiscal($contrato);

        $response = $this->get(route('tenant.contratos.show', $contrato));

        $response->assertStatus(200);
        $response->assertSee('Ocorrencias');
        $response->assertSee('Rel. Fiscais');
    }

    public function test_show_contrato_exibe_badge_ocorrencias_pendentes(): void
    {
        $contrato = $this->criarContratoCompleto();
        $fiscal = $this->designarFiscal($contrato);

        // Criar ocorrencia pendente
        Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'resolvida' => false,
            'registrado_por' => $this->admin->id,
        ]);

        $response = $this->get(route('tenant.contratos.show', $contrato));

        $response->assertStatus(200);
        // Badge deve mostrar 1 pendente
        $response->assertSee('Pendente');
    }

    public function test_show_contrato_exibe_resumo_relatorios_fiscais(): void
    {
        $contrato = $this->criarContratoCompleto();
        $fiscal = $this->designarFiscal($contrato);

        RelatorioFiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscal->id,
            'conformidade_geral' => true,
            'nota_desempenho' => 9,
            'registrado_por' => $this->admin->id,
        ]);

        $response = $this->get(route('tenant.contratos.show', $contrato));

        $response->assertStatus(200);
        $response->assertSee('Total Relatorios');
        $response->assertSee('Conformes');
        $response->assertSee('Nota Media');
    }

    // ============================================================
    // E2E: Score atualizado apos ciclo completo
    // ============================================================

    public function test_score_contrato_completo_e_regular(): void
    {
        $contrato = $this->criarContratoCompleto([
            'numero_processo' => '001/2025',
            'fundamento_legal' => 'Lei 14.133/2021',
            'prazo_meses' => 12,
        ]);
        $fiscal = $this->designarFiscal($contrato);

        // Registrar relatorio para evitar penalizacao
        RelatorioFiscalService::registrar($contrato, [
            'periodo_inicio' => now()->subDays(30)->format('Y-m-d'),
            'periodo_fim' => now()->format('Y-m-d'),
            'descricao_atividades' => 'Acompanhamento completo do periodo.',
            'conformidade_geral' => true,
        ], $this->admin);

        $contrato->refresh();
        $contrato->load('fiscalAtual');

        $risco = RiscoService::calcular($contrato);

        // Contrato bem documentado e regular deve ter score baixo
        $this->assertLessThan(60, $risco['score']);
    }

    public function test_score_contrato_com_problemas_e_critico(): void
    {
        $contrato = $this->criarContratoCompleto([
            'numero_processo' => '',
            'fundamento_legal' => '',
            'valor_global' => 2000000.00,
            'data_fim' => now()->addDays(15), // vence em 15 dias
            'prazo_meses' => 36,
        ]);
        // Sem fiscal designado = +20pts operacional

        // Criar 3 ocorrencias vencidas
        $fiscalFake = Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'servidor_id' => $this->servidor->id,
            'is_atual' => false,
            'tipo_fiscal' => 'titular',
        ]);

        Ocorrencia::factory()->count(3)->create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscalFake->id,
            'resolvida' => false,
            'prazo_providencia' => now()->subDays(10),
            'registrado_por' => $this->admin->id,
        ]);

        $risco = RiscoService::calcular($contrato);

        // Score deve ser alto (sem fiscal +20, sem processo +10, valor alto +10, vencimento proximo +15, etc)
        $this->assertEquals(NivelRisco::Alto, $risco['nivel']);
    }
}
