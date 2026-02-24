<?php

namespace App\Policies;

use App\Models\Aditivo;
use App\Models\Contrato;
use App\Models\User;

class AditivoPolicy
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
     * Listar aditivos — permissao basica.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('aditivo.visualizar');
    }

    /**
     * Visualizar aditivo especifico — permissao + secretaria via contrato.
     */
    public function view(User $user, Aditivo $aditivo): bool
    {
        if (!$user->hasPermission('aditivo.visualizar')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $aditivo);
    }

    /**
     * Criar aditivo — permissao basica.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('aditivo.criar');
    }

    /**
     * Aprovar/reprovar/cancelar aditivo — permissao + secretaria via contrato.
     */
    public function aprovar(User $user, Aditivo $aditivo): bool
    {
        if (!$user->hasPermission('aditivo.aprovar')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $aditivo);
    }

    /**
     * Verifica se o aditivo pertence a contrato de secretaria vinculada ao usuario.
     * Perfis estrategicos (RN-327) acessam qualquer secretaria.
     * Usa withoutGlobalScopes para evitar loop com SecretariaScope.
     */
    private function pertenceASecretariaDoUsuario(User $user, Aditivo $aditivo): bool
    {
        if ($user->isPerfilEstrategico()) {
            return true;
        }

        $contrato = Contrato::withoutGlobalScopes()
            ->find($aditivo->contrato_id);

        if (!$contrato) {
            return false;
        }

        $secretariaIds = $user->secretarias()->pluck('secretarias.id');

        return $secretariaIds->contains($contrato->secretaria_id);
    }
}
