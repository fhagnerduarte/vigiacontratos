<?php

namespace Tests\Unit\Models;

use App\Enums\StatusAditivo;
use App\Enums\TipoAditivo;
use App\Models\Aditivo;
use App\Models\Contrato;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AditivoTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_casts_retornam_enums(): void
    {
        $aditivo = Aditivo::factory()->create();

        $aditivo->refresh();

        $this->assertInstanceOf(TipoAditivo::class, $aditivo->tipo);
        $this->assertInstanceOf(StatusAditivo::class, $aditivo->status);
    }

    public function test_soft_deletes(): void
    {
        $aditivo = Aditivo::factory()->create();

        $aditivo->delete();

        $this->assertSoftDeleted('aditivos', ['id' => $aditivo->id]);
    }

    public function test_relacionamento_contrato(): void
    {
        $contrato = Contrato::factory()->create();
        $aditivo = Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
        ]);

        $aditivo->refresh();

        $this->assertNotNull($aditivo->contrato);
        $this->assertInstanceOf(Contrato::class, $aditivo->contrato);
        $this->assertEquals($contrato->id, $aditivo->contrato->id);
    }

    public function test_workflow_aprovado_accessor_false_sem_workflow(): void
    {
        $aditivo = Aditivo::factory()->create();

        $aditivo->load('workflowAprovacoes');

        $this->assertFalse($aditivo->workflowAprovado);
    }
}
