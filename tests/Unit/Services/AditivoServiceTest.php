<?php

namespace Tests\Unit\Services;

use App\Enums\StatusAditivo;
use App\Enums\StatusContrato;
use App\Enums\TipoAditivo;
use App\Enums\TipoContrato;
use App\Models\Aditivo;
use App\Models\ConfiguracaoLimiteAditivo;
use App\Models\Contrato;
use App\Models\User;
use App\Services\AditivoService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AditivoServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    // --- gerarNumeroSequencial ---

    public function test_gerar_numero_sequencial_primeiro_aditivo(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $numero = AditivoService::gerarNumeroSequencial($contrato);

        $this->assertEquals(1, $numero);
    }

    public function test_gerar_numero_sequencial_incrementa(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'numero_sequencial' => 1,
        ]);

        $numero = AditivoService::gerarNumeroSequencial($contrato);

        $this->assertEquals(2, $numero);
    }

    public function test_gerar_numero_sequencial_terceiro_aditivo(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        Aditivo::factory()->create(['contrato_id' => $contrato->id, 'numero_sequencial' => 1]);
        Aditivo::factory()->create(['contrato_id' => $contrato->id, 'numero_sequencial' => 2]);

        $numero = AditivoService::gerarNumeroSequencial($contrato);

        $this->assertEquals(3, $numero);
    }

    // --- calcularPercentualAcumulado ---

    public function test_calcular_percentual_acumulado_sem_aditivos(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 100000]);

        $percentual = AditivoService::calcularPercentualAcumulado($contrato);

        $this->assertEquals(0, $percentual);
    }

    public function test_calcular_percentual_acumulado_com_aditivos(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 100000]);
        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'valor_acrescimo' => 10000,
            'valor_anterior_contrato' => 100000,
            'status' => StatusAditivo::Vigente,
        ]);

        $percentual = AditivoService::calcularPercentualAcumulado($contrato, 5000);

        $this->assertEquals(15.0, $percentual);
    }

    public function test_calcular_percentual_acumulado_ignora_cancelados(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 100000]);
        Aditivo::factory()->cancelado()->create([
            'contrato_id' => $contrato->id,
            'valor_acrescimo' => 50000,
            'valor_anterior_contrato' => 100000,
        ]);

        $percentual = AditivoService::calcularPercentualAcumulado($contrato);

        $this->assertEquals(0, $percentual);
    }

    // --- obterValorOriginal ---

    public function test_obter_valor_original_sem_aditivos(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 200000]);

        $valor = AditivoService::obterValorOriginal($contrato);

        $this->assertEquals(200000, $valor);
    }

    public function test_obter_valor_original_com_snapshot(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 250000]);
        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'numero_sequencial' => 1,
            'valor_anterior_contrato' => 200000,
        ]);

        $valor = AditivoService::obterValorOriginal($contrato);

        $this->assertEquals(200000, $valor);
    }

    // --- verificarLimiteLegal ---

    public function test_verificar_limite_legal_dentro_limite(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['tipo' => TipoContrato::Servico]);
        ConfiguracaoLimiteAditivo::factory()->bloqueante()->create([
            'tipo_contrato' => TipoContrato::Servico,
            'percentual_limite' => 25.00,
        ]);

        $resultado = AditivoService::verificarLimiteLegal($contrato, 20.0);

        $this->assertTrue($resultado['dentro_limite']);
        $this->assertEquals(25.00, $resultado['limite']);
    }

    public function test_verificar_limite_legal_acima_limite_bloqueante(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['tipo' => TipoContrato::Compra]);
        ConfiguracaoLimiteAditivo::factory()->bloqueante()->create([
            'tipo_contrato' => TipoContrato::Compra,
            'percentual_limite' => 25.00,
        ]);

        $resultado = AditivoService::verificarLimiteLegal($contrato, 30.0);

        $this->assertFalse($resultado['dentro_limite']);
        $this->assertTrue($resultado['is_bloqueante']);
    }

    public function test_verificar_limite_legal_acima_limite_alerta(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['tipo' => TipoContrato::Locacao]);
        ConfiguracaoLimiteAditivo::factory()->create([
            'tipo_contrato' => TipoContrato::Locacao,
            'percentual_limite' => 25.00,
            'is_bloqueante' => false,
        ]);

        $resultado = AditivoService::verificarLimiteLegal($contrato, 30.0);

        $this->assertFalse($resultado['dentro_limite']);
        $this->assertFalse($resultado['is_bloqueante']);
    }

    public function test_verificar_limite_legal_obra_50_porcento(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['tipo' => TipoContrato::Obra]);
        ConfiguracaoLimiteAditivo::factory()->obra()->bloqueante()->create();

        $resultado = AditivoService::verificarLimiteLegal($contrato, 40.0);

        $this->assertTrue($resultado['dentro_limite']);
        $this->assertEquals(50.00, $resultado['limite']);
    }

    public function test_verificar_limite_legal_sem_configuracao(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['tipo' => TipoContrato::Locacao]);

        $resultado = AditivoService::verificarLimiteLegal($contrato, 20.0);

        $this->assertTrue($resultado['dentro_limite']);
        $this->assertEquals(25.00, $resultado['limite']);
        $this->assertFalse($resultado['is_bloqueante']);
    }

    // --- criar ---

    public function test_criar_aditivo_prazo(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create([
            'data_fim' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $dados = [
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addYear()->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 107 da Lei 14.133/2021',
            'justificativa' => 'Necessidade de continuidade do servico',
            'justificativa_tecnica' => 'Servico essencial em andamento',
        ];

        $aditivo = AditivoService::criar($dados, $contrato, $user, '127.0.0.1');

        $this->assertNotNull($aditivo->id);
        $this->assertEquals(1, $aditivo->numero_sequencial);
        $this->assertEquals(TipoAditivo::Prazo, $aditivo->tipo);
        $this->assertEquals(StatusAditivo::Vigente, $aditivo->status);
    }

    public function test_criar_aditivo_valor(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create([
            'valor_global' => 100000,
        ]);

        $dados = [
            'tipo' => TipoAditivo::Valor->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'valor_acrescimo' => 20000,
            'fundamentacao_legal' => 'Art. 125 da Lei 14.133/2021',
            'justificativa' => 'Aumento de escopo',
            'justificativa_tecnica' => 'Novos servicos necessarios',
        ];

        $aditivo = AditivoService::criar($dados, $contrato, $user, '127.0.0.1');

        $this->assertEquals(20000, (float) $aditivo->valor_acrescimo);
        $this->assertEquals(100000, (float) $aditivo->valor_anterior_contrato);
        $this->assertEquals(20.0, (float) $aditivo->percentual_acumulado);
    }

    public function test_criar_aditivo_reequilibrio(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 100000]);

        $dados = [
            'tipo' => TipoAditivo::Reequilibrio->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 124 da Lei 14.133/2021',
            'justificativa' => 'Variacao cambial',
            'justificativa_tecnica' => 'Insumos importados',
            'motivo_reequilibrio' => 'Variacao cambial significativa',
            'indice_utilizado' => 'IPCA',
            'valor_anterior_reequilibrio' => 100000,
            'valor_reajustado' => 112000,
        ];

        $aditivo = AditivoService::criar($dados, $contrato, $user, '127.0.0.1');

        $this->assertEquals(TipoAditivo::Reequilibrio, $aditivo->tipo);
        $this->assertEquals(12000, (float) $aditivo->valor_acrescimo);
    }

    public function test_criar_aditivo_contrato_nao_vigente_lanca_exception(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vencido()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('RN-009');

        AditivoService::criar([
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addYear()->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 107',
            'justificativa' => 'Teste',
            'justificativa_tecnica' => 'Teste',
        ], $contrato, $user, '127.0.0.1');
    }

    public function test_criar_aditivo_cria_workflow(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        $aditivo = AditivoService::criar([
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addYear()->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 107',
            'justificativa' => 'Teste',
            'justificativa_tecnica' => 'Teste',
        ], $contrato, $user, '127.0.0.1');

        $this->assertCount(5, $aditivo->workflowAprovacoes);
    }

    // --- atualizarContratoPai ---

    public function test_atualizar_contrato_pai_recalcula_valor_global(): void
    {
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 100000]);
        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'numero_sequencial' => 1,
            'valor_anterior_contrato' => 100000,
            'valor_acrescimo' => 25000,
            'valor_supressao' => 0,
            'status' => StatusAditivo::Vigente,
        ]);

        AditivoService::atualizarContratoPai($contrato);
        $contrato->refresh();

        $this->assertEquals(125000, (float) $contrato->valor_global);
    }

    public function test_atualizar_contrato_pai_recalcula_data_fim(): void
    {
        $novaDataFim = now()->addYears(2)->format('Y-m-d');
        $contrato = Contrato::factory()->vigente()->create([
            'data_fim' => now()->addMonths(6)->format('Y-m-d'),
        ]);
        Aditivo::factory()->dePrazo()->create([
            'contrato_id' => $contrato->id,
            'nova_data_fim' => $novaDataFim,
            'valor_anterior_contrato' => $contrato->valor_global,
        ]);

        AditivoService::atualizarContratoPai($contrato);
        $contrato->refresh();

        $this->assertEquals($novaDataFim, $contrato->data_fim->format('Y-m-d'));
    }

    public function test_atualizar_contrato_pai_recalcula_risco(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'valor_global' => 100000,
            'score_risco' => 0,
        ]);
        Aditivo::factory()->deValor(50000)->create([
            'contrato_id' => $contrato->id,
            'valor_anterior_contrato' => 100000,
        ]);

        AditivoService::atualizarContratoPai($contrato);
        $contrato->refresh();

        $this->assertNotNull($contrato->score_risco);
        $this->assertNotNull($contrato->nivel_risco);
    }

    // --- cancelar ---

    public function test_cancelar_aditivo(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 125000]);
        $aditivo = Aditivo::factory()->deValor(25000)->create([
            'contrato_id' => $contrato->id,
            'valor_anterior_contrato' => 100000,
            'status' => StatusAditivo::Vigente,
        ]);

        $resultado = AditivoService::cancelar($aditivo, $user, '127.0.0.1');

        $this->assertEquals(StatusAditivo::Cancelado, $resultado->status);
    }

    public function test_cancelar_aditivo_nao_vigente_lanca_exception(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->cancelado()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('vigentes');

        AditivoService::cancelar($aditivo, $user, '127.0.0.1');
    }

    // --- processarReequilibrio ---

    public function test_processar_reequilibrio(): void
    {
        $dados = [
            'valor_anterior_reequilibrio' => 100000,
            'valor_reajustado' => 112000,
        ];

        $resultado = AditivoService::processarReequilibrio($dados);

        $this->assertEquals(12000, $resultado['valor_acrescimo']);
    }

    public function test_processar_reequilibrio_sem_acrescimo_negativo(): void
    {
        $dados = [
            'valor_anterior_reequilibrio' => 120000,
            'valor_reajustado' => 100000,
        ];

        $resultado = AditivoService::processarReequilibrio($dados);

        $this->assertEquals(0, $resultado['valor_acrescimo']);
    }
}
