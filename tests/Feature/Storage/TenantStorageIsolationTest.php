<?php

namespace Tests\Feature\Storage;

use App\Enums\TipoDocumentoContratual;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DocumentoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class TenantStorageIsolationTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        Storage::fake('local');

        // Sobrescrever o tenant padrao com slug especifico para testes de path
        $this->tenant = Tenant::factory()->create(['slug' => 'prefeitura-teste']);
        app()->instance('tenant', $this->tenant);

        $this->admin = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
    }

    // ─── UPLOAD COM PREFIXO TENANT ──────────────────────

    public function test_upload_armazena_com_prefixo_tenant(): void
    {
        $contrato = Contrato::factory()->create();

        $pdfContent = '%PDF-1.4 test content for tenant isolation';
        $arquivo = UploadedFile::fake()->createWithContent('contrato.pdf', $pdfContent);

        $documento = DocumentoService::upload(
            arquivo: $arquivo,
            documentable: $contrato,
            tipoDocumento: TipoDocumentoContratual::ContratoOriginal,
            user: $this->admin,
            ip: '127.0.0.1',
        );

        $this->assertStringStartsWith('prefeitura-teste/', $documento->caminho);
        $this->assertStringContains('documentos/contratos/', $documento->caminho);
    }

    public function test_caminho_armazenado_contem_tenant_slug(): void
    {
        $contrato = Contrato::factory()->create();

        $pdfContent = '%PDF-1.4 test content';
        $arquivo = UploadedFile::fake()->createWithContent('doc.pdf', $pdfContent);

        $documento = DocumentoService::upload(
            arquivo: $arquivo,
            documentable: $contrato,
            tipoDocumento: TipoDocumentoContratual::PublicacaoOficial,
            user: $this->admin,
            ip: '127.0.0.1',
        );

        $expectedPrefix = "prefeitura-teste/documentos/contratos/{$contrato->id}/publicacao_oficial/";
        $this->assertStringStartsWith($expectedPrefix, $documento->caminho);
    }

    public function test_upload_sem_tenant_lanca_exception(): void
    {
        app()->forgetInstance('tenant');

        $contrato = Contrato::factory()->create();
        $pdfContent = '%PDF-1.4 test content';
        $arquivo = UploadedFile::fake()->createWithContent('doc.pdf', $pdfContent);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Contexto de tenant nao disponivel');

        DocumentoService::upload(
            arquivo: $arquivo,
            documentable: $contrato,
            tipoDocumento: TipoDocumentoContratual::ContratoOriginal,
            user: $this->admin,
            ip: '127.0.0.1',
        );
    }

    // ─── DOWNLOAD COM VALIDACAO DE TENANT ────────────────

    public function test_download_documento_com_path_tenant_correto(): void
    {
        $caminho = 'prefeitura-teste/documentos/contratos/1/contrato_original/test.pdf';
        Storage::disk('local')->put($caminho, '%PDF-1.4 test content');

        $contrato = Contrato::factory()->create();
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'caminho' => $caminho,
            'integridade_comprometida' => false,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = DocumentoService::download($documento, $this->admin, '127.0.0.1');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_download_documento_legado_sem_prefixo_tenant_permitido(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'path legado');
            });

        $caminho = 'documentos/contratos/1/contrato_original/legacy.pdf';
        Storage::disk('local')->put($caminho, '%PDF-1.4 legacy content');

        $contrato = Contrato::factory()->create();
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'caminho' => $caminho,
            'integridade_comprometida' => false,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = DocumentoService::download($documento, $this->admin, '127.0.0.1');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_download_documento_de_outro_tenant_bloqueado(): void
    {
        $caminho = 'outro-tenant/documentos/contratos/1/contrato_original/test.pdf';
        Storage::disk('local')->put($caminho, '%PDF-1.4 other tenant content');

        $contrato = Contrato::factory()->create();
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'caminho' => $caminho,
            'integridade_comprometida' => false,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Acesso negado: documento nao pertence ao tenant atual');

        DocumentoService::download($documento, $this->admin, '127.0.0.1');
    }

    // ─── INTEGRIDADE COM NOVO PATH ───────────────────────

    public function test_verificar_integridade_documento_com_path_tenant(): void
    {
        $pdfContent = '%PDF-1.4 integrity test';
        $hash = hash('sha256', $pdfContent);

        $caminho = 'prefeitura-teste/documentos/contratos/1/contrato_original/test.pdf';
        Storage::disk('local')->put($caminho, $pdfContent);

        $contrato = Contrato::factory()->create();
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'caminho' => $caminho,
            'hash_integridade' => $hash,
            'integridade_comprometida' => false,
            'uploaded_by' => $this->admin->id,
        ]);

        $status = DocumentoService::verificarIntegridade($documento);

        $this->assertEquals(\App\Enums\StatusIntegridade::Ok, $status);
    }

    // ─── HELPER ──────────────────────────────────────────

    /**
     * Assert customizado para verificar substring no caminho.
     */
    protected function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '{$haystack}' contains '{$needle}'."
        );
    }
}
