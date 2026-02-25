<?php

namespace App\Services;

use App\Enums\TipoOcorrencia;
use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Ocorrencia;
use App\Models\User;

class OcorrenciaService
{
    /**
     * Registra uma nova ocorrencia no contrato.
     *
     * @return array{ocorrencia: Ocorrencia, vencidas_count: int}
     */
    public static function registrar(Contrato $contrato, array $dados, User $user): array
    {
        $fiscal = $contrato->fiscalAtual;

        $ocorrencia = Ocorrencia::create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $dados['fiscal_id'] ?? $fiscal?->id,
            'data_ocorrencia' => $dados['data_ocorrencia'],
            'tipo_ocorrencia' => $dados['tipo_ocorrencia'],
            'descricao' => $dados['descricao'],
            'providencia' => $dados['providencia'] ?? null,
            'prazo_providencia' => $dados['prazo_providencia'] ?? null,
            'observacoes' => $dados['observacoes'] ?? null,
            'registrado_por' => $user->id,
        ]);

        AuditoriaService::registrar(
            $contrato,
            'ocorrencia_registrada',
            null,
            "Ocorrencia #{$ocorrencia->id} ({$ocorrencia->tipo_ocorrencia->label()}) registrada",
            $user
        );

        $vencidasCount = $contrato->ocorrencias()->vencidas()->count();

        return [
            'ocorrencia' => $ocorrencia,
            'vencidas_count' => $vencidasCount,
        ];
    }

    /**
     * Resolve uma ocorrencia pendente.
     */
    public static function resolver(Ocorrencia $ocorrencia, User $user, ?string $observacoes = null): Ocorrencia
    {
        $ocorrencia->update([
            'resolvida' => true,
            'resolvida_em' => now(),
            'resolvida_por' => $user->id,
            'observacoes' => $observacoes ?? $ocorrencia->observacoes,
        ]);

        AuditoriaService::registrar(
            $ocorrencia->contrato,
            'ocorrencia_resolvida',
            null,
            "Ocorrencia #{$ocorrencia->id} ({$ocorrencia->tipo_ocorrencia->label()}) resolvida",
            $user
        );

        return $ocorrencia->fresh();
    }

    /**
     * Resumo de ocorrencias do contrato.
     *
     * @return array{total: int, pendentes: int, resolvidas: int, vencidas: int, por_tipo: array}
     */
    public static function resumo(Contrato $contrato): array
    {
        $ocorrencias = $contrato->ocorrencias;

        $porTipo = [];
        foreach (TipoOcorrencia::cases() as $tipo) {
            $count = $ocorrencias->where('tipo_ocorrencia', $tipo)->count();
            if ($count > 0) {
                $porTipo[$tipo->value] = [
                    'label' => $tipo->label(),
                    'count' => $count,
                    'cor' => $tipo->cor(),
                ];
            }
        }

        return [
            'total' => $ocorrencias->count(),
            'pendentes' => $ocorrencias->where('resolvida', false)->count(),
            'resolvidas' => $ocorrencias->where('resolvida', true)->count(),
            'vencidas' => $ocorrencias->where('resolvida', false)
                ->filter(fn ($o) => $o->prazo_providencia && $o->prazo_providencia->lt(now()))
                ->count(),
            'por_tipo' => $porTipo,
        ];
    }
}
