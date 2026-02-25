<?php

namespace Tests\Feature\ExecucaoFinanceira;

use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\StatusContrato;
use App\Enums\TipoEventoAlerta;
use App\Enums\TipoExecucaoFinanceira;
use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\ExecucaoFinanceira;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Services\AlertaService;
use App\Services\ExecucaoFinanceiraService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ExecucaoFinanceiraAvancadaTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        Queue::fake();
    }

    private function criarContrato(array $overrides = []): Contrato
    {
        $secretaria = Secretaria::factory()->create();
        $fornecedor = Fornecedor::factory()->create();

        return Contrato::factory()->create(array_merge([
            'secretaria_id' => $secretaria->id,
            'fornecedor_id' => $fornecedor->id,
            'status' => StatusContrato::Vigente,
            'valor_global' => 100000.00,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ], $overrides));
    }

    // === ENUM ===

    public function test_enum_tipo_execucao_financeira_possui_3_cases(): void
    {
        $cases = TipoExecucaoFinanceira::cases();
        $this->assertCount(3, $cases);
        $this->assertEquals('pagamento', TipoExecucaoFinanceira::Pagamento->value);
        $this->assertEquals('liquidacao', TipoExecucaoFinanceira::Liquidacao->value);
        $this->assertEquals('empenho_adicional', TipoExecucaoFinanceira::EmpenhoAdicional->value);
    }

    public function test_enum_tipo_execucao_possui_label_icone_cor(): void
    {
        $this->assertEquals('Pagamento', TipoExecucaoFinanceira::Pagamento->label());
        $this->assertNotEmpty(TipoExecucaoFinanceira::Pagamento->icone());
        $this->assertNotEmpty(TipoExecucaoFinanceira::Pagamento->cor());

        $this->assertEquals('Liquidacao', TipoExecucaoFinanceira::Liquidacao->label());
        $this->assertEquals('Empenho Adicional', TipoExecucaoFinanceira::EmpenhoAdicional->label());
    }

    public function test_tipo_evento_alerta_empenho_insuficiente(): void
    {
        $tipo = TipoEventoAlerta::EmpenhoInsuficiente;
        $this->assertEquals('empenho_insuficiente', $tipo->value);
        $this->assertEquals('Empenho Insuficiente', $tipo->label());
        $this->assertEquals('critica', $tipo->severidade());
    }

    // === MODEL ===

    public function test_model_execucao_financeira_aceita_novos_campos(): void
    {
        $contrato = $this->criarContrato();
        $user = $this->createAdminUser();

        $execucao = ExecucaoFinanceira::create([
            'contrato_id' => $contrato->id,
            'tipo_execucao' => TipoExecucaoFinanceira::Pagamento->value,
            'descricao' => 'Pagamento parcela 1',
            'valor' => 5000.00,
            'data_execucao' => now()->format('Y-m-d'),
            'numero_nota_fiscal' => 'NF-001',
            'numero_empenho' => 'EMP-2026-001',
            'competencia' => '2026-01',
            'registrado_por' => $user->id,
        ]);

        $this->assertDatabaseHas('execucoes_financeiras', [
            'id' => $execucao->id,
            'tipo_execucao' => 'pagamento',
            'numero_empenho' => 'EMP-2026-001',
            'competencia' => '2026-01',
        ], 'tenant');
    }

    public function test_model_execucao_tipo_cast_para_enum(): void
    {
        $contrato = $this->criarContrato();
        $user = $this->createAdminUser();

        $execucao = ExecucaoFinanceira::create([
            'contrato_id' => $contrato->id,
            'tipo_execucao' => TipoExecucaoFinanceira::Liquidacao->value,
            'descricao' => 'Liquidacao parcela 1',
            'valor' => 3000.00,
            'data_execucao' => now()->format('Y-m-d'),
            'registrado_por' => $user->id,
        ]);

        $execucao->refresh();
        $this->assertInstanceOf(TipoExecucaoFinanceira::class, $execucao->tipo_execucao);
        $this->assertEquals(TipoExecucaoFinanceira::Liquidacao, $execucao->tipo_execucao);
    }

    public function test_model_contrato_possui_valor_empenhado_e_saldo(): void
    {
        $contrato = $this->criarContrato([
            'valor_empenhado' => 80000.00,
            'saldo_contratual' => 75000.00,
        ]);

        $contrato->refresh();
        $this->assertEquals('80000.00', $contrato->valor_empenhado);
        $this->assertEquals('75000.00', $contrato->saldo_contratual);
    }

    public function test_factory_estados_pagamento_liquidacao_empenho(): void
    {
        $contrato = $this->criarContrato();
        $user = $this->createAdminUser();

        $pag = ExecucaoFinanceira::factory()->pagamento()->create([
            'contrato_id' => $contrato->id,
            'registrado_por' => $user->id,
        ]);
        $this->assertEquals(TipoExecucaoFinanceira::Pagamento, $pag->fresh()->tipo_execucao);

        $liq = ExecucaoFinanceira::factory()->liquidacao()->create([
            'contrato_id' => $contrato->id,
            'registrado_por' => $user->id,
        ]);
        $this->assertEquals(TipoExecucaoFinanceira::Liquidacao, $liq->fresh()->tipo_execucao);

        $emp = ExecucaoFinanceira::factory()->empenhoAdicional()->create([
            'contrato_id' => $contrato->id,
            'registrado_por' => $user->id,
        ]);
        $this->assertEquals(TipoExecucaoFinanceira::EmpenhoAdicional, $emp->fresh()->tipo_execucao);
    }

    // === SERVICE: REGISTRAR ===

    public function test_service_registrar_com_tipo_pagamento(): void
    {
        $contrato = $this->criarContrato();
        $user = $this->createAdminUser();

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'tipo_execucao' => TipoExecucaoFinanceira::Pagamento->value,
            'descricao' => 'Pagamento mensal',
            'valor' => 10000.00,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $this->assertInstanceOf(ExecucaoFinanceira::class, $resultado['execucao']);
        $this->assertEquals('pagamento', $resultado['execucao']->tipo_execucao->value);
        $this->assertFalse($resultado['alerta']);
        $this->assertFalse($resultado['alerta_vencimento']);
        $this->assertFalse($resultado['alerta_empenho']);
    }

    public function test_service_registrar_com_tipo_liquidacao(): void
    {
        $contrato = $this->criarContrato();
        $user = $this->createAdminUser();

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'tipo_execucao' => TipoExecucaoFinanceira::Liquidacao->value,
            'descricao' => 'Liquidacao mensal',
            'valor' => 8000.00,
            'data_execucao' => now()->format('Y-m-d'),
            'competencia' => '2026-02',
        ], $user);

        $this->assertEquals(TipoExecucaoFinanceira::Liquidacao, $resultado['execucao']->fresh()->tipo_execucao);
        $this->assertEquals('2026-02', $resultado['execucao']->competencia);
    }

    public function test_service_registrar_sem_tipo_usa_pagamento_padrao(): void
    {
        $contrato = $this->criarContrato();
        $user = $this->createAdminUser();

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento sem tipo',
            'valor' => 5000.00,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $this->assertEquals(TipoExecucaoFinanceira::Pagamento, $resultado['execucao']->fresh()->tipo_execucao);
    }

    public function test_service_registrar_com_numero_empenho(): void
    {
        $contrato = $this->criarContrato();
        $user = $this->createAdminUser();

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento com empenho',
            'valor' => 5000.00,
            'data_execucao' => now()->format('Y-m-d'),
            'numero_empenho' => 'EMP-2026-099',
            'competencia' => '2026-01',
        ], $user);

        $this->assertEquals('EMP-2026-099', $resultado['execucao']->numero_empenho);
        $this->assertEquals('2026-01', $resultado['execucao']->competencia);
    }

    // === SERVICE: SALDO ===

    public function test_calcular_saldo_contrato_sem_execucoes(): void
    {
        $contrato = $this->criarContrato(['valor_global' => 50000.00]);

        $saldo = ExecucaoFinanceiraService::calcularSaldo($contrato);

        $this->assertEquals(50000.00, $saldo['saldo']);
        $this->assertEquals(0.0, $saldo['total_pago']);
        $this->assertEquals(50000.00, $saldo['valor_global']);
        $this->assertFalse($saldo['empenho_insuficiente']);
    }

    public function test_calcular_saldo_com_pagamentos(): void
    {
        $contrato = $this->criarContrato(['valor_global' => 50000.00]);
        $user = $this->createAdminUser();

        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pag 1', 'valor' => 10000, 'data_execucao' => now()->format('Y-m-d'),
        ], $user);
        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pag 2', 'valor' => 15000, 'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $saldo = ExecucaoFinanceiraService::calcularSaldo($contrato->fresh());

        $this->assertEquals(25000.00, $saldo['saldo']);
        $this->assertEquals(25000.00, $saldo['total_pago']);
    }

    public function test_saldo_negativo_quando_executado_excede_global(): void
    {
        $contrato = $this->criarContrato(['valor_global' => 10000.00]);
        $user = $this->createAdminUser();

        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pag alto', 'valor' => 15000, 'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $saldo = ExecucaoFinanceiraService::calcularSaldo($contrato->fresh());

        $this->assertLessThan(0, $saldo['saldo']);
    }

    public function test_empenho_adicional_nao_conta_como_pagamento_no_saldo(): void
    {
        $contrato = $this->criarContrato(['valor_global' => 50000.00, 'valor_empenhado' => 30000.00]);
        $user = $this->createAdminUser();

        // Registra pagamento normal
        ExecucaoFinanceiraService::registrar($contrato, [
            'tipo_execucao' => TipoExecucaoFinanceira::Pagamento->value,
            'descricao' => 'Pag normal', 'valor' => 10000, 'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        // Registra empenho adicional — nao deve afetar total_pago
        ExecucaoFinanceiraService::registrar($contrato, [
            'tipo_execucao' => TipoExecucaoFinanceira::EmpenhoAdicional->value,
            'descricao' => 'Empenho extra', 'valor' => 20000, 'data_execucao' => now()->format('Y-m-d'),
            'numero_empenho' => 'EMP-EXTRA-001',
        ], $user);

        $saldo = ExecucaoFinanceiraService::calcularSaldo($contrato->fresh());

        // Total pago = apenas pagamento (10000), nao empenho (20000)
        $this->assertEquals(10000.00, $saldo['total_pago']);
        $this->assertEquals(40000.00, $saldo['saldo']); // 50000 - 10000
    }

    public function test_saldo_contratual_atualizado_no_contrato_apos_registro(): void
    {
        $contrato = $this->criarContrato(['valor_global' => 30000.00]);
        $user = $this->createAdminUser();

        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pag 1', 'valor' => 12000, 'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $contrato->refresh();
        $this->assertEquals('18000.00', $contrato->saldo_contratual);
    }

    // === SERVICE: EMPENHO ===

    public function test_empenho_insuficiente_quando_pago_excede_empenhado(): void
    {
        $contrato = $this->criarContrato([
            'valor_global' => 100000.00,
            'valor_empenhado' => 10000.00,
        ]);
        $user = $this->createAdminUser();

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento grande',
            'valor' => 15000.00,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $this->assertTrue($resultado['alerta_empenho']);
    }

    public function test_empenho_suficiente_nao_gera_alerta(): void
    {
        $contrato = $this->criarContrato([
            'valor_global' => 100000.00,
            'valor_empenhado' => 50000.00,
        ]);
        $user = $this->createAdminUser();

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento pequeno',
            'valor' => 5000.00,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $this->assertFalse($resultado['alerta_empenho']);
    }

    public function test_sem_empenho_definido_nao_gera_alerta(): void
    {
        $contrato = $this->criarContrato([
            'valor_global' => 100000.00,
            'valor_empenhado' => null,
        ]);
        $user = $this->createAdminUser();

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento sem empenho definido',
            'valor' => 90000.00,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $this->assertFalse($resultado['alerta_empenho']);
    }

    public function test_alerta_empenho_insuficiente_criado_no_banco(): void
    {
        $contrato = $this->criarContrato([
            'valor_global' => 100000.00,
            'valor_empenhado' => 5000.00,
        ]);
        $user = $this->createAdminUser();

        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento excede empenho',
            'valor' => 10000.00,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::EmpenhoInsuficiente->value,
            'prioridade' => PrioridadeAlerta::Urgente->value,
            'status' => StatusAlerta::Pendente->value,
        ], 'tenant');
    }

    public function test_alerta_empenho_deduplicado(): void
    {
        $contrato = $this->criarContrato([
            'valor_global' => 100000.00,
            'valor_empenhado' => 5000.00,
        ]);
        $user = $this->createAdminUser();

        // Dois registros que excedem empenho
        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pag 1', 'valor' => 3000, 'data_execucao' => now()->format('Y-m-d'),
        ], $user);
        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pag 2', 'valor' => 3000, 'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $alertas = Alerta::where('contrato_id', $contrato->id)
            ->where('tipo_evento', TipoEventoAlerta::EmpenhoInsuficiente->value)
            ->count();

        $this->assertEquals(1, $alertas);
    }

    // === SERVICE: RESUMO FINANCEIRO ===

    public function test_resumo_financeiro_completo(): void
    {
        $contrato = $this->criarContrato([
            'valor_global' => 60000.00,
            'valor_empenhado' => 50000.00,
        ]);
        $user = $this->createAdminUser();

        ExecucaoFinanceiraService::registrar($contrato, [
            'tipo_execucao' => TipoExecucaoFinanceira::Pagamento->value,
            'descricao' => 'Pag 1', 'valor' => 10000, 'data_execucao' => now()->format('Y-m-d'),
            'competencia' => '2026-01',
        ], $user);
        ExecucaoFinanceiraService::registrar($contrato, [
            'tipo_execucao' => TipoExecucaoFinanceira::Liquidacao->value,
            'descricao' => 'Liq 1', 'valor' => 8000, 'data_execucao' => now()->format('Y-m-d'),
            'competencia' => '2026-01',
        ], $user);

        $resumo = ExecucaoFinanceiraService::resumoFinanceiro($contrato->fresh());

        $this->assertEquals(60000.00, $resumo['valor_global']);
        $this->assertEquals(50000.00, $resumo['valor_empenhado']);
        $this->assertEquals(10000.00, $resumo['total_pago']);
        $this->assertEquals(8000.00, $resumo['total_liquidado']);
        $this->assertEquals(42000.00, $resumo['saldo']); // 60000 - 10000 - 8000
        $this->assertFalse($resumo['empenho_insuficiente']);
        $this->assertNotEmpty($resumo['por_competencia']);
    }

    public function test_resumo_por_competencia_agrupado(): void
    {
        $contrato = $this->criarContrato();
        $user = $this->createAdminUser();

        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Jan', 'valor' => 5000, 'data_execucao' => '2026-01-15',
            'competencia' => '2026-01',
        ], $user);
        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Jan 2', 'valor' => 3000, 'data_execucao' => '2026-01-20',
            'competencia' => '2026-01',
        ], $user);
        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Fev', 'valor' => 7000, 'data_execucao' => '2026-02-10',
            'competencia' => '2026-02',
        ], $user);

        $resumo = ExecucaoFinanceiraService::resumoFinanceiro($contrato->fresh());
        $competencias = collect($resumo['por_competencia']);

        $this->assertCount(2, $competencias);

        $jan = $competencias->firstWhere('competencia', '2026-01');
        $this->assertEquals(8000.00, $jan['total']);
        $this->assertEquals(2, $jan['quantidade']);
    }

    // === MOTOR DE ALERTAS ===

    public function test_motor_alertas_verifica_empenho_insuficiente(): void
    {
        $contrato = $this->criarContrato([
            'valor_global' => 100000.00,
            'valor_empenhado' => 5000.00,
        ]);
        $user = $this->createAdminUser();

        // Cria pagamento que excede empenho
        ExecucaoFinanceira::factory()->pagamento()->create([
            'contrato_id' => $contrato->id,
            'valor' => 10000.00,
            'data_execucao' => now()->format('Y-m-d'),
            'registrado_por' => $user->id,
        ]);

        $resultado = AlertaService::verificarEmpenhoInsuficiente();

        $this->assertGreaterThanOrEqual(1, $resultado);

        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::EmpenhoInsuficiente->value,
        ], 'tenant');
    }

    public function test_motor_alertas_nao_gera_para_empenho_suficiente(): void
    {
        $contrato = $this->criarContrato([
            'valor_global' => 100000.00,
            'valor_empenhado' => 50000.00,
        ]);
        $user = $this->createAdminUser();

        ExecucaoFinanceira::factory()->pagamento()->create([
            'contrato_id' => $contrato->id,
            'valor' => 5000.00,
            'data_execucao' => now()->format('Y-m-d'),
            'registrado_por' => $user->id,
        ]);

        $resultado = AlertaService::verificarEmpenhoInsuficiente();

        $alertas = Alerta::where('contrato_id', $contrato->id)
            ->where('tipo_evento', TipoEventoAlerta::EmpenhoInsuficiente->value)
            ->count();

        $this->assertEquals(0, $alertas);
    }

    // === CONTROLLER ===

    public function test_controller_store_com_novos_campos(): void
    {
        $user = $this->actingAsAdmin();
        $contrato = $this->criarContrato();

        $response = $this->post(route('tenant.contratos.execucoes.store', $contrato), [
            'tipo_execucao' => 'pagamento',
            'descricao' => 'Pagamento via controller',
            'valor' => '15000.00',
            'data_execucao' => now()->format('Y-m-d'),
            'numero_nota_fiscal' => 'NF-999',
            'numero_empenho' => 'EMP-CTRL-001',
            'competencia' => '2026-02',
        ]);

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('execucoes_financeiras', [
            'contrato_id' => $contrato->id,
            'tipo_execucao' => 'pagamento',
            'numero_empenho' => 'EMP-CTRL-001',
            'competencia' => '2026-02',
        ], 'tenant');
    }

    public function test_controller_store_valida_competencia_formato(): void
    {
        $user = $this->actingAsAdmin();
        $contrato = $this->criarContrato();

        $response = $this->post(route('tenant.contratos.execucoes.store', $contrato), [
            'descricao' => 'Teste formato',
            'valor' => '5000.00',
            'data_execucao' => now()->format('Y-m-d'),
            'competencia' => 'janeiro-2026', // Formato invalido
        ]);

        $response->assertSessionHasErrors('competencia');
    }

    public function test_controller_store_exibe_warning_empenho_insuficiente(): void
    {
        $user = $this->actingAsAdmin();
        $contrato = $this->criarContrato([
            'valor_empenhado' => 1000.00,
        ]);

        $response = $this->post(route('tenant.contratos.execucoes.store', $contrato), [
            'descricao' => 'Pagamento grande',
            'valor' => '5000.00',
            'data_execucao' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');
    }

    // === SCOPES ===

    public function test_scope_pagamentos(): void
    {
        $contrato = $this->criarContrato();
        $user = $this->createAdminUser();

        ExecucaoFinanceira::factory()->pagamento()->create([
            'contrato_id' => $contrato->id, 'registrado_por' => $user->id,
        ]);
        ExecucaoFinanceira::factory()->liquidacao()->create([
            'contrato_id' => $contrato->id, 'registrado_por' => $user->id,
        ]);
        ExecucaoFinanceira::factory()->empenhoAdicional()->create([
            'contrato_id' => $contrato->id, 'registrado_por' => $user->id,
        ]);

        $this->assertEquals(1, $contrato->execucoesFinanceiras()->pagamentos()->count());
        $this->assertEquals(1, $contrato->execucoesFinanceiras()->liquidacoes()->count());
        $this->assertEquals(1, $contrato->execucoesFinanceiras()->empenhos()->count());
    }

    public function test_scope_por_competencia(): void
    {
        $contrato = $this->criarContrato();
        $user = $this->createAdminUser();

        ExecucaoFinanceira::factory()->comCompetencia('2026-01')->create([
            'contrato_id' => $contrato->id, 'registrado_por' => $user->id,
        ]);
        ExecucaoFinanceira::factory()->comCompetencia('2026-01')->create([
            'contrato_id' => $contrato->id, 'registrado_por' => $user->id,
        ]);
        ExecucaoFinanceira::factory()->comCompetencia('2026-02')->create([
            'contrato_id' => $contrato->id, 'registrado_por' => $user->id,
        ]);

        $this->assertEquals(2, $contrato->execucoesFinanceiras()->porCompetencia('2026-01')->count());
        $this->assertEquals(1, $contrato->execucoesFinanceiras()->porCompetencia('2026-02')->count());
    }

    // === PERCENTUAL EXECUTADO ===

    public function test_percentual_executado_exclui_empenho_adicional(): void
    {
        $contrato = $this->criarContrato(['valor_global' => 100000.00]);
        $user = $this->createAdminUser();

        // Pagamento de 20% do valor global
        ExecucaoFinanceiraService::registrar($contrato, [
            'tipo_execucao' => TipoExecucaoFinanceira::Pagamento->value,
            'descricao' => 'Pag', 'valor' => 20000, 'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        // Empenho adicional — nao deve afetar percentual
        ExecucaoFinanceiraService::registrar($contrato, [
            'tipo_execucao' => TipoExecucaoFinanceira::EmpenhoAdicional->value,
            'descricao' => 'Empenho', 'valor' => 50000, 'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $contrato->refresh();
        $this->assertEquals('20.00', $contrato->percentual_executado);
    }
}
