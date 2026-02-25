<?php

namespace Tests\Feature\Alertas;

use App\Enums\PrioridadeAlerta;
use App\Enums\StatusContrato;
use App\Enums\TipoAditivo;
use App\Enums\TipoEventoAlerta;
use App\Models\Aditivo;
use App\Models\Alerta;
use App\Models\ConfiguracaoAlertaAvancado;
use App\Models\Contrato;
use App\Models\ExecucaoFinanceira;
use App\Models\Fiscal;
use App\Models\HistoricoAlteracao;
use App\Models\User;
use App\Services\AlertaService;
use App\Services\ExecucaoFinanceiraService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AlertaMotorCompletoTest extends TestCase
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
    // REGRA 2: Execucao Apos Vencimento
    // ═══════════════════════════════════════════════════════════

    public function test_regra2_execucao_apos_vencimento_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vencido->value,
            'data_inicio' => now()->subYear(),
            'data_fim' => now()->subDays(30),
            'is_irregular' => true,
        ]);

        ExecucaoFinanceira::factory()->create([
            'contrato_id' => $contrato->id,
            'data_execucao' => now()->subDays(10)->format('Y-m-d'),
            'registrado_por' => $this->admin->id,
        ]);

        $alertas = AlertaService::verificarExecucaoAposVencimento();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ExecucaoAposVencimento->value,
            'prioridade' => PrioridadeAlerta::Urgente->value,
        ]);
    }

    public function test_regra2_execucao_antes_do_vencimento_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vencido->value,
            'data_inicio' => now()->subYear(),
            'data_fim' => now()->subDays(5),
            'is_irregular' => true,
        ]);

        ExecucaoFinanceira::factory()->create([
            'contrato_id' => $contrato->id,
            'data_execucao' => now()->subDays(10)->format('Y-m-d'),
            'registrado_por' => $this->admin->id,
        ]);

        $alertas = AlertaService::verificarExecucaoAposVencimento();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ExecucaoAposVencimento->value,
        ]);
    }

    public function test_regra2_deduplicacao_execucao_apos_vencimento(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vencido->value,
            'data_inicio' => now()->subYear(),
            'data_fim' => now()->subDays(30),
            'is_irregular' => true,
        ]);

        ExecucaoFinanceira::factory()->create([
            'contrato_id' => $contrato->id,
            'data_execucao' => now()->subDays(10)->format('Y-m-d'),
            'registrado_por' => $this->admin->id,
        ]);

        AlertaService::verificarExecucaoAposVencimento();
        $alertas2 = AlertaService::verificarExecucaoAposVencimento();

        $total = Alerta::where('contrato_id', $contrato->id)
            ->where('tipo_evento', TipoEventoAlerta::ExecucaoAposVencimento->value)
            ->count();

        $this->assertEquals(1, $total);
        $this->assertEquals(0, $alertas2);
    }

    public function test_regra2_alerta_imediato_no_registro_execucao(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vencido->value,
            'data_inicio' => now()->subYear(),
            'data_fim' => now()->subDays(30),
            'is_irregular' => true,
        ]);

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento servico',
            'valor' => 5000.00,
            'data_execucao' => now()->subDays(10)->format('Y-m-d'),
        ], $this->admin);

        $this->assertTrue($resultado['alerta_vencimento']);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ExecucaoAposVencimento->value,
            'prioridade' => PrioridadeAlerta::Urgente->value,
        ]);
    }

    public function test_regra2_execucao_dentro_vigencia_nao_gera_alerta_imediato(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento mensal',
            'valor' => 5000.00,
            'data_execucao' => now()->format('Y-m-d'),
        ], $this->admin);

        $this->assertFalse($resultado['alerta_vencimento']);
        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ExecucaoAposVencimento->value,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // REGRA 3: Aditivo Acima do Limite Legal
    // ═══════════════════════════════════════════════════════════

    public function test_regra3_aditivo_acima_limite_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'valor_global' => 100000.00,
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Valor->value,
            'status' => 'vigente',
            'valor_acrescimo' => 30000.00,
            'percentual_acumulado' => 30.00,
        ]);

        $alertas = AlertaService::verificarAditivosAcimaLimite();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::AditivoAcimaLimite->value,
            'prioridade' => PrioridadeAlerta::Urgente->value,
        ]);
    }

    public function test_regra3_aditivo_dentro_limite_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'valor_global' => 100000.00,
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Valor->value,
            'status' => 'vigente',
            'valor_acrescimo' => 20000.00,
            'percentual_acumulado' => 20.00,
        ]);

        AlertaService::verificarAditivosAcimaLimite();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::AditivoAcimaLimite->value,
        ]);
    }

    public function test_regra3_limite_configuravel(): void
    {
        ConfiguracaoAlertaAvancado::firstOrCreate(
            ['tipo_evento' => TipoEventoAlerta::AditivoAcimaLimite->value],
            [
                'percentual_limite_valor' => 50.00,
                'is_ativo' => true,
            ]
        );

        $contrato = Contrato::factory()->vigente()->create([
            'valor_global' => 100000.00,
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Valor->value,
            'status' => 'vigente',
            'valor_acrescimo' => 30000.00,
            'percentual_acumulado' => 30.00,
        ]);

        AlertaService::verificarAditivosAcimaLimite();

        // Com limite em 50%, aditivo de 30% nao deve gerar alerta
        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::AditivoAcimaLimite->value,
        ]);
    }

    public function test_regra3_aditivo_cancelado_ignorado(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'valor_global' => 100000.00,
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Valor->value,
            'status' => 'cancelado',
            'valor_acrescimo' => 30000.00,
            'percentual_acumulado' => 30.00,
        ]);

        AlertaService::verificarAditivosAcimaLimite();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::AditivoAcimaLimite->value,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // REGRA 4: Contrato sem Fiscal
    // ═══════════════════════════════════════════════════════════

    public function test_regra4_contrato_sem_fiscal_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $alertas = AlertaService::verificarContratosSemFiscal();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoSemFiscal->value,
        ]);
    }

    public function test_regra4_contrato_com_fiscal_titular_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
        ]);

        AlertaService::verificarContratosSemFiscal();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoSemFiscal->value,
        ]);
    }

    public function test_regra4_contrato_apenas_substituto_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'substituto',
        ]);

        $alertas = AlertaService::verificarContratosSemFiscal();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoSemFiscal->value,
        ]);
    }

    public function test_regra4_contrato_vencido_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        AlertaService::verificarContratosSemFiscal();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoSemFiscal->value,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // REGRA 5: Fiscal sem Relatorio Recente
    // ═══════════════════════════════════════════════════════════

    public function test_regra5_fiscal_sem_relatorio_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_inicio' => now()->subMonths(6),
        ]);

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
            'data_ultimo_relatorio' => null,
        ]);

        $alertas = AlertaService::verificarFiscalSemRelatorio();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::FiscalSemRelatorio->value,
        ]);
    }

    public function test_regra5_fiscal_com_relatorio_recente_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_inicio' => now()->subMonths(6),
        ]);

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
            'data_ultimo_relatorio' => now()->subDays(30)->format('Y-m-d'),
        ]);

        AlertaService::verificarFiscalSemRelatorio();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::FiscalSemRelatorio->value,
        ]);
    }

    public function test_regra5_fiscal_relatorio_antigo_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_inicio' => now()->subYear(),
        ]);

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
            'data_ultimo_relatorio' => now()->subDays(90)->format('Y-m-d'),
        ]);

        $alertas = AlertaService::verificarFiscalSemRelatorio();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::FiscalSemRelatorio->value,
        ]);
    }

    public function test_regra5_dias_configuravel(): void
    {
        ConfiguracaoAlertaAvancado::firstOrCreate(
            ['tipo_evento' => TipoEventoAlerta::FiscalSemRelatorio->value],
            [
                'dias_sem_relatorio' => 30,
                'is_ativo' => true,
            ]
        );

        $contrato = Contrato::factory()->vigente()->create([
            'data_inicio' => now()->subMonths(6),
        ]);

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
            'data_ultimo_relatorio' => now()->subDays(45)->format('Y-m-d'),
        ]);

        $alertas = AlertaService::verificarFiscalSemRelatorio();

        // Com limite em 30 dias, 45 dias sem relatorio deve gerar alerta
        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::FiscalSemRelatorio->value,
        ]);
    }

    public function test_regra5_contrato_recente_sem_relatorio_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_inicio' => now()->subDays(15),
        ]);

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
            'data_ultimo_relatorio' => null,
        ]);

        AlertaService::verificarFiscalSemRelatorio();

        // Contrato tem apenas 15 dias — nao deveria exigir relatorio (limite padrao 60)
        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::FiscalSemRelatorio->value,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // REGRA 9: Prorrogacao Fora do Prazo
    // ═══════════════════════════════════════════════════════════

    public function test_regra9_prorrogacao_fora_prazo_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'data_inicio' => now()->subYear(),
            'data_fim' => now()->subDays(10),
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Prazo->value,
            'status' => 'vigente',
            'data_assinatura' => now()->subDays(5)->format('Y-m-d'),
            'nova_data_fim' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $alertas = AlertaService::verificarProrrogacaoForaDoPrazo();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ProrrogacaoForaDoPrazo->value,
            'prioridade' => PrioridadeAlerta::Urgente->value,
        ]);
    }

    public function test_regra9_prorrogacao_dentro_prazo_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_inicio' => now()->subYear(),
            'data_fim' => now()->addDays(30),
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Prazo->value,
            'status' => 'vigente',
            'data_assinatura' => now()->subDays(5)->format('Y-m-d'),
            'nova_data_fim' => now()->addYear()->format('Y-m-d'),
        ]);

        AlertaService::verificarProrrogacaoForaDoPrazo();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ProrrogacaoForaDoPrazo->value,
        ]);
    }

    public function test_regra9_aditivo_valor_nao_gera_alerta_prorrogacao(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'data_inicio' => now()->subYear(),
            'data_fim' => now()->subDays(10),
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Valor->value,
            'status' => 'vigente',
            'data_assinatura' => now()->subDays(5)->format('Y-m-d'),
            'nova_data_fim' => null,
        ]);

        AlertaService::verificarProrrogacaoForaDoPrazo();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ProrrogacaoForaDoPrazo->value,
        ]);
    }

    public function test_regra9_prorrogacao_cancelada_ignorada(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'data_inicio' => now()->subYear(),
            'data_fim' => now()->subDays(10),
        ]);

        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Prazo->value,
            'status' => 'cancelado',
            'data_assinatura' => now()->subDays(5)->format('Y-m-d'),
            'nova_data_fim' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        AlertaService::verificarProrrogacaoForaDoPrazo();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ProrrogacaoForaDoPrazo->value,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // REGRA 10: Contrato Parado (sem movimentacao)
    // ═══════════════════════════════════════════════════════════

    public function test_regra10_contrato_parado_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_inicio' => now()->subMonths(6),
            'created_at' => now()->subMonths(6),
            'updated_at' => now()->subMonths(6),
        ]);

        $alertas = AlertaService::verificarContratosParados();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoParado->value,
        ]);
    }

    public function test_regra10_contrato_com_execucao_recente_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_inicio' => now()->subMonths(6),
        ]);

        ExecucaoFinanceira::factory()->create([
            'contrato_id' => $contrato->id,
            'data_execucao' => now()->subDays(10)->format('Y-m-d'),
            'registrado_por' => $this->admin->id,
            'created_at' => now()->subDays(10),
        ]);

        AlertaService::verificarContratosParados();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoParado->value,
        ]);
    }

    public function test_regra10_contrato_com_documento_recente_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_inicio' => now()->subMonths(6),
        ]);

        \App\Models\Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
            'created_at' => now()->subDays(10),
        ]);

        AlertaService::verificarContratosParados();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoParado->value,
        ]);
    }

    public function test_regra10_dias_inatividade_configuravel(): void
    {
        ConfiguracaoAlertaAvancado::firstOrCreate(
            ['tipo_evento' => TipoEventoAlerta::ContratoParado->value],
            [
                'dias_inatividade' => 30,
                'is_ativo' => true,
            ]
        );

        $contrato = Contrato::factory()->vigente()->create([
            'data_inicio' => now()->subDays(45),
            'created_at' => now()->subDays(45),
            'updated_at' => now()->subDays(45),
        ]);

        $alertas = AlertaService::verificarContratosParados();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoParado->value,
        ]);
    }

    public function test_regra10_contrato_vencido_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->vencido()->create([
            'data_inicio' => now()->subYear(),
        ]);

        AlertaService::verificarContratosParados();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoParado->value,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // ENUM: TipoEventoAlerta — novos cases
    // ═══════════════════════════════════════════════════════════

    public function test_enum_novos_tipos_possuem_label(): void
    {
        $novosTipos = [
            TipoEventoAlerta::ExecucaoAposVencimento,
            TipoEventoAlerta::AditivoAcimaLimite,
            TipoEventoAlerta::ContratoSemFiscal,
            TipoEventoAlerta::FiscalSemRelatorio,
            TipoEventoAlerta::ProrrogacaoForaDoPrazo,
            TipoEventoAlerta::ContratoParado,
        ];

        foreach ($novosTipos as $tipo) {
            $this->assertNotEmpty($tipo->label());
            $this->assertNotEmpty($tipo->value);
        }
    }

    public function test_enum_severidade_critica_para_regras_graves(): void
    {
        $this->assertEquals('critica', TipoEventoAlerta::ExecucaoAposVencimento->severidade());
        $this->assertEquals('critica', TipoEventoAlerta::AditivoAcimaLimite->severidade());
    }

    public function test_enum_severidade_alta_para_regras_importantes(): void
    {
        $this->assertEquals('alta', TipoEventoAlerta::ContratoSemFiscal->severidade());
        $this->assertEquals('alta', TipoEventoAlerta::ProrrogacaoForaDoPrazo->severidade());
    }

    public function test_enum_severidade_media_para_regras_operacionais(): void
    {
        $this->assertEquals('media', TipoEventoAlerta::FiscalSemRelatorio->severidade());
        $this->assertEquals('media', TipoEventoAlerta::ContratoParado->severidade());
    }

    // ═══════════════════════════════════════════════════════════
    // CONFIGURACAO: Desativar regras
    // ═══════════════════════════════════════════════════════════

    public function test_regra_desativada_nao_gera_alertas(): void
    {
        $config = ConfiguracaoAlertaAvancado::firstOrCreate(
            ['tipo_evento' => TipoEventoAlerta::ContratoSemFiscal->value],
            ['is_ativo' => false]
        );
        $config->update(['is_ativo' => false]);

        $contrato = Contrato::factory()->vigente()->create();

        $alertas = AlertaService::verificarContratosSemFiscal();

        $this->assertEquals(0, $alertas);
        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoSemFiscal->value,
        ]);
    }

    public function test_regra_sem_configuracao_considera_ativa(): void
    {
        // Deletar config existente (seeder pode ter criado)
        ConfiguracaoAlertaAvancado::where('tipo_evento', TipoEventoAlerta::ContratoSemFiscal->value)->delete();

        $contrato = Contrato::factory()->vigente()->create();

        $alertas = AlertaService::verificarContratosSemFiscal();

        $this->assertGreaterThanOrEqual(1, $alertas);
    }

    // ═══════════════════════════════════════════════════════════
    // MODEL: ConfiguracaoAlertaAvancado
    // ═══════════════════════════════════════════════════════════

    public function test_model_configuracao_alerta_avancado_criacao(): void
    {
        $config = ConfiguracaoAlertaAvancado::firstOrCreate(
            ['tipo_evento' => TipoEventoAlerta::ContratoParado->value],
            ['dias_inatividade' => 90, 'is_ativo' => true]
        );
        $config->update(['dias_inatividade' => 90, 'is_ativo' => true]);

        $this->assertDatabaseHas('configuracoes_alerta_avancado', [
            'tipo_evento' => 'contrato_parado',
            'dias_inatividade' => 90,
            'is_ativo' => true,
        ]);

        $this->assertEquals(TipoEventoAlerta::ContratoParado, $config->fresh()->tipo_evento);
    }

    public function test_model_scope_ativos(): void
    {
        $ativo = ConfiguracaoAlertaAvancado::firstOrCreate(
            ['tipo_evento' => TipoEventoAlerta::ContratoParado->value],
            ['is_ativo' => true]
        );
        $ativo->update(['is_ativo' => true]);

        $inativo = ConfiguracaoAlertaAvancado::firstOrCreate(
            ['tipo_evento' => TipoEventoAlerta::ContratoSemFiscal->value],
            ['is_ativo' => false]
        );
        $inativo->update(['is_ativo' => false]);

        // Desativar todos os outros para um teste limpo
        ConfiguracaoAlertaAvancado::whereNotIn('tipo_evento', [
            TipoEventoAlerta::ContratoParado->value,
            TipoEventoAlerta::ContratoSemFiscal->value,
        ])->update(['is_ativo' => false]);

        $ativos = ConfiguracaoAlertaAvancado::ativos()->count();
        $this->assertEquals(1, $ativos);
    }

    public function test_model_scope_por_tipo(): void
    {
        $config = ConfiguracaoAlertaAvancado::firstOrCreate(
            ['tipo_evento' => TipoEventoAlerta::FiscalSemRelatorio->value],
            ['dias_sem_relatorio' => 60, 'is_ativo' => true]
        );
        $config->update(['dias_sem_relatorio' => 60, 'is_ativo' => true]);

        $config = ConfiguracaoAlertaAvancado::porTipo(TipoEventoAlerta::FiscalSemRelatorio)->first();

        $this->assertNotNull($config);
        $this->assertEquals(60, $config->dias_sem_relatorio);
    }

    // ═══════════════════════════════════════════════════════════
    // INTEGRACAO: verificarVencimentos inclui novas regras
    // ═══════════════════════════════════════════════════════════

    public function test_verificar_vencimentos_executa_todas_as_regras(): void
    {
        // Criar configuracao de alerta basica para que o motor principal funcione
        \App\Models\ConfiguracaoAlerta::firstOrCreate(
            ['dias_antecedencia' => 30],
            ['prioridade_padrao' => PrioridadeAlerta::Atencao->value, 'is_ativo' => true]
        );

        // Contrato sem fiscal (Regra 4)
        $contratoSemFiscal = Contrato::factory()->vigente()->create();

        $resultado = AlertaService::verificarVencimentos();

        $this->assertArrayHasKey('alertas_gerados', $resultado);
        $this->assertArrayHasKey('contratos_vencidos', $resultado);
        $this->assertArrayHasKey('notificacoes_reenvio', $resultado);

        // Deve ter pelo menos 1 alerta (contrato sem fiscal)
        $this->assertGreaterThanOrEqual(1, $resultado['alertas_gerados']);
    }
}
