<?php

namespace Tests\Feature\Policies;

use App\Models\Secretaria;
use App\Models\User;
use App\Policies\SecretariaPolicy;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class SecretariaPolicyTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;
    protected SecretariaPolicy $policy;

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
        $this->policy = new SecretariaPolicy();
    }

    // ─── ADMIN GERAL (bypass total via before) ───────────

    public function test_admin_geral_pode_criar_secretaria(): void
    {
        $this->assertTrue($this->policy->create($this->admin));
    }

    public function test_admin_geral_pode_editar_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();

        $this->assertTrue($this->policy->update($this->admin, $secretaria));
    }

    public function test_admin_geral_pode_excluir_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();

        $this->assertTrue($this->policy->delete($this->admin, $secretaria));
    }

    // ─── GESTOR CONTRATO (somente visualizar) ────────────

    public function test_gestor_pode_visualizar_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        // gestor_contrato tem secretaria.visualizar
        $this->assertTrue($this->policy->view($user, $secretaria));
    }

    public function test_gestor_nao_pode_criar_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        // gestor_contrato nao tem secretaria.criar
        $this->assertFalse($this->policy->create($user));
    }

    public function test_gestor_nao_pode_editar_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        // gestor_contrato nao tem secretaria.editar
        $this->assertFalse($this->policy->update($user, $secretaria));
    }

    public function test_gestor_nao_pode_excluir_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        // gestor_contrato nao tem secretaria.excluir
        $this->assertFalse($this->policy->delete($user, $secretaria));
    }
}
