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
     * Registra auditoria (RN-332).
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

        // Auditoria (RN-332)
        $detalhe = $permission->nome;
        if ($expiresAt) {
            $detalhe .= " (expira: {$expiresAt->toDateTimeString()})";
        } else {
            $detalhe .= ' (permanente)';
        }

        AuditoriaService::registrar(
            $user,
            'permissao_concedida',
            null,
            $detalhe,
            $concedidoPor,
            request()?->ip() ?? '127.0.0.1'
        );
    }

    /**
     * Revoga uma permissao individual do usuario.
     * Registra auditoria (RN-332).
     */
    public static function revogarPermissaoIndividual(
        User $user,
        Permission $permission,
        ?User $revogadoPor = null
    ): void {
        $executor = $revogadoPor ?? (auth()->check() ? auth()->user() : $user);

        // Auditoria antes do detach (RN-332)
        AuditoriaService::registrar(
            $user,
            'permissao_revogada',
            $permission->nome,
            null,
            $executor,
            request()?->ip() ?? '127.0.0.1'
        );

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
