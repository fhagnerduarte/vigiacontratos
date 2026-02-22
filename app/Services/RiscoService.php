<?php

namespace App\Services;

use App\Enums\NivelRisco;
use App\Models\Contrato;

class RiscoService
{
    /**
     * Calcula o score de risco automaticamente (RN-029).
     * Criterios conforme Fluxo 2 do banco de conhecimento.
     *
     * @return array{score: int, nivel: NivelRisco}
     */
    public static function calcular(Contrato $contrato): array
    {
        $score = 0;

        // Sem fiscal designado: +20
        if (! $contrato->fiscalAtual) {
            $score += 20;
        }

        // Sem documento anexado: +20
        if ($contrato->documentos()->count() === 0) {
            $score += 20;
        }

        // Valor > R$ 1.000.000: +10
        if ($contrato->valor_global > 1000000) {
            $score += 10;
        }

        // Modalidade sensivel (dispensa/inexigibilidade): +10
        if ($contrato->modalidade_contratacao?->isSensivel()) {
            $score += 10;
        }

        // Sem fundamento legal quando dispensa/inexigibilidade: +10
        if ($contrato->modalidade_contratacao?->isSensivel() && empty($contrato->fundamento_legal)) {
            $score += 10;
        }

        // Sem processo administrativo: +10
        if (empty($contrato->numero_processo)) {
            $score += 10;
        }

        // Vigencia > 24 meses: +5
        if ($contrato->prazo_meses > 24) {
            $score += 5;
        }

        // --- Criterios de Aditivos (RN-106, RN-107, RN-108) ---

        // Percentual acumulado de acrescimos > 20%: +10 (RN-106)
        if ($contrato->relationLoaded('aditivos') || $contrato->aditivos()->exists()) {
            $valorOriginal = AditivoService::obterValorOriginal($contrato);
            if ($valorOriginal > 0) {
                $somaAcrescimos = (float) $contrato->aditivosVigentes()->sum('valor_acrescimo');
                $percentualAcumulado = ($somaAcrescimos / $valorOriginal) * 100;
                if ($percentualAcumulado > 20) {
                    $score += 10;
                }
            }

            // 3+ aditivos em ultimos 12 meses: +10 (RN-107)
            $aditivosRecentes = $contrato->aditivos()
                ->where('data_assinatura', '>=', now()->subYear())
                ->count();
            if ($aditivosRecentes >= 3) {
                $score += 10;
            }

            // Aditivo registrado ≤30 dias antes do vencimento: +5 (RN-108)
            $ultimoAditivo = $contrato->aditivos()
                ->orderByDesc('data_assinatura')
                ->first();
            if ($ultimoAditivo && $contrato->data_fim) {
                $diasAntes = $ultimoAditivo->data_assinatura->diffInDays($contrato->data_fim, false);
                if ($diasAntes >= 0 && $diasAntes <= 30) {
                    $score += 5;
                }
            }
        }

        // Classificacao: 0-29=baixo, 30-59=medio, 60+=alto
        $nivel = match (true) {
            $score >= 60 => NivelRisco::Alto,
            $score >= 30 => NivelRisco::Medio,
            default => NivelRisco::Baixo,
        };

        return ['score' => $score, 'nivel' => $nivel];
    }
}
