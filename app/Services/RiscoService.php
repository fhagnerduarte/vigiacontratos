<?php

namespace App\Services;

use App\Enums\CategoriaContrato;
use App\Enums\NivelRisco;
use App\Enums\StatusCompletudeDocumental;
use App\Enums\TipoDocumentoContratual;
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
        $expandido = self::calcularExpandido($contrato);

        return [
            'score' => $expandido['score'],
            'nivel' => $expandido['nivel'],
        ];
    }

    /**
     * Calcula o score de risco expandido com 5 categorias (RN-136 a RN-142).
     * Categorias: vencimento, financeiro, documental, juridico, operacional.
     *
     * @return array{score: int, nivel: NivelRisco, categorias: array}
     */
    public static function calcularExpandido(Contrato $contrato): array
    {
        $categorias = [
            'vencimento' => self::calcularRiscoVencimento($contrato),
            'financeiro' => self::calcularRiscoFinanceiro($contrato),
            'documental' => self::calcularRiscoDocumental($contrato),
            'juridico' => self::calcularRiscoJuridico($contrato),
            'operacional' => self::calcularRiscoOperacional($contrato),
        ];

        $score = 0;
        foreach ($categorias as $cat) {
            $score += $cat['score'];
        }
        $score = min(100, $score);

        $nivel = match (true) {
            $score >= 60 => NivelRisco::Alto,
            $score >= 30 => NivelRisco::Medio,
            default => NivelRisco::Baixo,
        };

        return [
            'score' => $score,
            'nivel' => $nivel,
            'categorias' => $categorias,
        ];
    }

    /**
     * Categoria: Vencimento (RN-137).
     */
    private static function calcularRiscoVencimento(Contrato $contrato): array
    {
        $score = 0;
        $criterios = [];

        // Vencimento <30 dias: +15pts
        if ($contrato->data_fim) {
            $diasRestantes = (int) now()->startOfDay()->diffInDays($contrato->data_fim->startOfDay(), false);
            if ($diasRestantes >= 0 && $diasRestantes < 30) {
                $score += 15;
                $criterios[] = "Vencimento em {$diasRestantes} dias (+15pts)";
            }
        }

        // Vigencia > 24 meses: +5pts (criterio base)
        if ($contrato->prazo_meses > 24) {
            $score += 5;
            $criterios[] = "Vigencia superior a 24 meses (+5pts)";
        }

        // Aditivo proximo do limite legal: +10pts (RN-137)
        if ($contrato->relationLoaded('aditivos') || $contrato->aditivos()->exists()) {
            $valorOriginal = AditivoService::obterValorOriginal($contrato);
            if ($valorOriginal > 0) {
                $somaAcrescimos = (float) $contrato->aditivosVigentes()->sum('valor_acrescimo');
                $percentualAcumulado = ($somaAcrescimos / $valorOriginal) * 100;
                $limite = AditivoService::verificarLimiteLegal($contrato, $percentualAcumulado);
                if (! $limite['dentro_limite'] || $percentualAcumulado > ($limite['limite'] * 0.8)) {
                    $score += 10;
                    $criterios[] = "Aditivo proximo do limite legal ({$percentualAcumulado}%/{$limite['limite']}%) (+10pts)";
                }
            }
        }

        return ['score' => $score, 'criterios' => $criterios];
    }

    /**
     * Categoria: Financeiro (RN-138).
     */
    private static function calcularRiscoFinanceiro(Contrato $contrato): array
    {
        $score = 0;
        $criterios = [];

        // Valor > R$ 1.000.000: +10pts
        if ($contrato->valor_global > 1000000) {
            $score += 10;
            $criterios[] = 'Valor superior a R$ 1.000.000 (+10pts)';
        }

        // Percentual executado > 100% (empenhado > contratado): +15pts
        if ((float) $contrato->percentual_executado > 100) {
            $score += 15;
            $criterios[] = "Executado ({$contrato->percentual_executado}%) acima do contratado (+15pts)";
        }

        // Aditivos acima do limite legal: +10pts
        if ($contrato->relationLoaded('aditivos') || $contrato->aditivos()->exists()) {
            $valorOriginal = AditivoService::obterValorOriginal($contrato);
            if ($valorOriginal > 0) {
                $somaAcrescimos = (float) $contrato->aditivosVigentes()->sum('valor_acrescimo');
                $percentualAcumulado = ($somaAcrescimos / $valorOriginal) * 100;
                $limite = AditivoService::verificarLimiteLegal($contrato, $percentualAcumulado);
                if (! $limite['dentro_limite']) {
                    $score += 10;
                    $criterios[] = "Aditivos acima do limite legal ({$percentualAcumulado}%>{$limite['limite']}%) (+10pts)";
                }
            }
        }

        // Sem saldo (percentual executado >= 90% e contrato vigente): +5pts
        if ((float) $contrato->percentual_executado >= 90 && (float) $contrato->percentual_executado <= 100) {
            $score += 5;
            $criterios[] = "Saldo restante inferior a 10% ({$contrato->percentual_executado}% executado) (+5pts)";
        }

        return ['score' => $score, 'criterios' => $criterios];
    }

    /**
     * Categoria: Documental (RN-139).
     */
    private static function calcularRiscoDocumental(Contrato $contrato): array
    {
        $score = 0;
        $criterios = [];

        $tiposPresentes = $contrato->documentos()
            ->where('is_versao_atual', true)
            ->whereNull('deleted_at')
            ->pluck('tipo_documento')
            ->unique();

        // Checklist obrigatorio: contrato_original, publicacao_oficial, parecer_juridico, nota_empenho
        $obrigatorios = [
            TipoDocumentoContratual::ContratoOriginal->value => 'Contrato Original',
            TipoDocumentoContratual::PublicacaoOficial->value => 'Publicacao Oficial',
            TipoDocumentoContratual::ParecerJuridico->value => 'Parecer Juridico',
            TipoDocumentoContratual::NotaEmpenho->value => 'Nota de Empenho',
        ];

        foreach ($obrigatorios as $tipo => $label) {
            if (! $tiposPresentes->contains($tipo)) {
                $score += 5;
                $criterios[] = "Falta {$label} (+5pts)";
            }
        }

        // Falta termo de fiscalizacao: +5pts
        if (! $tiposPresentes->contains(TipoDocumentoContratual::RelatorioFiscalizacao->value)) {
            $score += 5;
            $criterios[] = 'Falta Relatorio de Fiscalizacao (+5pts)';
        }

        // Sem nenhum documento: +10pts adicional (criterio base)
        if ($tiposPresentes->isEmpty()) {
            $score += 10;
            $criterios[] = 'Nenhum documento anexado (+10pts)';
        }

        return ['score' => $score, 'criterios' => $criterios];
    }

    /**
     * Categoria: Juridico (RN-140).
     */
    private static function calcularRiscoJuridico(Contrato $contrato): array
    {
        $score = 0;
        $criterios = [];

        // Modalidade sensivel sem fundamento legal: +15pts
        if ($contrato->modalidade_contratacao?->isSensivel() && empty($contrato->fundamento_legal)) {
            $score += 15;
            $criterios[] = 'Modalidade sensivel sem fundamento legal (+15pts)';
        }

        // Modalidade sensivel (dispensa/inexigibilidade): +10pts
        if ($contrato->modalidade_contratacao?->isSensivel()) {
            $score += 10;
            $criterios[] = 'Modalidade sensivel (dispensa/inexigibilidade) (+10pts)';
        }

        // Sem numero de processo administrativo: +10pts
        if (empty($contrato->numero_processo)) {
            $score += 10;
            $criterios[] = 'Sem numero de processo administrativo (+10pts)';
        }

        // 4+ aditivos em 12 meses: +10pts (RN-140)
        if ($contrato->relationLoaded('aditivos') || $contrato->aditivos()->exists()) {
            $aditivosRecentes = $contrato->aditivos()
                ->where('data_assinatura', '>=', now()->subYear())
                ->count();
            if ($aditivosRecentes >= 4) {
                $score += 10;
                $criterios[] = "{$aditivosRecentes} aditivos em 12 meses (+10pts)";
            }
        }

        return ['score' => $score, 'criterios' => $criterios];
    }

    /**
     * Categoria: Operacional (RN-141).
     */
    private static function calcularRiscoOperacional(Contrato $contrato): array
    {
        $score = 0;
        $criterios = [];

        // Sem fiscal designado: +20pts (criterio base)
        if (! $contrato->fiscalAtual) {
            $score += 20;
            $criterios[] = 'Sem fiscal designado (+20pts)';
        }

        // Contrato essencial vencendo em <60 dias: +20pts (RN-141)
        if ($contrato->categoria === CategoriaContrato::Essencial && $contrato->data_fim) {
            $diasRestantes = (int) now()->startOfDay()->diffInDays($contrato->data_fim->startOfDay(), false);
            if ($diasRestantes >= 0 && $diasRestantes < 60) {
                $score += 20;
                $criterios[] = "Contrato essencial vencendo em {$diasRestantes} dias (+20pts)";
            }
        }

        // Servico continuado sem prorrogacao automatica (RN-141)
        if ($contrato->tipo === \App\Enums\TipoContrato::Servico
            && ! $contrato->prorrogacao_automatica
            && $contrato->data_fim) {
            $diasRestantes = (int) now()->startOfDay()->diffInDays($contrato->data_fim->startOfDay(), false);
            if ($diasRestantes >= 0 && $diasRestantes < 90) {
                $score += 10;
                $criterios[] = 'Servico continuado sem prorrogacao automatica vencendo em breve (+10pts)';
            }
        }

        // Aditivo registrado <=30 dias antes do vencimento: +5pts (RN-108)
        if ($contrato->relationLoaded('aditivos') || $contrato->aditivos()->exists()) {
            $ultimoAditivo = $contrato->aditivos()
                ->orderByDesc('data_assinatura')
                ->first();
            if ($ultimoAditivo && $contrato->data_fim) {
                $diasAntes = $ultimoAditivo->data_assinatura->diffInDays($contrato->data_fim, false);
                if ($diasAntes >= 0 && $diasAntes <= 30) {
                    $score += 5;
                    $criterios[] = 'Ultimo aditivo registrado proximo ao vencimento (+5pts)';
                }
            }
        }

        return ['score' => $score, 'criterios' => $criterios];
    }
}
