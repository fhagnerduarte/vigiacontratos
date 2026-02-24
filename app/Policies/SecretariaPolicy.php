<?php

namespace App\Policies;

use App\Models\Secretaria;
use App\Models\User;

class SecretariaPolicy
{
    /**
     * Administrador geral tem acesso total (RN-305).
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('administrador_geral')) {
            return true;
        }

        return null;
    }

    /**
     * Listar secretarias — permissao basica.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('secretaria.visualizar');
    }

    /**
     * Visualizar secretaria — permissao basica.
     */
    public function view(User $user, Secretaria $secretaria): bool
    {
        return $user->hasPermission('secretaria.visualizar');
    }

    /**
     * Criar secretaria — permissao basica.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('secretaria.criar');
    }

    /**
     * Editar secretaria — permissao basica.
     */
    public function update(User $user, Secretaria $secretaria): bool
    {
        return $user->hasPermission('secretaria.editar');
    }

    /**
     * Excluir secretaria — permissao basica.
     */
    public function delete(User $user, Secretaria $secretaria): bool
    {
        return $user->hasPermission('secretaria.excluir');
    }
}
