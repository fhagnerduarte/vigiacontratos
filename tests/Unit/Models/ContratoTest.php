<?php

namespace Tests\Unit\Models;

use App\Enums\StatusCompletudeDocumental;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ContratoTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_casts_retornam_enums_corretos(): void
    {
        $contrato = Contrato::factory()->create([
            'tipo' => 'servico',
            'status' => 'vigente',
        ]);

        $contrato->refresh();

        $this->assertInstanceOf(TipoContrato::class, $contrato->tipo);
        $this->assertEquals(TipoContrato::Servico, $contrato->tipo);
        $this->assertInstanceOf(StatusContrato::class, $contrato->status);
        $this->assertEquals(StatusContrato::Vigente, $contrato->status);
    }

    public function test_status_completude_incompleto_sem_documentos(): void
    {
        $contrato = Contrato::factory()->create();

        $this->assertEquals(
            StatusCompletudeDocumental::Incompleto,
            $contrato->status_completude
        );
    }

    public function test_status_completude_parcial_com_alguns_docs(): void
    {
        $contrato = Contrato::factory()->create();
        $uploader = User::factory()->create();

        // Apenas ContratoOriginal e PublicacaoOficial (2 de 4 obrigatorios)
        // Nota: a logica de Parcial exige que ContratoOriginal esteja presente
        Documento::factory()->contratoOriginal()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $uploader->id,
        ]);

        Documento::factory()->publicacaoOficial()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $uploader->id,
        ]);

        // Recarrega o contrato com documentos para que o accessor funcione
        $contrato->load('documentos');

        $this->assertEquals(
            StatusCompletudeDocumental::Parcial,
            $contrato->status_completude
        );
    }

    public function test_status_completude_completo_com_todos_docs(): void
    {
        $contrato = Contrato::factory()->create();
        $uploader = User::factory()->create();

        // Todos os 4 tipos obrigatorios
        Documento::factory()->contratoOriginal()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $uploader->id,
        ]);

        Documento::factory()->publicacaoOficial()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $uploader->id,
        ]);

        Documento::factory()->parecerJuridico()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $uploader->id,
        ]);

        Documento::factory()->notaEmpenho()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $uploader->id,
        ]);

        // Recarrega o contrato com documentos para que o accessor funcione
        $contrato->load('documentos');

        $this->assertEquals(
            StatusCompletudeDocumental::Completo,
            $contrato->status_completude
        );
    }

    public function test_dias_para_vencimento(): void
    {
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $contrato->refresh();

        $this->assertInstanceOf(Carbon::class, $contrato->data_fim);
        $this->assertEquals(30, $contrato->dias_para_vencimento);
    }
}
