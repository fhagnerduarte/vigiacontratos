<?php

namespace Tests\Unit\Services;

use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Servidor;
use App\Services\FiscalService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class FiscalServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_designar_fiscal(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $servidor = Servidor::factory()->create();

        $fiscal = FiscalService::designar($contrato, ['servidor_id' => $servidor->id]);

        $this->assertNotNull($fiscal->id);
        $this->assertEquals($contrato->id, $fiscal->contrato_id);
        $this->assertTrue($fiscal->is_atual);
    }

    public function test_designar_snapshot_dados_servidor(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $servidor = Servidor::factory()->create([
            'nome' => 'Joao Silva',
            'matricula' => '12345',
            'cargo' => 'Analista',
            'email' => 'joao@prefeitura.gov.br',
        ]);

        $fiscal = FiscalService::designar($contrato, ['servidor_id' => $servidor->id]);

        $this->assertEquals('Joao Silva', $fiscal->nome);
        $this->assertEquals('12345', $fiscal->matricula);
        $this->assertEquals('Analista', $fiscal->cargo);
        $this->assertEquals('joao@prefeitura.gov.br', $fiscal->email);
    }

    public function test_trocar_fiscal_desativa_anterior(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $servidor1 = Servidor::factory()->create();
        $servidor2 = Servidor::factory()->create();

        $fiscal1 = FiscalService::designar($contrato, ['servidor_id' => $servidor1->id]);
        $contrato->refresh();

        FiscalService::trocar($contrato, ['servidor_id' => $servidor2->id]);
        $fiscal1->refresh();

        $this->assertFalse($fiscal1->is_atual);
        $this->assertNotNull($fiscal1->data_fim);
    }

    public function test_trocar_fiscal_cria_novo(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $servidor1 = Servidor::factory()->create();
        $servidor2 = Servidor::factory()->create();

        FiscalService::designar($contrato, ['servidor_id' => $servidor1->id]);
        $contrato->refresh();

        $novoFiscal = FiscalService::trocar($contrato, ['servidor_id' => $servidor2->id]);

        $this->assertTrue($novoFiscal->is_atual);
        $this->assertEquals($servidor2->id, $novoFiscal->servidor_id);
    }

    public function test_trocar_fiscal_mantem_historico(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $servidor1 = Servidor::factory()->create();
        $servidor2 = Servidor::factory()->create();

        FiscalService::designar($contrato, ['servidor_id' => $servidor1->id]);
        $contrato->refresh();
        FiscalService::trocar($contrato, ['servidor_id' => $servidor2->id]);

        $totalFiscais = Fiscal::where('contrato_id', $contrato->id)->count();

        $this->assertEquals(2, $totalFiscais);
    }
}
