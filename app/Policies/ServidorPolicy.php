<?php

namespace App\Policies;

use App\Models\Servidor;
use App\Models\User;

class ServidorPolicy
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
     * Listar servidores — permissao basica.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('servidor.visualizar');
    }

    /**
     * Visualizar servidor — permissao + secretaria.
     */
    public function view(User $user, Servidor $servidor): bool
    {
        if (!$user->hasPermission('servidor.visualizar')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $servidor);
    }

    /**
     * Criar servidor — permissao basica.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('servidor.criar');
    }

    /**
     * Editar servidor — permissao + secretaria.
     */
    public function update(User $user, Servidor $servidor): bool
    {
        if (!$user->hasPermission('servidor.editar')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $servidor);
    }

    /**
     * Excluir servidor — permissao + secretaria.
     */
    public function delete(User $user, Servidor $servidor): bool
    {
        if (!$user->hasPermission('servidor.excluir')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $servidor);
    }

    /**
     * Verifica se o servidor pertence a secretaria vinculada ao usuario.
     * Perfis estrategicos (RN-327) acessam qualquer secretaria.
     */
    private function pertenceASecretariaDoUsuario(User $user, Servidor $servidor): bool
    {
        if ($user->isPerfilEstrategico()) {
            return true;
        }

        $secretariaIds = $user->secretarias()->pluck('secretarias.id');

        return $secretariaIds->contains($servidor->secretaria_id);
    }
}
