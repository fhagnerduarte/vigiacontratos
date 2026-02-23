<?php

namespace Tests\Unit\Services;

use App\Models\Contrato;
use App\Services\ExecucaoFinanceiraService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ExecucaoFinanceiraServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_registrar_execucao(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 100000]);

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento parcela 1',
            'valor' => 10000,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $this->assertNotNull($resultado['execucao']->id);
        $this->assertFalse($resultado['alerta']);
    }

    public function test_registrar_recalcula_percentual(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 100000]);

        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Parcela 1',
            'valor' => 50000,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $contrato->refresh();

        $this->assertEquals(50.0, (float) $contrato->percentual_executado);
    }

    public function test_registrar_alerta_acima_100_porcento(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 100000]);

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento total',
            'valor' => 110000,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $this->assertTrue($resultado['alerta']);
    }

    public function test_registrar_multiplas_execucoes_soma(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 100000]);

        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Parcela 1',
            'valor' => 30000,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Parcela 2',
            'valor' => 20000,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $contrato->refresh();

        $this->assertEquals(50.0, (float) $contrato->percentual_executado);
    }

    public function test_registrar_com_valor_global_zero(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create(['valor_global' => 0]);

        $resultado = ExecucaoFinanceiraService::registrar($contrato, [
            'descricao' => 'Pagamento',
            'valor' => 1000,
            'data_execucao' => now()->format('Y-m-d'),
        ], $user);

        $contrato->refresh();

        $this->assertEquals(0, (float) $contrato->percentual_executado);
        $this->assertFalse($resultado['alerta']);
    }
}
