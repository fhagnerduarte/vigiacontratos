<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class UserTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_has_permission_admin_geral_always_true(): void
    {
        $role = Role::where('nome', 'administrador_geral')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasPermission('contrato.criar'));
        $this->assertTrue($user->hasPermission('qualquer.permissao.inexistente'));
    }

    public function test_has_permission_via_role(): void
    {
        $role = Role::where('nome', 'gestor_contrato')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        // gestor_contrato deve ter contrato.criar via RolePermissionSeeder
        $this->assertTrue($user->hasPermission('contrato.criar'));
    }

    public function test_has_permission_sem_permissao_retorna_false(): void
    {
        // Cria um role customizado sem nenhuma permissao atribuida
        $roleSemPermissao = Role::factory()->create(['nome' => 'role_sem_permissao']);
        $user = User::factory()->create(['role_id' => $roleSemPermissao->id]);

        $this->assertFalse($user->hasPermission('contrato.criar'));
    }

    public function test_has_role(): void
    {
        $role = Role::where('nome', 'gestor_contrato')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasRole('gestor_contrato'));
        $this->assertFalse($user->hasRole('administrador_geral'));
    }

    public function test_is_perfil_estrategico_true(): void
    {
        $role = Role::where('nome', 'administrador_geral')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->isPerfilEstrategico());
    }

    public function test_is_perfil_estrategico_false(): void
    {
        $role = Role::where('nome', 'gestor_contrato')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isPerfilEstrategico());
    }
}
