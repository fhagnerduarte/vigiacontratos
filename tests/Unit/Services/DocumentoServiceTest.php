<?php

namespace Tests\Unit\Services;

use App\Enums\AcaoLogDocumento;
use App\Enums\StatusCompletudeDocumental;
use App\Enums\TipoDocumentoContratual;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\LogAcessoDocumento;
use App\Services\DocumentoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class DocumentoServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        Storage::fake('local');
    }

    private function criarPdfFake(): UploadedFile
    {
        // Cria arquivo com magic bytes de PDF
        $tmpFile = tempnam(sys_get_temp_dir(), 'pdf_test_');
        file_put_contents($tmpFile, '%PDF-1.4 fake pdf content for testing');

        return new UploadedFile(
            $tmpFile,
            'documento_teste.pdf',
            'application/pdf',
            null,
            true // test mode
        );
    }

    // --- upload ---

    public function test_upload_cria_documento(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();
        $arquivo = $this->criarPdfFake();

        $documento = DocumentoService::upload(
            $arquivo,
            $contrato,
            TipoDocumentoContratual::ContratoOriginal,
            $user,
            '127.0.0.1'
        );

        $this->assertNotNull($documento->id);
        $this->assertEquals(TipoDocumentoContratual::ContratoOriginal, $documento->tipo_documento);
        $this->assertTrue($documento->is_versao_atual);
        $this->assertEquals(1, $documento->versao);
    }

    public function test_upload_versionamento_automatico(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        // Upload v1
        $doc1 = DocumentoService::upload(
            $this->criarPdfFake(),
            $contrato,
            TipoDocumentoContratual::ContratoOriginal,
            $user,
            '127.0.0.1'
        );

        // Upload v2 do mesmo tipo
        $doc2 = DocumentoService::upload(
            $this->criarPdfFake(),
            $contrato,
            TipoDocumentoContratual::ContratoOriginal,
            $user,
            '127.0.0.1'
        );

        $doc1->refresh();

        $this->assertEquals(1, $doc1->versao);
        $this->assertFalse($doc1->is_versao_atual);
        $this->assertEquals(2, $doc2->versao);
        $this->assertTrue($doc2->is_versao_atual);
    }

    public function test_upload_nome_padronizado(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create(['numero' => '001/2026']);

        $documento = DocumentoService::upload(
            $this->criarPdfFake(),
            $contrato,
            TipoDocumentoContratual::ContratoOriginal,
            $user,
            '127.0.0.1'
        );

        $this->assertEquals('contrato_001-2026_contrato_original_v1.pdf', $documento->nome_arquivo);
    }

    public function test_upload_calcula_hash_sha256(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        $documento = DocumentoService::upload(
            $this->criarPdfFake(),
            $contrato,
            TipoDocumentoContratual::ContratoOriginal,
            $user,
            '127.0.0.1'
        );

        $this->assertNotNull($documento->hash_integridade);
        $this->assertEquals(64, strlen($documento->hash_integridade)); // SHA-256 = 64 hex chars
    }

    public function test_upload_registra_log_acesso(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        $documento = DocumentoService::upload(
            $this->criarPdfFake(),
            $contrato,
            TipoDocumentoContratual::ContratoOriginal,
            $user,
            '127.0.0.1'
        );

        $log = LogAcessoDocumento::where('documento_id', $documento->id)->first();

        $this->assertNotNull($log);
        $this->assertEquals(AcaoLogDocumento::Upload, $log->acao);
        $this->assertEquals($user->id, $log->user_id);
    }

    // --- excluir ---

    public function test_excluir_soft_delete(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        $documento = DocumentoService::upload(
            $this->criarPdfFake(),
            $contrato,
            TipoDocumentoContratual::ContratoOriginal,
            $user,
            '127.0.0.1'
        );

        DocumentoService::excluir($documento, $user, '127.0.0.1');

        $this->assertSoftDeleted('documentos', ['id' => $documento->id]);
    }

    public function test_excluir_registra_log(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        $documento = DocumentoService::upload(
            $this->criarPdfFake(),
            $contrato,
            TipoDocumentoContratual::ContratoOriginal,
            $user,
            '127.0.0.1'
        );

        DocumentoService::excluir($documento, $user, '127.0.0.1');

        $logExclusao = LogAcessoDocumento::where('documento_id', $documento->id)
            ->where('acao', AcaoLogDocumento::Exclusao->value)
            ->first();

        $this->assertNotNull($logExclusao);
    }

    // --- verificarChecklist ---

    public function test_verificar_checklist_retorna_pendencias(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $contrato->load('documentos');

        $checklist = DocumentoService::verificarChecklist($contrato);

        $this->assertCount(4, $checklist);
        foreach ($checklist as $item) {
            $this->assertFalse($item['presente']);
        }
    }

    public function test_verificar_checklist_com_documentos(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create([
            'numero' => '900/' . date('Y'),
        ]);

        // Upload contrato original e publicacao oficial
        DocumentoService::upload(
            $this->criarPdfFake(),
            $contrato,
            TipoDocumentoContratual::ContratoOriginal,
            $user,
            '127.0.0.1'
        );
        DocumentoService::upload(
            $this->criarPdfFake(),
            $contrato,
            TipoDocumentoContratual::PublicacaoOficial,
            $user,
            '127.0.0.1'
        );

        $contrato->load('documentos');
        $checklist = DocumentoService::verificarChecklist($contrato);

        $presentes = collect($checklist)->filter(fn ($item) => $item['presente'])->count();
        $this->assertEquals(2, $presentes);
    }

    // --- calcularCompletude ---

    public function test_calcular_completude_incompleto_sem_docs(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $completude = DocumentoService::calcularCompletude($contrato);

        $this->assertEquals(StatusCompletudeDocumental::Incompleto, $completude);
    }

    public function test_calcular_completude_completo_com_todos_docs(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        // Upload dos 4 documentos obrigatorios
        foreach (DocumentoService::CHECKLIST_OBRIGATORIO as $tipo) {
            DocumentoService::upload(
                $this->criarPdfFake(),
                $contrato,
                $tipo,
                $user,
                '127.0.0.1'
            );
        }

        $contrato->refresh();
        $completude = DocumentoService::calcularCompletude($contrato);

        $this->assertEquals(StatusCompletudeDocumental::Completo, $completude);
    }

    // --- gerarIndicadoresDashboard ---

    public function test_gerar_indicadores_dashboard_retorna_chaves(): void
    {
        $indicadores = DocumentoService::gerarIndicadoresDashboard();

        $this->assertArrayHasKey('pct_completos', $indicadores);
        $this->assertArrayHasKey('total_sem_contrato_original', $indicadores);
        $this->assertArrayHasKey('total_aditivos_sem_doc', $indicadores);
        $this->assertArrayHasKey('secretarias_pendentes', $indicadores);
    }

    public function test_gerar_indicadores_dashboard_com_contratos(): void
    {
        Contrato::factory()->vigente()->create();
        Contrato::factory()->vigente()->create();

        $indicadores = DocumentoService::gerarIndicadoresDashboard();

        $this->assertArrayHasKey('pct_completos', $indicadores);
        $this->assertArrayHasKey('total_sem_contrato_original', $indicadores);
        $this->assertArrayHasKey('total_aditivos_sem_doc', $indicadores);
        $this->assertArrayHasKey('secretarias_pendentes', $indicadores);
    }
}
