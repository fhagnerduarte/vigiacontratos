<?php

namespace Tests\Traits;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;

trait SeedsTenantData
{
    /**
     * Roda os seeders base necessarios para a maioria dos testes tenant:
     * Roles (8 perfis padrao), Permissions (43), RolePermissions (matriz).
     */
    protected function seedBaseData(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * Cria um usuario autenticado com perfil Administrador Geral
     * (acesso total a todas as permissoes).
     */
    protected function createAdminUser(array $overrides = []): User
    {
        $role = Role::where('nome', 'administrador_geral')->first()
            ?? Role::factory()->create(['nome' => 'administrador_geral', 'is_padrao' => true]);

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'is_ativo' => true,
        ], $overrides));
    }

    /**
     * Cria um usuario com role e permissoes especificas.
     */
    protected function createUserWithRole(string $roleName, array $overrides = []): User
    {
        $role = Role::where('nome', $roleName)->first()
            ?? Role::factory()->create(['nome' => $roleName]);

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'is_ativo' => true,
        ], $overrides));
    }

    /**
     * Cria um usuario e autentica via actingAs.
     */
    protected function actingAsAdmin(array $overrides = []): User
    {
        $user = $this->createAdminUser($overrides);
        $this->actingAs($user);

        return $user;
    }
}
