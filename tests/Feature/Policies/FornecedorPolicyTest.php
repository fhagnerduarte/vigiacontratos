<?php

namespace Tests\Feature\Policies;

use App\Models\Fornecedor;
use App\Models\Permission;
use App\Models\Secretaria;
use App\Models\User;
use App\Policies\FornecedorPolicy;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class FornecedorPolicyTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;
    protected FornecedorPolicy $policy;

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
        $this->policy = new FornecedorPolicy();
    }

    // ─── ADMIN GERAL (bypass total via before) ───────────

    public function test_admin_geral_pode_visualizar_qualquer_fornecedor(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $this->assertTrue($this->policy->view($this->admin, $fornecedor));
    }

    public function test_admin_geral_pode_excluir_qualquer_fornecedor(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $this->assertTrue($this->policy->delete($this->admin, $fornecedor));
    }

    // ─── GESTOR CONTRATO (permissoes parciais) ───────────

    public function test_gestor_pode_visualizar_fornecedor(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $fornecedor = Fornecedor::factory()->create();

        // gestor_contrato tem fornecedor.visualizar
        $this->assertTrue($this->policy->view($user, $fornecedor));
    }

    public function test_gestor_pode_criar_fornecedor(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        // gestor_contrato tem fornecedor.criar
        $this->assertTrue($this->policy->create($user));
    }

    public function test_gestor_pode_editar_fornecedor(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $fornecedor = Fornecedor::factory()->create();

        // gestor_contrato tem fornecedor.editar
        $this->assertTrue($this->policy->update($user, $fornecedor));
    }

    public function test_gestor_nao_pode_excluir_fornecedor(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $fornecedor = Fornecedor::factory()->create();

        // gestor_contrato nao tem fornecedor.excluir
        $this->assertFalse($this->policy->delete($user, $fornecedor));
    }

    // ─── FISCAL CONTRATO (somente visualizar) ────────────

    public function test_fiscal_pode_visualizar_fornecedor(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        $fornecedor = Fornecedor::factory()->create();

        // fiscal_contrato tem fornecedor.visualizar
        $this->assertTrue($this->policy->view($user, $fornecedor));
    }

    public function test_fiscal_nao_pode_criar_fornecedor(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        // fiscal_contrato nao tem fornecedor.criar
        $this->assertFalse($this->policy->create($user));
    }

    public function test_fiscal_nao_pode_editar_fornecedor(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        $fornecedor = Fornecedor::factory()->create();

        // fiscal_contrato nao tem fornecedor.editar
        $this->assertFalse($this->policy->update($user, $fornecedor));
    }
}
