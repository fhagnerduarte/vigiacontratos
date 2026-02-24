<?php

namespace Tests\Feature\Middleware;

use App\Models\Tenant;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class EnsureUserIsActiveMiddlewareTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_usuario_ativo_acessa_normalmente(): void
    {
        $user = $this->createAdminUser([
            'is_ativo' => true,
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.dashboard'));

        $response->assertStatus(200);
    }

    public function test_usuario_inativo_e_deslogado_e_redirecionado(): void
    {
        $user = $this->createAdminUser([
            'is_ativo' => false,
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.dashboard'));

        $response->assertRedirect(route('tenant.login'));
    }

    public function test_usuario_inativo_recebe_mensagem_de_erro(): void
    {
        $user = $this->createAdminUser([
            'is_ativo' => false,
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.dashboard'));

        $response->assertSessionHasErrors('email');
    }

    public function test_tenant_inativo_bloqueia_acesso(): void
    {
        $user = $this->createAdminUser([
            'is_ativo' => true,
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        // Desativar tenant — SetTenantConnection middleware retorna 404 para tenants inativos
        $this->tenant->update(['is_ativo' => false]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.dashboard'));

        // SetTenantConnection retorna 404 antes do EnsureUserIsActive
        $response->assertStatus(404);
    }

    public function test_sem_usuario_autenticado_segue_normalmente(): void
    {
        // Guest deveria ser redirecionado pelo auth middleware, nao pelo user.active
        $response = $this->get(route('tenant.dashboard'));

        $response->assertRedirect(route('tenant.login'));
    }
}
