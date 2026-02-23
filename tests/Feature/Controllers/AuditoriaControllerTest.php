<?php

namespace Tests\Feature\Controllers;

use App\Enums\AcaoLogDocumento;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\HistoricoAlteracao;
use App\Models\LogAcessoDocumento;
use App\Models\LoginLog;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AuditoriaControllerTest extends TestCase
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

    public function test_index_exibe_trilha_de_auditoria(): void
    {
        HistoricoAlteracao::create([
            'auditable_type' => 'App\\Models\\Contrato',
            'auditable_id' => 1,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'Antigo',
            'valor_novo' => 'Novo',
            'user_id' => $this->admin->id,
            'role_nome' => 'administrador_geral',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        LoginLog::create([
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test',
            'success' => true,
            'created_at' => now(),
        ]);

        $response = $this->actAsAdmin()->get(route('tenant.auditoria.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.auditoria.index');
        $response->assertViewHas('paginator');
        $response->assertViewHas('totalAlteracoes');
        $response->assertViewHas('totalLogins');
        $response->assertViewHas('totalGeral');

        // Verificar que existem registros
        $this->assertGreaterThanOrEqual(1, $response->viewData('totalAlteracoes'));
        $this->assertGreaterThanOrEqual(1, $response->viewData('totalLogins'));
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.auditoria.index'));
        $response->assertRedirect();
    }

    public function test_index_usuario_sem_permissao_retorna_403(): void
    {
        $role = Role::factory()->create(['nome' => 'audit_sem_perm_' . uniqid()]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('tenant.auditoria.index'));
        $response->assertStatus(403);
    }

    public function test_index_filtra_por_tipo_acao_alteracao(): void
    {
        HistoricoAlteracao::create([
            'auditable_type' => 'App\\Models\\Contrato',
            'auditable_id' => 1,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'Antigo',
            'valor_novo' => 'Novo',
            'user_id' => $this->admin->id,
            'role_nome' => 'administrador_geral',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actAsAdmin()->get(route('tenant.auditoria.index', [
            'tipo_acao' => 'alteracao',
        ]));

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->viewData('totalAlteracoes'));
        // Filtro login deve retornar 0 quando filtrando por alteracao
        $this->assertEquals(0, $response->viewData('totalLogins'));
        $this->assertEquals(0, $response->viewData('totalAcessosDocs'));
    }

    public function test_index_filtra_por_tipo_acao_login(): void
    {
        LoginLog::create([
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'success' => true,
            'created_at' => now(),
        ]);

        $response = $this->actAsAdmin()->get(route('tenant.auditoria.index', [
            'tipo_acao' => 'login',
        ]));

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->viewData('totalLogins'));
        // Filtro alteracao deve retornar 0 quando filtrando por login
        $this->assertEquals(0, $response->viewData('totalAlteracoes'));
    }

    public function test_index_filtra_por_user_id(): void
    {
        $outroUser = User::factory()->create();

        HistoricoAlteracao::create([
            'auditable_type' => 'App\\Models\\Contrato',
            'auditable_id' => 1,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'A',
            'valor_novo' => 'B',
            'user_id' => $outroUser->id,
            'role_nome' => 'gestor_contrato',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        // Filtrar por outroUser — deve encontrar apenas os registros dele
        $response = $this->actAsAdmin()->get(route('tenant.auditoria.index', [
            'tipo_acao' => 'alteracao',
            'user_id' => $outroUser->id,
        ]));

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->viewData('totalAlteracoes'));
    }

    public function test_index_filtra_por_entidade(): void
    {
        HistoricoAlteracao::create([
            'auditable_type' => 'App\\Models\\Contrato',
            'auditable_id' => 1,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'A',
            'valor_novo' => 'B',
            'user_id' => $this->admin->id,
            'role_nome' => 'administrador_geral',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actAsAdmin()->get(route('tenant.auditoria.index', [
            'entidade' => 'contrato',
        ]));

        $response->assertStatus(200);
        // Quando entidade filtrada, login e acesso_doc nao aparecem
        $this->assertEquals(0, $response->viewData('totalLogins'));
        $this->assertEquals(0, $response->viewData('totalAcessosDocs'));
    }

    public function test_index_filtra_por_periodo(): void
    {
        // Registro dentro do periodo
        HistoricoAlteracao::create([
            'auditable_type' => 'App\\Models\\Contrato',
            'auditable_id' => 1,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'A',
            'valor_novo' => 'B',
            'user_id' => $this->admin->id,
            'role_nome' => 'administrador_geral',
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(5),
        ]);

        // Registro fora do periodo
        HistoricoAlteracao::create([
            'auditable_type' => 'App\\Models\\Contrato',
            'auditable_id' => 2,
            'campo_alterado' => 'valor_global',
            'valor_anterior' => '100',
            'valor_novo' => '200',
            'user_id' => $this->admin->id,
            'role_nome' => 'administrador_geral',
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(90),
        ]);

        $response = $this->actAsAdmin()->get(route('tenant.auditoria.index', [
            'tipo_acao' => 'alteracao',
            'data_inicio' => now()->subDays(10)->format('Y-m-d'),
            'data_fim' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->viewData('totalAlteracoes'));
    }

    public function test_index_pagina_resultados(): void
    {
        // Criar registros suficientes para forcar paginacao (25 por pagina)
        for ($i = 0; $i < 30; $i++) {
            HistoricoAlteracao::create([
                'auditable_type' => 'App\\Models\\Contrato',
                'auditable_id' => $i + 1,
                'campo_alterado' => 'objeto',
                'valor_anterior' => "Valor $i",
                'valor_novo' => "Novo $i",
                'user_id' => $this->admin->id,
                'role_nome' => 'administrador_geral',
                'ip_address' => '127.0.0.1',
                'created_at' => now(),
            ]);
        }

        $response = $this->actAsAdmin()->get(route('tenant.auditoria.index', [
            'tipo_acao' => 'alteracao',
            'page' => 2,
        ]));

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(30, $response->viewData('totalGeral'));
    }

    // ─── SHOW ──────────────────────────────────────────────

    public function test_show_exibe_detalhe_alteracao(): void
    {
        $historico = HistoricoAlteracao::create([
            'auditable_type' => 'App\\Models\\Contrato',
            'auditable_id' => 1,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'Antigo',
            'valor_novo' => 'Novo',
            'user_id' => $this->admin->id,
            'role_nome' => 'administrador_geral',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actAsAdmin()->get(
            route('tenant.auditoria.show', ['tipo' => 'alteracao', 'id' => $historico->id])
        );

        $response->assertStatus(200);
        $response->assertViewIs('tenant.auditoria.show');
        $response->assertViewHas('tipo', 'alteracao');
        $response->assertViewHas('registro');
        $response->assertViewHas('contexto');
    }

    public function test_show_exibe_detalhe_login(): void
    {
        $log = LoginLog::create([
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'success' => true,
            'created_at' => now(),
        ]);

        $response = $this->actAsAdmin()->get(
            route('tenant.auditoria.show', ['tipo' => 'login', 'id' => $log->id])
        );

        $response->assertStatus(200);
        $response->assertViewIs('tenant.auditoria.show');
        $response->assertViewHas('tipo', 'login');
    }

    public function test_show_exibe_detalhe_acesso_documento(): void
    {
        $contrato = Contrato::factory()->create();
        $documento = Documento::factory()->create([
            'documentable_type' => 'App\\Models\\Contrato',
            'documentable_id' => $contrato->id,
        ]);

        $log = LogAcessoDocumento::create([
            'documento_id' => $documento->id,
            'user_id' => $this->admin->id,
            'acao' => AcaoLogDocumento::Download,
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actAsAdmin()->get(
            route('tenant.auditoria.show', ['tipo' => 'acesso_documento', 'id' => $log->id])
        );

        $response->assertStatus(200);
        $response->assertViewIs('tenant.auditoria.show');
        $response->assertViewHas('tipo', 'acesso_documento');
    }

    public function test_show_tipo_invalido_retorna_404(): void
    {
        $response = $this->actAsAdmin()->get(
            route('tenant.auditoria.show', ['tipo' => 'invalido', 'id' => 1])
        );

        $response->assertStatus(404);
    }

    public function test_show_id_inexistente_retorna_404(): void
    {
        $response = $this->actAsAdmin()->get(
            route('tenant.auditoria.show', ['tipo' => 'alteracao', 'id' => 99999])
        );

        $response->assertStatus(404);
    }

    public function test_show_requer_permissao(): void
    {
        $role = Role::factory()->create(['nome' => 'audit_show_sem_perm_' . uniqid()]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $historico = HistoricoAlteracao::create([
            'auditable_type' => 'App\\Models\\Contrato',
            'auditable_id' => 1,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'A',
            'valor_novo' => 'B',
            'user_id' => $this->admin->id,
            'role_nome' => 'administrador_geral',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(
            route('tenant.auditoria.show', ['tipo' => 'alteracao', 'id' => $historico->id])
        );

        $response->assertStatus(403);
    }

    // ─── EXPORTAR PDF ──────────────────────────────────────

    public function test_exportar_pdf_com_filtros_validos(): void
    {
        HistoricoAlteracao::create([
            'auditable_type' => 'App\\Models\\Contrato',
            'auditable_id' => 1,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'Antigo',
            'valor_novo' => 'Novo',
            'user_id' => $this->admin->id,
            'role_nome' => 'administrador_geral',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.auditoria.exportar.pdf'), [
            'data_inicio' => now()->subMonth()->format('Y-m-d'),
            'data_fim' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_exportar_pdf_valida_data_inicio_obrigatoria(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.auditoria.exportar.pdf'), [
            'data_fim' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('data_inicio');
    }

    public function test_exportar_pdf_valida_data_fim_obrigatoria(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.auditoria.exportar.pdf'), [
            'data_inicio' => now()->subMonth()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('data_fim');
    }

    public function test_exportar_pdf_valida_data_inicio_antes_da_data_fim(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.auditoria.exportar.pdf'), [
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->subMonth()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('data_inicio');
    }

    public function test_exportar_pdf_usuario_sem_permissao_retorna_403(): void
    {
        $role = Role::factory()->create(['nome' => 'audit_export_sem_perm_' . uniqid()]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->post(route('tenant.auditoria.exportar.pdf'), [
            'data_inicio' => now()->subMonth()->format('Y-m-d'),
            'data_fim' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
    }

    // ─── EXPORTAR CSV ──────────────────────────────────────

    public function test_exportar_csv_com_filtros_validos(): void
    {
        HistoricoAlteracao::create([
            'auditable_type' => 'App\\Models\\Contrato',
            'auditable_id' => 1,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'Antigo',
            'valor_novo' => 'Novo',
            'user_id' => $this->admin->id,
            'role_nome' => 'administrador_geral',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.auditoria.exportar.csv'), [
            'data_inicio' => now()->subMonth()->format('Y-m-d'),
            'data_fim' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_exportar_csv_valida_filtros(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.auditoria.exportar.csv'), [
            'data_fim' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('data_inicio');
    }

    public function test_exportar_csv_com_filtro_tipo_acao(): void
    {
        LoginLog::create([
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'success' => true,
            'created_at' => now(),
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.auditoria.exportar.csv'), [
            'data_inicio' => now()->subMonth()->format('Y-m-d'),
            'data_fim' => now()->format('Y-m-d'),
            'tipo_acao' => 'login',
        ]);

        $response->assertStatus(200);
    }
}
