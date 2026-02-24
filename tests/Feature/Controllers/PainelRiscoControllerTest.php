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

    // ─── DADOS AVANÇADOS ─────────────────────────────────────

    public function test_index_view_recebe_ranking(): void
    {
        Contrato::factory()->vigente()->count(3)->create();

        $response = $this->actAsAdmin()->get(route('tenant.painel-risco.index'));

        $response->assertStatus(200);
        $response->assertViewHas('ranking');
    }

    public function test_index_view_recebe_mapa_secretarias(): void
    {
        Contrato::factory()->vigente()->count(2)->create();

        $response = $this->actAsAdmin()->get(route('tenant.painel-risco.index'));

        $response->assertStatus(200);
        $response->assertViewHas('mapaSecretarias');
    }

    public function test_index_indicadores_incluem_campos_obrigatorios(): void
    {
        Contrato::factory()->vigente()->count(2)->create();
        Contrato::factory()->altoRisco()->create();

        $response = $this->actAsAdmin()->get(route('tenant.painel-risco.index'));

        $indicadores = $response->viewData('indicadores');
        $this->assertArrayHasKey('total_ativos', $indicadores);
        $this->assertArrayHasKey('alto_risco', $indicadores);
        $this->assertArrayHasKey('pct_alto_risco', $indicadores);
        $this->assertArrayHasKey('vencendo_30d', $indicadores);
        $this->assertArrayHasKey('aditivos_acima_20', $indicadores);
        $this->assertArrayHasKey('sem_doc_obrigatoria', $indicadores);
    }

    public function test_index_acessivel_por_controladoria(): void
    {
        $user = $this->createUserWithRole('controladoria');

        $response = $this->actingAs($user)->get(route('tenant.painel-risco.index'));

        $response->assertStatus(200);
    }

    public function test_index_acessivel_por_gestor_contrato(): void
    {
        $user = $this->createUserWithRole('gestor_contrato');

        $response = $this->actingAs($user)->get(route('tenant.painel-risco.index'));

        $response->assertStatus(200);
    }

    public function test_exportar_tce_retorna_pdf_com_nome_arquivo(): void
    {
        Contrato::factory()->altoRisco()->create();

        $response = $this->actAsAdmin()->get(route('tenant.painel-risco.exportar-tce'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('relatorio-risco-tce', $contentDisposition);
    }

    public function test_exportar_tce_acessivel_por_controladoria(): void
    {
        $user = $this->createUserWithRole('controladoria');

        $response = $this->actingAs($user)->get(route('tenant.painel-risco.exportar-tce'));

        $response->assertStatus(200);
    }
}
