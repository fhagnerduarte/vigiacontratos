<?php

namespace Tests\Feature\Policies;

use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use App\Policies\ServidorPolicy;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ServidorPolicyTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;
    protected ServidorPolicy $policy;

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
        $this->policy = new ServidorPolicy();
    }

    // --- ADMIN GERAL (bypass total via before) ---

    public function test_admin_geral_pode_visualizar_servidor_qualquer_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->view($this->admin, $servidor));
    }

    public function test_admin_geral_pode_excluir_servidor_qualquer_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->delete($this->admin, $servidor));
    }

    // --- GESTOR CONTRATO (restrito por secretaria, sem servidor.excluir) ---

    public function test_gestor_pode_visualizar_servidor_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->view($user, $servidor));
    }

    public function test_gestor_nao_pode_visualizar_servidor_de_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gestor_contrato', $secretariaDoUsuario);

        $servidor = Servidor::factory()->create(['secretaria_id' => $outraSecretaria->id]);

        $this->assertFalse($this->policy->view($user, $servidor));
    }

    public function test_gestor_pode_criar_servidor(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $this->assertTrue($this->policy->create($user));
    }

    public function test_gestor_pode_editar_servidor_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->update($user, $servidor));
    }

    public function test_gestor_nao_pode_editar_servidor_de_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gestor_contrato', $secretariaDoUsuario);

        $servidor = Servidor::factory()->create(['secretaria_id' => $outraSecretaria->id]);

        $this->assertFalse($this->policy->update($user, $servidor));
    }

    public function test_gestor_nao_pode_excluir_servidor(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        // gestor_contrato nao tem servidor.excluir
        $this->assertFalse($this->policy->delete($user, $servidor));
    }

    // --- FISCAL CONTRATO (somente visualizar) ---

    public function test_fiscal_pode_visualizar_servidor_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        $this->assertTrue($this->policy->view($user, $servidor));
    }

    public function test_fiscal_nao_pode_editar_servidor(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        // fiscal_contrato nao tem servidor.editar
        $this->assertFalse($this->policy->update($user, $servidor));
    }

    // --- CONTROLADORIA (perfil estrategico — bypass de secretaria) ---

    public function test_controladoria_pode_visualizar_servidor_qualquer_secretaria(): void
    {
        $secretariaA = Secretaria::factory()->create();
        $secretariaB = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('controladoria', $secretariaA);

        $servidor = Servidor::factory()->create(['secretaria_id' => $secretariaB->id]);

        $this->assertTrue($this->policy->view($user, $servidor));
    }
}
