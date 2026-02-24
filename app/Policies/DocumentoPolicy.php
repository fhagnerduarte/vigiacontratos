<?php

namespace App\Policies;

use App\Models\Aditivo;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\User;

class DocumentoPolicy
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
     * Listar documentos — permissao basica (RN-130).
     * SecretariaScope ja filtra contratos na query.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('documento.visualizar');
    }

    /**
     * Visualizar documento especifico — permissao + secretaria (RN-130).
     */
    public function view(User $user, Documento $documento): bool
    {
        if (!$user->hasPermission('documento.visualizar')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $documento);
    }

    /**
     * Upload de documento (RN-039, RN-040).
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('documento.criar');
    }

    /**
     * Download de documento — permissao + secretaria (RN-130).
     */
    public function download(User $user, Documento $documento): bool
    {
        if (!$user->hasPermission('documento.visualizar')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $documento);
    }

    /**
     * Exclusao logica de documento — permissao + secretaria (RN-134).
     */
    public function delete(User $user, Documento $documento): bool
    {
        if (!$user->hasPermission('documento.excluir')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $documento);
    }

    /**
     * Verificar integridade SHA-256 — permissao + secretaria (RN-221).
     */
    public function verificarIntegridade(User $user, Documento $documento): bool
    {
        if (!$user->hasPermission('auditoria.verificar_integridade')) {
            return false;
        }

        return $this->pertenceASecretariaDoUsuario($user, $documento);
    }

    /**
     * Verifica se o documento pertence a uma secretaria vinculada ao usuario.
     * Perfis estrategicos (RN-327) acessam qualquer secretaria.
     */
    private function pertenceASecretariaDoUsuario(User $user, Documento $documento): bool
    {
        if ($user->isPerfilEstrategico()) {
            return true;
        }

        $contrato = $this->resolverContrato($documento);

        if (!$contrato) {
            return false;
        }

        $secretariaIds = $user->secretarias()->pluck('secretarias.id');

        return $secretariaIds->contains($contrato->secretaria_id);
    }

    /**
     * Resolve o Contrato a partir do parent polimorfico do Documento.
     * Documento pode pertencer a Contrato ou Aditivo (via contrato_id).
     * Usa withoutGlobalScopes para evitar loop com SecretariaScope.
     */
    private function resolverContrato(Documento $documento): ?Contrato
    {
        if ($documento->documentable_type === Contrato::class) {
            return Contrato::withoutGlobalScopes()
                ->find($documento->documentable_id);
        }

        if ($documento->documentable_type === Aditivo::class) {
            $aditivo = Aditivo::find($documento->documentable_id);

            if ($aditivo) {
                return Contrato::withoutGlobalScopes()
                    ->find($aditivo->contrato_id);
            }
        }

        return null;
    }
}
