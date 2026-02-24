<?php

namespace Tests\Feature\Policies;

use App\Enums\TipoDocumentoContratual;
use App\Models\Aditivo;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\Permission;
use App\Models\Secretaria;
use App\Models\User;
use App\Policies\DocumentoPolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class DocumentoPolicyTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;
    protected DocumentoPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->admin = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
        $this->policy = new DocumentoPolicy();
    }

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);
    }

    // ─── ADMIN GERAL (bypass total via before) ───────────

    public function test_admin_geral_pode_visualizar_qualquer_documento(): void
    {
        $secretariaA = Secretaria::factory()->create();
        $contrato = Contrato::factory()->create(['secretaria_id' => $secretariaA->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->policy->view($this->admin, $documento));
    }

    public function test_admin_geral_pode_download_qualquer_documento(): void
    {
        $secretariaA = Secretaria::factory()->create();
        $contrato = Contrato::factory()->create(['secretaria_id' => $secretariaA->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->policy->download($this->admin, $documento));
    }

    public function test_admin_geral_pode_excluir_qualquer_documento(): void
    {
        $contrato = Contrato::factory()->create();
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->policy->delete($this->admin, $documento));
    }

    public function test_admin_geral_pode_verificar_integridade(): void
    {
        $contrato = Contrato::factory()->create();
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->policy->verificarIntegridade($this->admin, $documento));
    }

    // ─── PERFIS ESTRATEGICOS (bypass de secretaria) ──────

    public function test_controladoria_pode_visualizar_documento_qualquer_secretaria(): void
    {
        $secretariaA = Secretaria::factory()->create();
        $secretariaB = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('controladoria', $secretariaA);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretariaB->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->policy->view($user, $documento));
    }

    public function test_gabinete_pode_download_documento_qualquer_secretaria(): void
    {
        $secretariaA = Secretaria::factory()->create();
        $secretariaB = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gabinete', $secretariaA);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretariaB->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->policy->download($user, $documento));
    }

    // ─── GESTOR CONTRATO (restrito por secretaria) ───────

    public function test_gestor_pode_visualizar_documento_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->policy->view($user, $documento));
    }

    public function test_gestor_nao_pode_visualizar_documento_de_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gestor_contrato', $secretariaDoUsuario);

        $contrato = Contrato::factory()->create(['secretaria_id' => $outraSecretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertFalse($this->policy->view($user, $documento));
    }

    public function test_gestor_pode_download_documento_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->policy->download($user, $documento));
    }

    public function test_gestor_nao_pode_download_documento_de_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gestor_contrato', $secretariaDoUsuario);

        $contrato = Contrato::factory()->create(['secretaria_id' => $outraSecretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertFalse($this->policy->download($user, $documento));
    }

    // ─── FISCAL CONTRATO (restrito por secretaria) ───────

    public function test_fiscal_pode_visualizar_documento_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->policy->view($user, $documento));
    }

    public function test_fiscal_nao_pode_visualizar_documento_de_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretariaDoUsuario);

        $contrato = Contrato::factory()->create(['secretaria_id' => $outraSecretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertFalse($this->policy->view($user, $documento));
    }

    // ─── PERMISSOES INSUFICIENTES ────────────────────────

    public function test_fiscal_nao_pode_excluir_documento(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        // fiscal_contrato nao tem documento.excluir
        $this->assertFalse($this->policy->delete($user, $documento));
    }

    public function test_fiscal_nao_pode_verificar_integridade(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        // fiscal_contrato nao tem auditoria.verificar_integridade
        $this->assertFalse($this->policy->verificarIntegridade($user, $documento));
    }

    // ─── DOCUMENTO DE ADITIVO (resolve via contrato) ─────

    public function test_documento_de_aditivo_resolvido_via_contrato_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $documento = Documento::factory()->create([
            'documentable_type' => Aditivo::class,
            'documentable_id' => $aditivo->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->policy->view($user, $documento));
    }

    public function test_documento_de_aditivo_bloqueado_para_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gestor_contrato', $secretariaDoUsuario);

        $contrato = Contrato::factory()->create(['secretaria_id' => $outraSecretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $documento = Documento::factory()->create([
            'documentable_type' => Aditivo::class,
            'documentable_id' => $aditivo->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertFalse($this->policy->view($user, $documento));
    }

    // ─── INTEGRACAO COM CONTROLLER ───────────────────────

    public function test_policy_integrada_download_controller(): void
    {
        Storage::fake('local');

        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);

        $caminho = 'documentos/contratos/' . $contrato->id . '/contrato_original/test.pdf';
        Storage::disk('local')->put($caminho, '%PDF-1.4 test content');

        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'caminho' => $caminho,
            'integridade_comprometida' => false,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.documentos.download', $documento));

        $response->assertStatus(200);
    }

    public function test_policy_integrada_download_bloqueado_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gestor_contrato', $secretariaDoUsuario);

        $contrato = Contrato::factory()->create(['secretaria_id' => $outraSecretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.documentos.download', $documento));

        $response->assertStatus(403);
    }

    public function test_policy_integrada_destroy_bloqueado_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        // Admin tem permissao documento.excluir, mas vamos testar via Policy secretaria
        // Usamos admin vinculado a secretaria especifica (tirando do perfil estrategico)
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretariaDoUsuario);
        // gestor_contrato nao tem documento.excluir, entao sera 403 de qualquer forma

        $contrato = Contrato::factory()->create(['secretaria_id' => $outraSecretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->delete(route('tenant.documentos.destroy', $documento));

        $response->assertStatus(403);
    }

    // ─── PERMISSAO TEMPORARIA EXPIRADA ───────────────────

    public function test_usuario_com_permissao_temporaria_expirada_nao_acessa(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        // Adicionar permissao temporaria ja expirada
        $perm = Permission::where('nome', 'documento.excluir')->first();
        $user->permissions()->attach($perm->id, [
            'expires_at' => now()->subHour(),
            'concedido_por' => $this->admin->id,
        ]);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $this->assertFalse($this->policy->delete($user, $documento));
    }
}
