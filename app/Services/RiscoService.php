<?php

namespace App\Services;

use App\Enums\CategoriaContrato;
use App\Enums\ClassificacaoSigilo;
use App\Enums\FaseContratual;
use App\Enums\NivelRisco;
use App\Enums\StatusComparativoPreco;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Models\ComparativoPreco;
use App\Models\Contrato;

class RiscoService
{
    /**
     * Calcula o score de risco automaticamente (RN-029).
     * Critérios conforme Fluxo 2 do banco de conhecimento.
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
     * Calcula o score de risco expandido com 6 categorias (RN-136 a RN-142, RN-434).
     * Categorias: vencimento, financeiro, documental, jurídico, operacional, transparência.
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
            'transparencia' => self::calcularRiscoTransparencia($contrato),
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

        // Vigência > 24 meses: +5pts (critério base)
        if ($contrato->prazo_meses > 24) {
            $score += 5;
            $criterios[] = "Vigência superior a 24 meses (+5pts)";
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
                    $criterios[] = "Aditivo próximo do limite legal ({$percentualAcumulado}%/{$limite['limite']}%) (+10pts)";
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

        // Sobrepreco PNP: +10pts se contrato acima do teto referencial
        $comparativo = ComparativoPreco::where('contrato_id', $contrato->id)
            ->latest()
            ->first();
        if ($comparativo && $comparativo->status_comparativo === StatusComparativoPreco::Sobrepreco) {
            $score += 10;
            $criterios[] = "Sobrepreço detectado ({$comparativo->percentual_diferenca}% acima do referencial) (+10pts)";
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
            ->map(fn ($t) => $t instanceof TipoDocumentoContratual ? $t->value : $t)
            ->unique();

        // Checklist configurável por tipo de contrato (RN-129 + RN-139)
        $checklistObrigatorio = $contrato->tipo instanceof TipoContrato
            ? DocumentoService::obterChecklistPorTipo($contrato->tipo)
            : DocumentoService::CHECKLIST_OBRIGATORIO;

        foreach ($checklistObrigatorio as $tipo) {
            if (! $tiposPresentes->contains($tipo->value)) {
                $score += 5;
                $criterios[] = "Falta {$tipo->label()} (+5pts)";
            }
        }

        // Falta termo de fiscalização: +5pts
        if (! $tiposPresentes->contains(TipoDocumentoContratual::RelatorioFiscalizacao->value)) {
            $score += 5;
            $criterios[] = 'Falta Relatório de Fiscalização (+5pts)';
        }

        // Falta relatório de execução (medição): +5pts (RN-139)
        if (! $tiposPresentes->contains(TipoDocumentoContratual::RelatorioMedicao->value)) {
            $score += 5;
            $criterios[] = 'Falta Relatório de Medição (+5pts)';
        }

        // Sem nenhum documento: +10pts adicional (critério base)
        if ($tiposPresentes->isEmpty()) {
            $score += 10;
            $criterios[] = 'Nenhum documento anexado (+10pts)';
        }

        // Conformidade por fase crítica (IMP-050, RN-139)
        $fasesCriticas = [FaseContratual::Formalizacao, FaseContratual::Publicacao];
        foreach ($fasesCriticas as $faseCritica) {
            $conformidade = ChecklistService::calcularConformidadeFase($contrato, $faseCritica);
            if ($conformidade['total_obrigatorios'] > 0 && $conformidade['percentual'] < 100) {
                $score += 5;
                $criterios[] = "Fase {$faseCritica->label()} incompleta ({$conformidade['total_presentes']}/{$conformidade['total_obrigatorios']}) (+5pts)";
            }
        }

        return ['score' => $score, 'criterios' => $criterios];
    }

    /**
     * Categoria: Jurídico (RN-140).
     */
    private static function calcularRiscoJuridico(Contrato $contrato): array
    {
        $score = 0;
        $criterios = [];

        // Modalidade sensível sem fundamento legal: +15pts
        if ($contrato->modalidade_contratacao?->isSensivel() && empty($contrato->fundamento_legal)) {
            $score += 15;
            $criterios[] = 'Modalidade sensível sem fundamento legal (+15pts)';
        }

        // Modalidade sensível (dispensa/inexigibilidade): +10pts
        if ($contrato->modalidade_contratacao?->isSensivel()) {
            $score += 10;
            $criterios[] = 'Modalidade sensível (dispensa/inexigibilidade) (+10pts)';
        }

        // Sem número de processo administrativo: +10pts
        if (empty($contrato->numero_processo)) {
            $score += 10;
            $criterios[] = 'Sem número de processo administrativo (+10pts)';
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

        // Prazo superior ao permitido por lei: +10pts (RN-140)
        if ($contrato->prazo_meses) {
            $limiteMeses = match ($contrato->tipo) {
                \App\Enums\TipoContrato::Servico => 60,
                default => 36,
            };
            if ($contrato->prazo_meses > $limiteMeses) {
                $score += 10;
                $criterios[] = "Prazo ({$contrato->prazo_meses} meses) superior ao limite legal ({$limiteMeses} meses) (+10pts)";
            }
        }

        // Ausência de justificativa formal em aditivo: +10pts (RN-140)
        if ($contrato->relationLoaded('aditivos') || $contrato->aditivos()->exists()) {
            $aditivosSemJustificativa = $contrato->aditivos()
                ->where(function ($q) {
                    $q->whereNull('justificativa_tecnica')
                      ->orWhere('justificativa_tecnica', '');
                })
                ->count();
            if ($aditivosSemJustificativa > 0) {
                $score += 10;
                $criterios[] = "{$aditivosSemJustificativa} aditivo(s) sem justificativa técnica (+10pts)";
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

        // Sem fiscal designado: +20pts (critério base)
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

        // Serviço continuado sem prorrogação automática (RN-141)
        if ($contrato->tipo === \App\Enums\TipoContrato::Servico
            && ! $contrato->prorrogacao_automatica
            && $contrato->data_fim) {
            $diasRestantes = (int) now()->startOfDay()->diffInDays($contrato->data_fim->startOfDay(), false);
            if ($diasRestantes >= 0 && $diasRestantes < 90) {
                $score += 10;
                $criterios[] = 'Serviço continuado sem prorrogação automática vencendo em breve (+10pts)';
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
                    $criterios[] = 'Último aditivo registrado próximo ao vencimento (+5pts)';
                }
            }
        }

        // Ocorrências pendentes vencidas: +5pts por ocorrência (IMP-054, RN-141)
        $ocorrenciasVencidas = $contrato->ocorrencias()
            ->where('resolvida', false)
            ->whereNotNull('prazo_providencia')
            ->where('prazo_providencia', '<', now()->toDateString())
            ->count();
        if ($ocorrenciasVencidas > 0) {
            $pontos = min(15, $ocorrenciasVencidas * 5);
            $score += $pontos;
            $criterios[] = "{$ocorrenciasVencidas} ocorrência(s) com prazo vencido (+{$pontos}pts)";
        }

        // Fiscal sem relatório recente (>60 dias): +10pts (IMP-054, RN-141)
        if ($contrato->fiscalAtual && $contrato->fiscalAtual->data_ultimo_relatorio) {
            $diasSemRelatorio = (int) $contrato->fiscalAtual->data_ultimo_relatorio->diffInDays(now());
            if ($diasSemRelatorio > 60) {
                $score += 10;
                $criterios[] = "Fiscal sem relatório há {$diasSemRelatorio} dias (+10pts)";
            }
        } elseif ($contrato->fiscalAtual && ! $contrato->fiscalAtual->data_ultimo_relatorio) {
            $score += 10;
            $criterios[] = 'Fiscal nunca registrou relatório (+10pts)';
        }

        return ['score' => $score, 'criterios' => $criterios];
    }

    /**
     * Categoria: Transparência (RN-434, LAI 12.527/2011).
     */
    private static function calcularRiscoTransparencia(Contrato $contrato): array
    {
        $score = 0;
        $criterios = [];

        // Contrato público não publicado no portal: +10pts
        if ($contrato->classificacao_sigilo === ClassificacaoSigilo::Publico
            && ! $contrato->publicado_portal) {
            $score += 10;
            $criterios[] = 'Contrato público não publicado no portal (+10pts)';
        }

        // Classificação de sigilo sem justificativa: +10pts
        if ($contrato->classificacao_sigilo !== null
            && $contrato->classificacao_sigilo !== ClassificacaoSigilo::Publico
            && empty($contrato->justificativa_sigilo)) {
            $score += 10;
            $criterios[] = 'Classificação de sigilo sem justificativa (+10pts)';
        }

        // Dados de publicação incompletos: +5pts
        if (empty($contrato->data_publicacao) || empty($contrato->veiculo_publicacao)) {
            $score += 5;
            $criterios[] = 'Dados de publicação incompletos (+5pts)';
        }

        return ['score' => $score, 'criterios' => $criterios];
    }
}
