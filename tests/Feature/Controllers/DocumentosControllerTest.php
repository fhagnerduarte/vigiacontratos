<?php

namespace Tests\Feature\Controllers;

use App\Enums\TipoDocumentoContratual;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class DocumentosControllerTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        Storage::fake('s3');
        $this->admin = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
    }

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);
    }

    // ─── INDEX (Central de Documentos) ─────────────────────

    public function test_index_exibe_central_de_documentos(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.documentos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.documentos.index');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.documentos.index'));
        $response->assertRedirect();
    }

    public function test_index_acessivel_por_usuario_com_permissao(): void
    {
        // fiscal_contrato tem documento.visualizar no RolePermissionSeeder
        $user = $this->createUserWithRole('gestor_contrato');
        $response = $this->actingAs($user)->get(route('tenant.documentos.index'));
        $response->assertStatus(200);
    }

    // ─── STORE (Upload) ────────────────────────────────────

    public function test_store_upload_documento_pdf_com_sucesso(): void
    {
        $contrato = Contrato::factory()->create();

        // Criar PDF real com magic bytes válidos
        $pdfContent = '%PDF-1.4 test content';
        $arquivo = UploadedFile::fake()->createWithContent('contrato.pdf', $pdfContent);

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.documentos.store', $contrato),
            [
                'arquivo' => $arquivo,
                'tipo_documento' => TipoDocumentoContratual::ContratoOriginal->value,
            ]
        );

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
    }

    public function test_store_rejeita_arquivo_nao_pdf(): void
    {
        $contrato = Contrato::factory()->create();
        $arquivo = UploadedFile::fake()->create('planilha.xlsx', 500, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.documentos.store', $contrato),
            [
                'arquivo' => $arquivo,
                'tipo_documento' => TipoDocumentoContratual::ContratoOriginal->value,
            ]
        );

        $response->assertSessionHasErrors('arquivo');
    }

    public function test_store_rejeita_arquivo_maior_que_20mb(): void
    {
        $contrato = Contrato::factory()->create();
        $arquivo = UploadedFile::fake()->create('grande.pdf', 21000, 'application/pdf');

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.documentos.store', $contrato),
            [
                'arquivo' => $arquivo,
                'tipo_documento' => TipoDocumentoContratual::ContratoOriginal->value,
            ]
        );

        $response->assertSessionHasErrors('arquivo');
    }

    public function test_store_valida_tipo_documento_obrigatorio(): void
    {
        $contrato = Contrato::factory()->create();
        $arquivo = UploadedFile::fake()->create('doc.pdf', 500, 'application/pdf');

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.documentos.store', $contrato),
            [
                'arquivo' => $arquivo,
                'tipo_documento' => '',
            ]
        );

        $response->assertSessionHasErrors('tipo_documento');
    }

    // ─── DOWNLOAD ──────────────────────────────────────────

    public function test_download_documento_requer_autenticacao(): void
    {
        $documento = Documento::factory()->create();

        $response = $this->get(route('tenant.documentos.download', $documento));
        $response->assertRedirect();
    }

    // ─── DESTROY ───────────────────────────────────────────

    public function test_destroy_soft_deleta_documento(): void
    {
        $documento = Documento::factory()->create(['uploaded_by' => $this->admin->id]);

        $response = $this->actAsAdmin()->delete(route('tenant.documentos.destroy', $documento));

        $response->assertRedirect();
        $this->assertSoftDeleted($documento);
    }

    public function test_destroy_exige_permissao(): void
    {
        $documento = Documento::factory()->create();
        $user = $this->createUserWithRole('fiscal_contrato');

        $response = $this->actingAs($user)->delete(route('tenant.documentos.destroy', $documento));

        $response->assertStatus(403);
    }

    // ─── UPLOAD AVANÇADO ─────────────────────────────────────

    public function test_store_upload_gera_hash_integridade(): void
    {
        $contrato = Contrato::factory()->create();

        $pdfContent = '%PDF-1.4 test content for hash';
        $arquivo = UploadedFile::fake()->createWithContent('contrato.pdf', $pdfContent);

        $this->actAsAdmin()->post(
            route('tenant.contratos.documentos.store', $contrato),
            [
                'arquivo' => $arquivo,
                'tipo_documento' => TipoDocumentoContratual::ContratoOriginal->value,
            ]
        );

        $documento = Documento::where('documentable_id', $contrato->id)
            ->where('documentable_type', get_class($contrato))
            ->latest()
            ->first();

        $this->assertNotNull($documento);
        $this->assertNotNull($documento->hash_integridade);
    }

    public function test_store_versionamento_incrementa_versao(): void
    {
        $contrato = Contrato::factory()->create();
        $tipo = TipoDocumentoContratual::ContratoOriginal->value;

        // Primeiro upload
        $pdf1 = UploadedFile::fake()->createWithContent('contrato_v1.pdf', '%PDF-1.4 version 1');
        $this->actAsAdmin()->post(
            route('tenant.contratos.documentos.store', $contrato),
            ['arquivo' => $pdf1, 'tipo_documento' => $tipo]
        );

        // Segundo upload mesmo tipo
        $pdf2 = UploadedFile::fake()->createWithContent('contrato_v2.pdf', '%PDF-1.4 version 2');
        $this->actAsAdmin()->post(
            route('tenant.contratos.documentos.store', $contrato),
            ['arquivo' => $pdf2, 'tipo_documento' => $tipo]
        );

        $docs = Documento::where('documentable_id', $contrato->id)
            ->where('tipo_documento', $tipo)
            ->orderBy('versao')
            ->get();

        $this->assertCount(2, $docs);
        $this->assertEquals(1, $docs[0]->versao);
        $this->assertFalse((bool) $docs[0]->is_versao_atual);
        $this->assertEquals(2, $docs[1]->versao);
        $this->assertTrue((bool) $docs[1]->is_versao_atual);
    }

    // ─── DOWNLOAD AVANÇADO ───────────────────────────────────

    public function test_download_documento_integridade_comprometida_redireciona(): void
    {
        $documento = Documento::factory()->create([
            'uploaded_by' => $this->admin->id,
            'integridade_comprometida' => true,
        ]);

        $response = $this->actAsAdmin()->get(route('tenant.documentos.download', $documento));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_download_documento_integro_retorna_stream(): void
    {
        Storage::fake('local');

        $caminho = 'documentos/contratos/1/contrato_original/test.pdf';
        Storage::disk('local')->put($caminho, '%PDF-1.4 test content');

        $documento = Documento::factory()->create([
            'uploaded_by' => $this->admin->id,
            'caminho' => $caminho,
            'integridade_comprometida' => false,
        ]);

        $response = $this->actAsAdmin()->get(route('tenant.documentos.download', $documento));

        $response->assertStatus(200);
    }

    // ─── VERIFICAR INTEGRIDADE ───────────────────────────────

    public function test_verificar_integridade_endpoint(): void
    {
        $documento = Documento::factory()->create([
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actAsAdmin()->post(
            route('tenant.documentos.verificar-integridade', $documento)
        );

        $response->assertRedirect();
    }

    public function test_verificar_integridade_exige_permissao(): void
    {
        $documento = Documento::factory()->create();
        $user = $this->createUserWithRole('fiscal_contrato');

        $response = $this->actingAs($user)->post(
            route('tenant.documentos.verificar-integridade', $documento)
        );

        $response->assertStatus(403);
    }

    // ─── INDEX FILTROS ───────────────────────────────────────

    public function test_index_com_filtro_numero_contrato(): void
    {
        Contrato::factory()->create(['numero' => '001']);

        $response = $this->actAsAdmin()->get(route('tenant.documentos.index', [
            'numero_contrato' => '001',
        ]));

        $response->assertStatus(200);
    }

    public function test_index_com_filtro_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.documentos.index', [
            'secretaria_id' => $secretaria->id,
        ]));

        $response->assertStatus(200);
    }

    public function test_index_view_recebe_indicadores(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.documentos.index'));

        $response->assertStatus(200);
        $response->assertViewHas('indicadores');
    }
}
