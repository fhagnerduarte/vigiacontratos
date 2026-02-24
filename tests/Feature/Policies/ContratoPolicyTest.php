<?php

namespace Tests\Feature\Policies;

use App\Enums\StatusContrato;
use App\Models\Contrato;
use App\Models\Permission;
use App\Models\Secretaria;
use App\Models\User;
use App\Policies\ContratoPolicy;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ContratoPolicyTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;
    protected ContratoPolicy $policy;

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
        $this->policy = new ContratoPolicy();
    }

    // ─── ADMIN GERAL (bypass total via before) ───────────

    public function test_admin_geral_pode_visualizar_qualquer_contrato(): void
    {
        $secretaria = Secretaria::factory()->create();
        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->view($this->admin, $contrato));
    }

    public function test_admin_geral_pode_excluir_qualquer_contrato(): void
    {
        $secretaria = Secretaria::factory()->create();
        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->delete($this->admin, $contrato));
    }

    public function test_admin_geral_pode_editar_qualquer_contrato(): void
    {
        $secretaria = Secretaria::factory()->create();
        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->update($this->admin, $contrato));
    }

    public function test_admin_geral_pode_criar_contrato(): void
    {
        $this->assertTrue($this->policy->create($this->admin));
    }

    // ─── PERFIS ESTRATEGICOS (bypass de secretaria) ──────

    public function test_controladoria_pode_visualizar_contrato_qualquer_secretaria(): void
    {
        $secretariaA = Secretaria::factory()->create();
        $secretariaB = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('controladoria', $secretariaA);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretariaB->id]);

        $this->assertTrue($this->policy->view($user, $contrato));
    }

    public function test_gabinete_pode_visualizar_contrato_qualquer_secretaria(): void
    {
        $secretariaA = Secretaria::factory()->create();
        $secretariaB = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gabinete', $secretariaA);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretariaB->id]);

        $this->assertTrue($this->policy->view($user, $contrato));
    }

    // ─── GESTOR CONTRATO (restrito por secretaria) ───────

    public function test_gestor_pode_visualizar_contrato_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->view($user, $contrato));
    }

    public function test_gestor_nao_pode_visualizar_contrato_de_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gestor_contrato', $secretariaDoUsuario);

        $contrato = Contrato::factory()->create(['secretaria_id' => $outraSecretaria->id]);

        $this->assertFalse($this->policy->view($user, $contrato));
    }

    public function test_gestor_pode_editar_contrato_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $contrato = Contrato::factory()->vigente()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->update($user, $contrato));
    }

    public function test_gestor_nao_pode_editar_contrato_de_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gestor_contrato', $secretariaDoUsuario);

        $contrato = Contrato::factory()->vigente()->create(['secretaria_id' => $outraSecretaria->id]);

        $this->assertFalse($this->policy->update($user, $contrato));
    }

    public function test_gestor_nao_pode_excluir_contrato_sem_permissao(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);

        // gestor_contrato nao tem contrato.excluir
        $this->assertFalse($this->policy->delete($user, $contrato));
    }

    public function test_gestor_pode_criar_contrato(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        // gestor_contrato tem contrato.criar
        $this->assertTrue($this->policy->create($user));
    }

    // ─── FISCAL CONTRATO (restrito por secretaria) ───────

    public function test_fiscal_pode_visualizar_contrato_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->view($user, $contrato));
    }

    public function test_fiscal_nao_pode_editar_contrato(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        $contrato = Contrato::factory()->vigente()->create(['secretaria_id' => $secretaria->id]);

        // fiscal_contrato nao tem contrato.editar
        $this->assertFalse($this->policy->update($user, $contrato));
    }

    // ─── BLOQUEIO DE CONTRATO VENCIDO (RN-006) ──────────

    public function test_editar_contrato_vencido_bloqueado_mesmo_com_permissao(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $contrato = Contrato::factory()->vencido()->create(['secretaria_id' => $secretaria->id]);

        // gestor tem contrato.editar, mas contrato vencido bloqueia edicao (RN-006)
        $this->assertFalse($this->policy->update($user, $contrato));
    }

    // ─── PERMISSAO TEMPORARIA EXPIRADA ───────────────────

    public function test_usuario_com_permissao_temporaria_expirada_nao_cria_contrato(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        // Adicionar permissao temporaria ja expirada
        $perm = Permission::where('nome', 'contrato.criar')->first();
        $user->permissions()->attach($perm->id, [
            'expires_at' => now()->subHour(),
            'concedido_por' => $this->admin->id,
        ]);

        // fiscal_contrato nao tem contrato.criar e permissao temporaria expirou
        $this->assertFalse($this->policy->create($user));
    }
}
