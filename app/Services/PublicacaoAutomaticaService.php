<?php

namespace App\Services;

use App\Enums\ClassificacaoSigilo;
use App\Enums\StatusContrato;
use App\Models\Contrato;
use App\Models\HistoricoAlteracao;
use App\Models\User;

class PublicacaoAutomaticaService
{
    /**
     * Publica automaticamente contratos publicos que possuem data de publicacao
     * mas ainda nao foram marcados como publicados no portal (LAI art. 8).
     *
     * @return array{publicados: int, ja_publicados: int}
     */
    public static function publicar(): array
    {
        $resultado = ['publicados' => 0, 'ja_publicados' => 0];

        $contratos = Contrato::withoutGlobalScopes()
            ->where('status', StatusContrato::Vigente->value)
            ->where('classificacao_sigilo', ClassificacaoSigilo::Publico->value)
            ->whereNotNull('data_publicacao')
            ->where('publicado_portal', false)
            ->get();

        // Buscar admin para auditoria (user_id NOT NULL)
        $adminId = User::whereHas('role', fn ($q) => $q->where('nome', 'administrador_geral'))
            ->value('id') ?? User::first()?->id;

        foreach ($contratos as $contrato) {
            $contrato->update(['publicado_portal' => true]);

            if ($adminId) {
                HistoricoAlteracao::create([
                    'auditable_type' => Contrato::class,
                    'auditable_id' => $contrato->id,
                    'campo_alterado' => 'publicado_portal',
                    'valor_anterior' => 'false',
                    'valor_novo' => 'true',
                    'user_id' => $adminId,
                    'role_nome' => 'sistema',
                    'ip_address' => '127.0.0.1',
                ]);
            }

            $resultado['publicados']++;
        }

        // Contar os que ja estao publicados (para referencia)
        $resultado['ja_publicados'] = Contrato::withoutGlobalScopes()
            ->where('status', StatusContrato::Vigente->value)
            ->where('classificacao_sigilo', ClassificacaoSigilo::Publico->value)
            ->where('publicado_portal', true)
            ->count();

        return $resultado;
    }
}
