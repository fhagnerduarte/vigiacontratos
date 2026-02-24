<?php

namespace Tests\Feature\Alertas;

use App\Enums\TipoAditivo;
use App\Enums\TipoDocumentoContratual;
use App\Enums\TipoEventoAlerta;
use App\Models\Aditivo;
use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\User;
use App\Services\AlertaService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class IncompletudeDocumentalTest extends TestCase
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

    // ─── RN-125: Aditivo sem documento aditivo_doc ───────────

    public function test_rn125_aditivo_sem_documento_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create();
        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Valor->value,
            'status' => 'vigente',
        ]);

        $alertas = AlertaService::verificarIncompletudeDocumental();

        $this->assertGreaterThanOrEqual(1, $alertas);

        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::AditivoSemDocumento->value,
            'status' => 'pendente',
        ]);
    }

    public function test_rn125_aditivo_com_documento_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create();
        $aditivo = Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Valor->value,
            'status' => 'vigente',
        ]);

        Documento::factory()->create([
            'documentable_type' => Aditivo::class,
            'documentable_id' => $aditivo->id,
            'tipo_documento' => TipoDocumentoContratual::AditivoDoc->value,
            'uploaded_by' => $this->admin->id,
        ]);

        AlertaService::verificarIncompletudeDocumental();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::AditivoSemDocumento->value,
        ]);
    }

    public function test_rn125_aditivo_cancelado_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create();
        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Valor->value,
            'status' => 'cancelado',
        ]);

        AlertaService::verificarIncompletudeDocumental();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::AditivoSemDocumento->value,
        ]);
    }

    // ─── RN-126: Prorrogacao sem parecer juridico ────────────

    public function test_rn126_prorrogacao_sem_parecer_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create();
        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Prazo->value,
            'status' => 'vigente',
        ]);

        AlertaService::verificarIncompletudeDocumental();

        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ProrrogacaoSemParecer->value,
            'status' => 'pendente',
        ]);
    }

    public function test_rn126_prorrogacao_com_parecer_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create();
        $aditivo = Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Prazo->value,
            'status' => 'vigente',
        ]);

        Documento::factory()->create([
            'documentable_type' => Aditivo::class,
            'documentable_id' => $aditivo->id,
            'tipo_documento' => TipoDocumentoContratual::ParecerJuridico->value,
            'uploaded_by' => $this->admin->id,
        ]);

        AlertaService::verificarIncompletudeDocumental();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ProrrogacaoSemParecer->value,
        ]);
    }

    public function test_rn126_aditivo_valor_sem_parecer_nao_gera_alerta_prorrogacao(): void
    {
        $contrato = Contrato::factory()->create();
        Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Valor->value,
            'status' => 'vigente',
        ]);

        AlertaService::verificarIncompletudeDocumental();

        // Aditivo de valor nao deveria gerar alerta de prorrogacao sem parecer
        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ProrrogacaoSemParecer->value,
        ]);
    }

    // ─── RN-127: Contrato >R$500k sem publicacao ─────────────

    public function test_rn127_contrato_alto_valor_sem_publicacao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create([
            'valor_global' => 600000.00,
            'status' => 'vigente',
        ]);

        AlertaService::verificarIncompletudeDocumental();

        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoSemPublicacao->value,
            'status' => 'pendente',
        ]);
    }

    public function test_rn127_contrato_alto_valor_com_publicacao_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create([
            'valor_global' => 600000.00,
            'status' => 'vigente',
        ]);

        Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'tipo_documento' => TipoDocumentoContratual::PublicacaoOficial->value,
            'uploaded_by' => $this->admin->id,
        ]);

        AlertaService::verificarIncompletudeDocumental();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoSemPublicacao->value,
        ]);
    }

    public function test_rn127_contrato_baixo_valor_sem_publicacao_nao_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create([
            'valor_global' => 400000.00,
            'status' => 'vigente',
        ]);

        AlertaService::verificarIncompletudeDocumental();

        $this->assertDatabaseMissing('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoSemPublicacao->value,
        ]);
    }

    // ─── DEDUPLICACAO ────────────────────────────────────────

    public function test_deduplicacao_nao_cria_alerta_duplicado(): void
    {
        $contrato = Contrato::factory()->create([
            'valor_global' => 600000.00,
            'status' => 'vigente',
        ]);

        // Primeira execucao
        $alertas1 = AlertaService::verificarIncompletudeDocumental();
        // Segunda execucao (nao deve duplicar)
        $alertas2 = AlertaService::verificarIncompletudeDocumental();

        $total = Alerta::where('contrato_id', $contrato->id)
            ->where('tipo_evento', TipoEventoAlerta::ContratoSemPublicacao->value)
            ->count();

        $this->assertEquals(1, $total);
        $this->assertEquals(0, $alertas2); // Segunda execucao nao gera novos
    }
}
