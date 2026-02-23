<?php

namespace Tests\Feature\Controllers;

use App\Models\Contrato;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class PainelRiscoControllerTest extends TestCase
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

    public function test_index_exibe_painel_de_risco(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.painel-risco.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.painel-risco.index');
    }

    public function test_index_exibe_indicadores(): void
    {
        Contrato::factory()->vigente()->count(3)->create();
        Contrato::factory()->altoRisco()->count(2)->create();

        $response = $this->actAsAdmin()->get(route('tenant.painel-risco.index'));

        $response->assertStatus(200);
        $response->assertViewHas('indicadores');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.painel-risco.index'));
        $response->assertRedirect();
    }

    public function test_index_exige_permissao_painel_risco(): void
    {
        $role = \App\Models\Role::factory()->create(['nome' => 'role_sem_permissao']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $response = $this->actingAs($user)->get(route('tenant.painel-risco.index'));
        $response->assertStatus(403);
    }

    public function test_index_funciona_sem_contratos(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.painel-risco.index'));
        $response->assertStatus(200);
    }

    // ─── EXPORTAR TCE ──────────────────────────────────────

    public function test_exportar_tce_gera_pdf(): void
    {
        Contrato::factory()->altoRisco()->count(2)->create();

        $response = $this->actAsAdmin()->get(route('tenant.painel-risco.exportar-tce'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_exportar_tce_exige_permissao(): void
    {
        $role = \App\Models\Role::factory()->create(['nome' => 'role_sem_export']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $response = $this->actingAs($user)->get(route('tenant.painel-risco.exportar-tce'));
        $response->assertStatus(403);
    }

    public function test_exportar_tce_funciona_sem_contratos_criticos(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.painel-risco.exportar-tce'));
        $response->assertStatus(200);
    }
}
