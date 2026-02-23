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
        $this->actingAsAdmin();

        $response = $this->get(route('tenant.contratos.index'));

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
