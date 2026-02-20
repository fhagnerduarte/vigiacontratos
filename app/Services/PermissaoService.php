<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PermissaoService
{
    /**
     * Verifica se o usuario possui uma permissao especifica.
     */
    public static function verificar(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    /**
     * Atribui uma permissao individual ao usuario (com ou sem expiracao).
     */
    public static function atribuirPermissaoIndividual(
        User $user,
        Permission $permission,
        ?Carbon $expiresAt,
        User $concedidoPor
    ): void {
        $user->permissions()->syncWithoutDetaching([
            $permission->id => [
                'expires_at' => $expiresAt,
                'concedido_por' => $concedidoPor->id,
                'created_at' => now(),
            ],
        ]);
    }

    /**
     * Revoga uma permissao individual do usuario.
     */
    public static function revogarPermissaoIndividual(User $user, Permission $permission): void
    {
        $user->permissions()->detach($permission->id);
    }

    /**
     * Retorna todas as permissoes do usuario (via role + individuais validas).
     */
    public static function permissoesDoUsuario(User $user): Collection
    {
        // Permissoes via role
        $rolePermissions = $user->role
            ? $user->role->permissions()->pluck('nome')
            : collect();

        // Permissoes individuais (validas — nao expiradas)
        $userPermissions = $user->permissions()
            ->where(function ($query) {
                $query->whereNull('user_permissions.expires_at')
                      ->orWhere('user_permissions.expires_at', '>', now());
            })
            ->pluck('nome');

        return $rolePermissions->merge($userPermissions)->unique()->sort()->values();
    }

    /**
     * Sincroniza as permissoes de um role.
     */
    public static function sincronizarPermissoesRole(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }
}
