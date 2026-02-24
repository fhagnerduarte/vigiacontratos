<?php

namespace App\Policies;

use App\Models\Fornecedor;
use App\Models\User;

class FornecedorPolicy
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
     * Listar fornecedores — permissao basica.
     * Fornecedores sao globais (sem scoping por secretaria).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('fornecedor.visualizar');
    }

    /**
     * Visualizar fornecedor — permissao basica.
     */
    public function view(User $user, Fornecedor $fornecedor): bool
    {
        return $user->hasPermission('fornecedor.visualizar');
    }

    /**
     * Criar fornecedor — permissao basica.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('fornecedor.criar');
    }

    /**
     * Editar fornecedor — permissao basica.
     */
    public function update(User $user, Fornecedor $fornecedor): bool
    {
        return $user->hasPermission('fornecedor.editar');
    }

    /**
     * Excluir fornecedor — permissao basica.
     */
    public function delete(User $user, Fornecedor $fornecedor): bool
    {
        return $user->hasPermission('fornecedor.excluir');
    }
}
