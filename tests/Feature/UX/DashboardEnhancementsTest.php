<?php

namespace Tests\Feature\UX;

use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class DashboardEnhancementsTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

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

    // ==========================================
    // Dashboard View Enhancements
    // ==========================================

    public function test_dashboard_contem_chart_score_gestao(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('chartScoreGestao', false);
    }

    public function test_dashboard_contem_data_countup_nos_cards(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('data-countup', false);
    }

    public function test_dashboard_contem_tooltips_nos_cards(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('data-bs-toggle="tooltip"', false);
    }

    public function test_dashboard_contem_acoes_rapidas(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Novo Contrato');
        $response->assertSee('Alertas');
        $response->assertSee('Relatorios');
    }

    public function test_dashboard_contem_form_atualizar_async(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('formAtualizarDashboard', false);
        $response->assertSee('btnAtualizarDashboard', false);
        $response->assertSee('spinner-border', false);
    }

    public function test_dashboard_score_data_no_javascript(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('score:', false);
        $response->assertSee('cor_hex', false);
    }

    // ==========================================
    // Dashboard Atualizar AJAX
    // ==========================================

    public function test_atualizar_retorna_json_quando_aceita_json(): void
    {
        $response = $this->actAsAdmin()
            ->postJson(route('tenant.dashboard.atualizar'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Dados do dashboard atualizados com sucesso.',
        ]);
    }

    public function test_atualizar_retorna_redirect_sem_json(): void
    {
        $response = $this->actAsAdmin()
            ->post(route('tenant.dashboard.atualizar'));

        $response->assertRedirect(route('tenant.dashboard'));
        $response->assertSessionHas('success');
    }

    // ==========================================
    // Assets
    // ==========================================

    public function test_dashboard_charts_js_existe(): void
    {
        $this->assertFileExists(public_path('assets/js/dashboard-charts.js'));
    }

    public function test_dashboard_enhancements_js_existe(): void
    {
        $this->assertFileExists(public_path('assets/js/dashboard-enhancements.js'));
    }

    public function test_dashboard_charts_js_contem_radialbar(): void
    {
        $content = file_get_contents(public_path('assets/js/dashboard-charts.js'));
        $this->assertStringContainsString('radialBar', $content);
        $this->assertStringContainsString('chartScoreGestao', $content);
    }

    public function test_dashboard_charts_js_contem_click_events(): void
    {
        $content = file_get_contents(public_path('assets/js/dashboard-charts.js'));
        $this->assertStringContainsString('dataPointSelection', $content);
    }

    public function test_dashboard_enhancements_js_contem_countup(): void
    {
        $content = file_get_contents(public_path('assets/js/dashboard-enhancements.js'));
        $this->assertStringContainsString('data-countup', $content);
        $this->assertStringContainsString('animateCounter', $content);
    }

    public function test_custom_css_contem_estilos_dashboard(): void
    {
        $content = file_get_contents(public_path('assets/css/custom.css'));
        $this->assertStringContainsString('apexcharts-pie-area', $content);
        $this->assertStringContainsString('cursor: pointer', $content);
    }
}
