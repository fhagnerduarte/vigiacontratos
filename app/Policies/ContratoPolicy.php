<?php

namespace App\Policies;

use App\Enums\StatusContrato;
use App\Models\Contrato;
use App\Models\User;

class ContratoPolicy
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
     * Listar contratos — permissao basica.
     * SecretariaScope ja filtra contratos na query.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('contrato.visualizar');
    }

    /**
     * Visualizar contrato especifico — permissao + secretaria.
     */
    public function view(User $user, Contrato $contrato): bool
    {
        if (!$user->hasPermission('contrato.visualizar')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $contrato);
    }

    /**
     * Criar contrato — permissao basica.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('contrato.criar');
    }

    /**
     * Editar contrato — permissao + secretaria + bloqueio vencido (RN-006).
     */
    public function update(User $user, Contrato $contrato): bool
    {
        if (!$user->hasPermission('contrato.editar')) {
            return false;
        }

        if ($contrato->status === StatusContrato::Vencido) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $contrato);
    }

    /**
     * Excluir contrato — permissao + secretaria.
     */
    public function delete(User $user, Contrato $contrato): bool
    {
        if (!$user->hasPermission('contrato.excluir')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $contrato);
    }

    /**
     * Verifica se o contrato pertence a uma secretaria vinculada ao usuario.
     * Perfis estrategicos (RN-327) acessam qualquer secretaria.
     */
    private function pertenceASecretariaDoUsuario(User $user, Contrato $contrato): bool
    {
        if ($user->isPerfilEstrategico()) {
            return true;
        }

        $secretariaIds = $user->secretarias()->pluck('secretarias.id');

        return $secretariaIds->contains($contrato->secretaria_id);
    }
}
