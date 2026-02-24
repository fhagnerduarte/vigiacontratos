<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class SessionInvalidationTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_desativar_usuario_redireciona_com_sucesso(): void
    {
        $admin = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $target = $this->createUserWithRole('fiscal_contrato');

        $response = $this->actingAs($admin)
            ->withSession(['mfa_verified' => true])
            ->delete(route('tenant.users.destroy', $target));

        $response->assertRedirect(route('tenant.users.index'));
        $response->assertSessionHas('success');

        $this->assertFalse($target->fresh()->is_ativo);
    }

    public function test_usuario_desativado_nao_acessa_mais_sistema(): void
    {
        $user = $this->createUserWithRole('gestor_contrato');

        // Simular que usuario esta logado
        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.dashboard'));
        $response->assertStatus(200);

        // Desativar o usuario
        $user->update(['is_ativo' => false]);

        // Proxima request: middleware EnsureUserIsActive deve forcar logout
        $response = $this->actingAs($user->fresh())
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.dashboard'));

        $response->assertRedirect(route('tenant.login'));
    }
}
