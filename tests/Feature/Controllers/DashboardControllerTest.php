<?php

namespace Tests\Feature\Controllers;

use App\Models\Contrato;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class DashboardControllerTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

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
    }

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);
    }

    // ─── INDEX ─────────────────────────────────────────────

    public function test_dashboard_acessivel_por_admin(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.dashboard.index');
    }

    public function test_dashboard_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.dashboard'));
        $response->assertRedirect();
    }

    public function test_dashboard_exibe_dados_com_contratos(): void
    {
        Contrato::factory()->vigente()->count(3)->create();

        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('dados');
    }

    public function test_dashboard_aceita_filtros(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.dashboard', [
            'tipo_contrato' => 'servico',
            'nivel_risco' => 'alto',
        ]));

        $response->assertStatus(200);
    }

    public function test_dashboard_funciona_sem_contratos(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));

        $response->assertStatus(200);
    }

    public function test_dashboard_visao_controlador_para_admin(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('dados');
    }

    public function test_dashboard_acessivel_por_secretario(): void
    {
        $user = $this->createUserWithRole('secretario');

        $response = $this->actingAs($user)->get(route('tenant.dashboard'));

        $response->assertStatus(200);
    }

    // ─── ATUALIZAR ─────────────────────────────────────────

    public function test_atualizar_exige_permissao_dashboard_atualizar(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        $response = $this->actingAs($user)->post(route('tenant.dashboard.atualizar'));

        $response->assertStatus(403);
    }

    public function test_atualizar_funciona_para_admin(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.dashboard.atualizar'));

        $response->assertRedirect(route('tenant.dashboard'));
        $response->assertSessionHas('success');
    }
}
