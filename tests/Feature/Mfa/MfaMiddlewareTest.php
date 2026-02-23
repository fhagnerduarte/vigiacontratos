<?php

namespace Tests\Feature\Mfa;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;

class MfaMiddlewareTest extends TestCase
{
    use RunsTenantMigrations;

    protected function getRole(string $nome, string $descricao = ''): Role
    {
        return Role::on('tenant')->firstOrCreate(
            ['nome' => $nome],
            ['descricao' => $descricao ?: $nome, 'is_padrao' => true, 'is_ativo' => true]
        );
    }

    protected function setUpTenantComMfa(string $modo = 'opcional', ?array $perfisObrigatorios = null): void
    {
        $this->setUpTenant();
        $this->tenant->update([
            'mfa_habilitado' => true,
            'mfa_modo' => $modo,
            'mfa_perfis_obrigatorios' => $perfisObrigatorios,
        ]);
    }

    public function test_usuario_sem_mfa_e_perfil_nao_obrigatorio_acessa_dashboard(): void
    {
        $this->setUpTenant();
        $role = $this->getRole('fiscal_contrato', 'Fiscal de Contrato');
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('tenant.dashboard'));

        $response->assertStatus(200);
    }

    public function test_usuario_com_mfa_obrigatorio_sem_setup_redireciona_para_setup(): void
    {
        $this->setUpTenantComMfa('opcional', ['administrador_geral']);
        $role = $this->getRole('administrador_geral', 'Administrador Geral');
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('tenant.dashboard'));

        $response->assertRedirect(route('tenant.mfa.setup'));
    }

    public function test_usuario_com_mfa_habilitado_sem_verificacao_redireciona_para_verify(): void
    {
        $this->setUpTenantComMfa('opcional', ['administrador_geral']);
        $role = $this->getRole('administrador_geral', 'Administrador Geral');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $response = $this->actingAs($user)->get(route('tenant.dashboard'));

        $response->assertRedirect(route('tenant.mfa.verify'));
    }

    public function test_usuario_com_mfa_habilitado_e_verificado_acessa_dashboard(): void
    {
        $this->setUpTenantComMfa('opcional', ['administrador_geral']);
        $role = $this->getRole('administrador_geral', 'Administrador Geral');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.dashboard'));

        $response->assertStatus(200);
    }

    public function test_usuario_com_mfa_opcional_sem_setup_acessa_dashboard(): void
    {
        $this->setUpTenantComMfa('opcional');
        $role = $this->getRole('secretario', 'Secretario');
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('tenant.dashboard'));

        $response->assertStatus(200);
    }

    public function test_rotas_mfa_acessiveis_sem_mfa_verified(): void
    {
        $this->setUpTenantComMfa('opcional', ['administrador_geral']);
        $role = $this->getRole('administrador_geral', 'Administrador Geral');
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('tenant.mfa.setup'));

        $response->assertStatus(200);
    }

    public function test_tenant_sem_mfa_habilitado_permite_acesso_direto(): void
    {
        $this->setUpTenant(); // MFA desabilitado por padrão
        $role = $this->getRole('administrador_geral', 'Administrador Geral');
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('tenant.dashboard'));

        $response->assertStatus(200);
    }

    public function test_tenant_mfa_obrigatorio_global_exige_de_todos(): void
    {
        $this->setUpTenantComMfa('obrigatorio');
        $role = $this->getRole('fiscal_contrato', 'Fiscal de Contrato');
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('tenant.dashboard'));

        $response->assertRedirect(route('tenant.mfa.setup'));
    }

    public function test_tenant_mfa_desativado_bloqueia_setup(): void
    {
        $this->setUpTenant(); // MFA desabilitado
        $role = $this->getRole('administrador_geral', 'Administrador Geral');
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('tenant.mfa.setup'));

        $response->assertRedirect(route('tenant.dashboard'));
    }
}
