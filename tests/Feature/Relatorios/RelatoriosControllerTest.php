<?php

namespace Tests\Feature\Relatorios;

use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\HistoricoAlteracao;
use App\Models\Role;
use App\Models\Secretaria;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class RelatoriosControllerTest extends TestCase
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

    // ─── INDEX ──────────────────────────────────────────────

    public function test_index_renderiza_pagina_central_de_relatorios(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.relatorios.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.relatorios.index');
        $response->assertViewHas('contratos');
    }

    public function test_index_exige_permissao(): void
    {
        $role = Role::factory()->create(['nome' => 'sem_relatorio']);
        $user = User::factory()->create(['role_id' => $role->id, 'is_ativo' => true]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.relatorios.index'));

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            'Esperado 403 ou 404, recebido: ' . $response->getStatusCode()
        );
    }

    // ─── AUDITORIA FILTROS ──────────────────────────────────

    public function test_auditoria_filtros_renderiza_form(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.relatorios.auditoria'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.relatorios.auditoria-filtros');
        $response->assertViewHas('usuarios');
    }

    // ─── AUDITORIA PDF ──────────────────────────────────────

    public function test_auditoria_pdf_retorna_pdf(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.relatorios.auditoria.pdf'), [
            'data_inicio' => now()->subMonth()->format('Y-m-d'),
            'data_fim' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ─── AUDITORIA CSV ──────────────────────────────────────

    public function test_auditoria_csv_retorna_csv(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.relatorios.auditoria.csv'), [
            'data_inicio' => now()->subMonth()->format('Y-m-d'),
            'data_fim' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    // ─── CONFORMIDADE DOCUMENTAL ────────────────────────────

    public function test_conformidade_documental_pdf_retorna_pdf(): void
    {
        Contrato::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.relatorios.conformidade-documental'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ─── EFETIVIDADE MENSAL ─────────────────────────────────

    public function test_efetividade_mensal_renderiza_view(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.relatorios.efetividade-mensal'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.relatorios.efetividade-mensal');
        $response->assertViewHas('secretarias');
    }

    public function test_efetividade_mensal_pdf_retorna_pdf(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.relatorios.efetividade-mensal.pdf'), [
            'mes' => 1,
            'ano' => 2025,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_efetividade_mensal_excel_retorna_xlsx(): void
    {
        $response = $this->actAsAdmin()->get(
            route('tenant.relatorios.efetividade-mensal.excel', ['mes' => 1, 'ano' => 2025])
        );

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }

    // ─── EXPORTACOES EXCEL ──────────────────────────────────

    public function test_contratos_excel_retorna_xlsx(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.exportar.contratos'));

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }

    public function test_alertas_excel_retorna_xlsx(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.exportar.alertas'));

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }

    public function test_fornecedores_excel_retorna_xlsx(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.exportar.fornecedores'));

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }
}
