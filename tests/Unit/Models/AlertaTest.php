<?php

namespace Tests\Unit\Models;

use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\TipoEventoAlerta;
use App\Models\Alerta;
use App\Models\Contrato;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AlertaTest extends TestCase
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
        $alerta = Alerta::factory()->create();

        $alerta->refresh();

        $this->assertInstanceOf(TipoEventoAlerta::class, $alerta->tipo_evento);
        $this->assertInstanceOf(PrioridadeAlerta::class, $alerta->prioridade);
        $this->assertInstanceOf(StatusAlerta::class, $alerta->status);
    }

    public function test_scope_pendentes(): void
    {
        $contrato = Contrato::factory()->create();

        // 1 pendente
        Alerta::factory()->create([
            'contrato_id' => $contrato->id,
            'status' => StatusAlerta::Pendente,
        ]);

        // 1 resolvido (nao deve aparecer no scope)
        Alerta::factory()->resolvido()->create([
            'contrato_id' => $contrato->id,
        ]);

        $pendentes = Alerta::where('contrato_id', $contrato->id)->pendentes()->get();

        $this->assertCount(1, $pendentes);
        $this->assertEquals(StatusAlerta::Pendente, $pendentes->first()->status);
    }

    public function test_scope_nao_resolvidos(): void
    {
        $contrato = Contrato::factory()->create();

        // Pendente — deve aparecer
        Alerta::factory()->create([
            'contrato_id' => $contrato->id,
            'status' => StatusAlerta::Pendente,
        ]);

        // Enviado — deve aparecer
        Alerta::factory()->enviado()->create([
            'contrato_id' => $contrato->id,
        ]);

        // Visualizado — deve aparecer
        Alerta::factory()->create([
            'contrato_id' => $contrato->id,
            'status' => StatusAlerta::Visualizado,
        ]);

        // Resolvido — NAO deve aparecer
        Alerta::factory()->resolvido()->create([
            'contrato_id' => $contrato->id,
        ]);

        $naoResolvidos = Alerta::where('contrato_id', $contrato->id)->naoResolvidos()->get();

        $this->assertCount(3, $naoResolvidos);
        $this->assertTrue($naoResolvidos->every(
            fn ($a) => $a->status !== StatusAlerta::Resolvido
        ));
    }

    public function test_relacionamento_contrato(): void
    {
        $contrato = Contrato::factory()->create();
        $alerta = Alerta::factory()->create([
            'contrato_id' => $contrato->id,
        ]);

        $alerta->refresh();

        $this->assertNotNull($alerta->contrato);
        $this->assertInstanceOf(Contrato::class, $alerta->contrato);
        $this->assertEquals($contrato->id, $alerta->contrato->id);
    }
}
