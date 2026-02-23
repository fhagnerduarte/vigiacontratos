<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class EnsureUserHasPermissionTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_usuario_nao_autenticado_redirect(): void
    {
        $response = $this->get(route('tenant.contratos.index'));

        $response->assertRedirect();
    }

    public function test_admin_geral_sempre_passa(): void
    {
        $user = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        // Admin geral tem MFA obrigatório — simular sessão verificada
        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.contratos.index'));

        $response->assertStatus(200);
    }

    public function test_usuario_sem_permissao_403(): void
    {
        // fiscal_contrato nao possui permissao usuario.visualizar
        $user = $this->createUserWithRole('fiscal_contrato');
        $this->actingAs($user);

        $response = $this->get(route('tenant.users.index'));

        $response->assertStatus(403);
    }
}
