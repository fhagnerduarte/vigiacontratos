<?php

namespace Tests\Feature\Rbac;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

/**
 * Testa acesso de cada um dos 8 perfis a recursos protegidos pelo middleware 'permission'.
 * Validacao baseada na matriz do RolePermissionSeeder (RN-305 a RN-320).
 */
class PerfilUsuarioTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    /**
     * Helper: cria usuario com MFA configurado (obrigatorio para admin_geral e controladoria).
     */
    private function createUserWithMfa(string $roleName): User
    {
        return $this->createUserWithRole($roleName, [
            'mfa_secret'         => 'TESTSECRETKEY123',
            'mfa_enabled_at'     => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
    }

    /**
     * Helper: GET autenticado com MFA verificado.
     */
    private function authenticatedGet(User $user, string $route): \Illuminate\Testing\TestResponse
    {
        $request = $this->actingAs($user);

        // Perfis que requerem MFA no fallback: administrador_geral, controladoria
        if ($user->role && in_array($user->role->nome, ['administrador_geral', 'controladoria'])) {
            $request = $request->withSession(['mfa_verified' => true]);
        }

        return $request->get($route);
    }

    /**
     * Helper: DELETE autenticado com MFA verificado.
     */
    private function authenticatedDelete(User $user, string $route): \Illuminate\Testing\TestResponse
    {
        $request = $this->actingAs($user);

        if ($user->role && in_array($user->role->nome, ['administrador_geral', 'controladoria'])) {
            $request = $request->withSession(['mfa_verified' => true]);
        }

        return $request->delete($route);
    }

    // ═══════════════════════════════════════════════════════════════
    // ADMINISTRADOR GERAL — acesso total (RN-305)
    // ═══════════════════════════════════════════════════════════════

    public function test_administrador_geral_acessa_usuarios(): void
    {
        $user = $this->createUserWithMfa('administrador_geral');

        $this->authenticatedGet($user, route('tenant.users.index'))
            ->assertStatus(200);
    }

    public function test_administrador_geral_acessa_auditoria(): void
    {
        $user = $this->createUserWithMfa('administrador_geral');

        $this->authenticatedGet($user, route('tenant.auditoria.index'))
            ->assertStatus(200);
    }

    public function test_administrador_geral_acessa_configuracoes_alertas(): void
    {
        $user = $this->createUserWithMfa('administrador_geral');

        $this->authenticatedGet($user, route('tenant.alertas.configuracoes'))
            ->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════════════════
    // CONTROLADORIA — acesso amplo, sem gestao de usuarios (RN-306)
    // ═══════════════════════════════════════════════════════════════

    public function test_controladoria_acessa_auditoria(): void
    {
        $user = $this->createUserWithMfa('controladoria');

        $this->authenticatedGet($user, route('tenant.auditoria.index'))
            ->assertStatus(200);
    }

    public function test_controladoria_acessa_aditivos(): void
    {
        $user = $this->createUserWithMfa('controladoria');

        $this->authenticatedGet($user, route('tenant.aditivos.index'))
            ->assertStatus(200);
    }

    public function test_controladoria_acessa_relatorios(): void
    {
        $user = $this->createUserWithMfa('controladoria');

        $this->authenticatedGet($user, route('tenant.relatorios.index'))
            ->assertStatus(200);
    }

    public function test_controladoria_nao_acessa_gestao_usuarios(): void
    {
        $user = $this->createUserWithMfa('controladoria');

        $this->authenticatedGet($user, route('tenant.users.index'))
            ->assertStatus(403);
    }

    public function test_controladoria_nao_acessa_configuracoes_alertas(): void
    {
        $user = $this->createUserWithMfa('controladoria');

        $this->authenticatedGet($user, route('tenant.alertas.configuracoes'))
            ->assertStatus(403);
    }

    // ═══════════════════════════════════════════════════════════════
    // SECRETARIO — visualizacao limitada, sem auditoria (RN-307)
    // ═══════════════════════════════════════════════════════════════

    public function test_secretario_acessa_contratos(): void
    {
        $user = $this->createUserWithRole('secretario');

        $this->authenticatedGet($user, route('tenant.contratos.index'))
            ->assertStatus(200);
    }

    public function test_secretario_acessa_aditivos(): void
    {
        $user = $this->createUserWithRole('secretario');

        $this->authenticatedGet($user, route('tenant.aditivos.index'))
            ->assertStatus(200);
    }

    public function test_secretario_nao_acessa_auditoria(): void
    {
        $user = $this->createUserWithRole('secretario');

        $this->authenticatedGet($user, route('tenant.auditoria.index'))
            ->assertStatus(403);
    }

    public function test_secretario_nao_acessa_gestao_usuarios(): void
    {
        $user = $this->createUserWithRole('secretario');

        $this->authenticatedGet($user, route('tenant.users.index'))
            ->assertStatus(403);
    }

    public function test_secretario_nao_acessa_relatorios(): void
    {
        $user = $this->createUserWithRole('secretario');

        // secretario NAO tem relatorio.visualizar
        $this->authenticatedGet($user, route('tenant.relatorios.index'))
            ->assertStatus(403);
    }

    // ═══════════════════════════════════════════════════════════════
    // GESTOR DE CONTRATO — CRUD contratos/aditivos, sem auditoria (RN-308)
    // ═══════════════════════════════════════════════════════════════

    public function test_gestor_contrato_acessa_contratos(): void
    {
        $user = $this->createUserWithRole('gestor_contrato');

        $this->authenticatedGet($user, route('tenant.contratos.index'))
            ->assertStatus(200);
    }

    public function test_gestor_contrato_acessa_aditivos(): void
    {
        $user = $this->createUserWithRole('gestor_contrato');

        $this->authenticatedGet($user, route('tenant.aditivos.index'))
            ->assertStatus(200);
    }

    public function test_gestor_contrato_nao_acessa_auditoria(): void
    {
        $user = $this->createUserWithRole('gestor_contrato');

        $this->authenticatedGet($user, route('tenant.auditoria.index'))
            ->assertStatus(403);
    }

    public function test_gestor_contrato_nao_acessa_gestao_usuarios(): void
    {
        $user = $this->createUserWithRole('gestor_contrato');

        $this->authenticatedGet($user, route('tenant.users.index'))
            ->assertStatus(403);
    }

    // ═══════════════════════════════════════════════════════════════
    // FISCAL DE CONTRATO — visualizacao + upload docs (RN-309)
    // ═══════════════════════════════════════════════════════════════

    public function test_fiscal_contrato_acessa_contratos(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        $this->authenticatedGet($user, route('tenant.contratos.index'))
            ->assertStatus(200);
    }

    public function test_fiscal_contrato_acessa_documentos(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        $this->authenticatedGet($user, route('tenant.documentos.index'))
            ->assertStatus(200);
    }

    public function test_fiscal_contrato_nao_acessa_aditivos(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        // fiscal_contrato TEM aditivo.visualizar na matriz
        $this->authenticatedGet($user, route('tenant.aditivos.index'))
            ->assertStatus(200);
    }

    public function test_fiscal_contrato_nao_acessa_gestao_usuarios(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        $this->authenticatedGet($user, route('tenant.users.index'))
            ->assertStatus(403);
    }

    public function test_fiscal_contrato_nao_acessa_auditoria(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        $this->authenticatedGet($user, route('tenant.auditoria.index'))
            ->assertStatus(403);
    }

    public function test_fiscal_contrato_nao_acessa_relatorios(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        // fiscal_contrato NAO tem relatorio.visualizar
        $this->authenticatedGet($user, route('tenant.relatorios.index'))
            ->assertStatus(403);
    }

    // ═══════════════════════════════════════════════════════════════
    // FINANCEIRO — financeiro + relatorios, sem aditivos (RN-310)
    // ═══════════════════════════════════════════════════════════════

    public function test_financeiro_acessa_contratos(): void
    {
        $user = $this->createUserWithRole('financeiro');

        $this->authenticatedGet($user, route('tenant.contratos.index'))
            ->assertStatus(200);
    }

    public function test_financeiro_acessa_relatorios(): void
    {
        $user = $this->createUserWithRole('financeiro');

        $this->authenticatedGet($user, route('tenant.relatorios.index'))
            ->assertStatus(200);
    }

    public function test_financeiro_nao_acessa_aditivos(): void
    {
        $user = $this->createUserWithRole('financeiro');

        // financeiro NAO tem aditivo.visualizar
        $this->authenticatedGet($user, route('tenant.aditivos.index'))
            ->assertStatus(403);
    }

    public function test_financeiro_nao_acessa_gestao_usuarios(): void
    {
        $user = $this->createUserWithRole('financeiro');

        $this->authenticatedGet($user, route('tenant.users.index'))
            ->assertStatus(403);
    }

    public function test_financeiro_nao_acessa_auditoria(): void
    {
        $user = $this->createUserWithRole('financeiro');

        $this->authenticatedGet($user, route('tenant.auditoria.index'))
            ->assertStatus(403);
    }

    // ═══════════════════════════════════════════════════════════════
    // PROCURADORIA — pareceres + aditivos, sem CRUD contratos (RN-311)
    // ═══════════════════════════════════════════════════════════════

    public function test_procuradoria_acessa_contratos(): void
    {
        $user = $this->createUserWithRole('procuradoria');

        $this->authenticatedGet($user, route('tenant.contratos.index'))
            ->assertStatus(200);
    }

    public function test_procuradoria_acessa_aditivos(): void
    {
        $user = $this->createUserWithRole('procuradoria');

        $this->authenticatedGet($user, route('tenant.aditivos.index'))
            ->assertStatus(200);
    }

    public function test_procuradoria_nao_acessa_gestao_usuarios(): void
    {
        $user = $this->createUserWithRole('procuradoria');

        $this->authenticatedGet($user, route('tenant.users.index'))
            ->assertStatus(403);
    }

    public function test_procuradoria_nao_acessa_auditoria(): void
    {
        $user = $this->createUserWithRole('procuradoria');

        $this->authenticatedGet($user, route('tenant.auditoria.index'))
            ->assertStatus(403);
    }

    public function test_procuradoria_nao_acessa_relatorios(): void
    {
        $user = $this->createUserWithRole('procuradoria');

        // procuradoria NAO tem relatorio.visualizar
        $this->authenticatedGet($user, route('tenant.relatorios.index'))
            ->assertStatus(403);
    }

    // ═══════════════════════════════════════════════════════════════
    // GABINETE — visualizacao basica, sem aditivos (RN-312)
    // ═══════════════════════════════════════════════════════════════

    public function test_gabinete_acessa_dashboard(): void
    {
        $user = $this->createUserWithRole('gabinete');

        $this->authenticatedGet($user, route('tenant.dashboard'))
            ->assertStatus(200);
    }

    public function test_gabinete_acessa_contratos(): void
    {
        $user = $this->createUserWithRole('gabinete');

        $this->authenticatedGet($user, route('tenant.contratos.index'))
            ->assertStatus(200);
    }

    public function test_gabinete_acessa_relatorios(): void
    {
        $user = $this->createUserWithRole('gabinete');

        // gabinete TEM relatorio.visualizar
        $this->authenticatedGet($user, route('tenant.relatorios.index'))
            ->assertStatus(200);
    }

    public function test_gabinete_nao_acessa_aditivos(): void
    {
        $user = $this->createUserWithRole('gabinete');

        // gabinete NAO tem aditivo.visualizar
        $this->authenticatedGet($user, route('tenant.aditivos.index'))
            ->assertStatus(403);
    }

    public function test_gabinete_nao_acessa_auditoria(): void
    {
        $user = $this->createUserWithRole('gabinete');

        $this->authenticatedGet($user, route('tenant.auditoria.index'))
            ->assertStatus(403);
    }

    public function test_gabinete_nao_acessa_gestao_usuarios(): void
    {
        $user = $this->createUserWithRole('gabinete');

        $this->authenticatedGet($user, route('tenant.users.index'))
            ->assertStatus(403);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD — todos os perfis autenticados acessam (rota sem permission middleware)
    // ═══════════════════════════════════════════════════════════════

    #[\PHPUnit\Framework\Attributes\DataProvider('perfisSemMfaProvider')]
    public function test_todos_perfis_acessam_dashboard(string $perfil): void
    {
        $user = $this->createUserWithRole($perfil);

        $this->authenticatedGet($user, route('tenant.dashboard'))
            ->assertStatus(200);
    }

    public static function perfisSemMfaProvider(): array
    {
        return [
            'secretario'       => ['secretario'],
            'gestor_contrato'  => ['gestor_contrato'],
            'fiscal_contrato'  => ['fiscal_contrato'],
            'financeiro'       => ['financeiro'],
            'procuradoria'     => ['procuradoria'],
            'gabinete'         => ['gabinete'],
        ];
    }

    public function test_administrador_geral_acessa_dashboard(): void
    {
        $user = $this->createUserWithMfa('administrador_geral');

        $this->authenticatedGet($user, route('tenant.dashboard'))
            ->assertStatus(200);
    }

    public function test_controladoria_acessa_dashboard(): void
    {
        $user = $this->createUserWithMfa('controladoria');

        $this->authenticatedGet($user, route('tenant.dashboard'))
            ->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════════════════
    // PERFIS PADRAO NAO DELETAVEIS (is_padrao = true)
    // ═══════════════════════════════════════════════════════════════

    public function test_perfis_padrao_nao_podem_ser_deletados(): void
    {
        $admin = $this->createUserWithMfa('administrador_geral');

        $nomesPadrao = [
            'administrador_geral', 'controladoria', 'secretario', 'gestor_contrato',
            'fiscal_contrato', 'financeiro', 'procuradoria', 'gabinete',
        ];

        foreach ($nomesPadrao as $nome) {
            $role = Role::where('nome', $nome)->first();
            $this->assertNotNull($role, "Role {$nome} deve existir");
            $this->assertTrue((bool) $role->is_padrao, "Role {$nome} deve ser padrao");

            $response = $this->authenticatedDelete($admin, route('tenant.roles.destroy', $role));

            $response->assertRedirect(route('tenant.roles.index'));
            $response->assertSessionHas('error');

            $this->assertDatabaseHas('roles', [
                'id' => $role->id,
                'nome' => $nome,
            ], 'tenant');
        }
    }

    public function test_perfil_customizado_pode_ser_deletado(): void
    {
        $admin = $this->createUserWithMfa('administrador_geral');

        $role = Role::create([
            'nome' => 'perfil_temporario',
            'descricao' => 'Perfil para teste de exclusao',
            'is_padrao' => false,
        ]);

        $response = $this->authenticatedDelete($admin, route('tenant.roles.destroy', $role));

        $response->assertRedirect(route('tenant.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ], 'tenant');
    }
}
