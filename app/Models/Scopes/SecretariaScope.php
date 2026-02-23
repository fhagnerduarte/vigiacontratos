<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Eloquent Global Scope — filtra registros por secretaria do usuario logado (RN-326).
 *
 * Bypass automatico:
 * - Sem autenticacao (jobs, CLI, seeders) → sem filtro
 * - Perfis estrategicos: administrador_geral, controladoria, gabinete (RN-327) → sem filtro
 *
 * Demais perfis: WHERE secretaria_id IN (secretarias vinculadas ao usuario)
 */
class SecretariaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Bypass: sem autenticacao (jobs agendados, CLI, seeders, testes sem actingAs)
        if (! auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Bypass: perfis estrategicos veem todos os registros (RN-327)
        if ($user->isPerfilEstrategico()) {
            return;
        }

        // Filtrar por secretarias vinculadas ao usuario (RN-326)
        $secretariaIds = $user->secretarias()->pluck('secretarias.id');

        $builder->whereIn($model->getTable() . '.secretaria_id', $secretariaIds);
    }
}
