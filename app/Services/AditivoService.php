<?php

namespace App\Services;

use App\Enums\StatusAditivo;
use App\Enums\StatusContrato;
use App\Enums\TipoAditivo;
use App\Models\Aditivo;
use App\Models\ConfiguracaoLimiteAditivo;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AditivoService
{
    /**
     * Gera numero sequencial por contrato: MAX + 1 (RN-091, ADR-031).
     */
    public static function gerarNumeroSequencial(Contrato $contrato): int
    {
        $max = $contrato->aditivos()->max('numero_sequencial');

        return ($max ?? 0) + 1;
    }

    /**
     * Calcula o percentual acumulado de acrescimos sobre o valor original (RN-097).
     * Supressoes NAO entram no calculo do percentual para efeito do limite legal.
     */
    public static function calcularPercentualAcumulado(Contrato $contrato, float $novoAcrescimo = 0): float
    {
        $somaAcrescimosVigentes = (float) $contrato->aditivosVigentes()->sum('valor_acrescimo');
        $totalAcrescimos = $somaAcrescimosVigentes + $novoAcrescimo;

        $valorOriginal = self::obterValorOriginal($contrato);

        if ($valorOriginal <= 0) {
            return 0;
        }

        return round(($totalAcrescimos / $valorOriginal) * 100, 2);
    }

    /**
     * Obtem o valor original do contrato (antes de qualquer aditivo).
     * Usa o snapshot do primeiro aditivo, ou o valor_global se nenhum aditivo existe.
     */
    public static function obterValorOriginal(Contrato $contrato): float
    {
        $primeiroAditivo = $contrato->aditivos()
            ->orderBy('numero_sequencial')
            ->first();

        if ($primeiroAditivo && $primeiroAditivo->valor_anterior_contrato) {
            return (float) $primeiroAditivo->valor_anterior_contrato;
        }

        return (float) $contrato->valor_global;
    }

    /**
     * Verifica limite legal configurado para o tipo de contrato (RN-098 a RN-102).
     *
     * @return array{dentro_limite: bool, limite: float, is_bloqueante: bool, percentual: float}
     */
    public static function verificarLimiteLegal(Contrato $contrato, float $percentualAcumulado): array
    {
        $config = ConfiguracaoLimiteAditivo::where('tipo_contrato', $contrato->tipo->value)
            ->where('is_ativo', true)
            ->first();

        if (! $config) {
            return [
                'dentro_limite' => true,
                'limite' => 25.00,
                'is_bloqueante' => false,
                'percentual' => $percentualAcumulado,
            ];
        }

        return [
            'dentro_limite' => $percentualAcumulado <= (float) $config->percentual_limite,
            'limite' => (float) $config->percentual_limite,
            'is_bloqueante' => $config->is_bloqueante,
            'percentual' => $percentualAcumulado,
        ];
    }

    /**
     * Cria um aditivo completo com atualizacao do contrato pai, auditoria e workflow (RN-103).
     */
    public static function criar(array $dados, Contrato $contrato, User $user, string $ip): Aditivo
    {
        return DB::connection('tenant')->transaction(function () use ($dados, $contrato, $user, $ip) {
            // Valida contrato vigente (RN-009)
            if ($contrato->status !== StatusContrato::Vigente) {
                throw new \RuntimeException('Aditivo so pode ser vinculado a contrato vigente (RN-009).');
            }

            // Gera numero sequencial (RN-091)
            $dados['contrato_id'] = $contrato->id;
            $dados['numero_sequencial'] = self::gerarNumeroSequencial($contrato);
            $dados['status'] = StatusAditivo::Vigente->value;

            // Snapshot do valor atual do contrato (RN-104)
            $dados['valor_anterior_contrato'] = $contrato->valor_global;

            // Processa reequilibrio se aplicavel (RN-095, ADR-029)
            $tipo = TipoAditivo::from($dados['tipo']);
            if ($tipo === TipoAditivo::Reequilibrio) {
                $dados = self::processarReequilibrio($dados);
            }

            // Calcula percentual acumulado (RN-097)
            $novoAcrescimo = (float) ($dados['valor_acrescimo'] ?? 0);
            $dados['percentual_acumulado'] = self::calcularPercentualAcumulado($contrato, $novoAcrescimo);

            // Define parecer juridico obrigatorio (RN-096)
            if ($novoAcrescimo > 0) {
                $percentualAcrescimoSobreAtual = ($novoAcrescimo / (float) $contrato->valor_global) * 100;
                $dados['parecer_juridico_obrigatorio'] = $percentualAcrescimoSobreAtual > 10;
            }

            // Cria o aditivo
            $aditivo = Aditivo::create($dados);

            // Atualiza contrato pai (RN-103)
            self::atualizarContratoPai($contrato);

            // Registra auditoria no contrato (RN-105)
            AuditoriaService::registrar(
                $contrato,
                'aditivo_registrado',
                null,
                $aditivo->numero_sequencial . 'o Termo Aditivo - ' . $tipo->label(),
                $user,
                $ip
            );

            // Registra auditoria de criacao do aditivo (RN-117)
            AuditoriaService::registrarCriacao($aditivo, $aditivo->getAttributes(), $user, $ip);

            // Cria workflow de aprovacao 5 etapas (RN-335)
            WorkflowService::criarFluxo($aditivo, $user, $ip);

            return $aditivo->fresh(['contrato', 'workflowAprovacoes']);
        });
    }

    /**
     * Recalcula e atualiza os valores do contrato pai apos aditivo (RN-103).
     */
    public static function atualizarContratoPai(Contrato $contrato): void
    {
        $contrato->load('aditivosVigentes');

        // Recalcula valor_global: original + SUM(acrescimos) - SUM(supressoes)
        $valorOriginal = self::obterValorOriginal($contrato);
        $somaAcrescimos = (float) $contrato->aditivosVigentes->sum('valor_acrescimo');
        $somaSupressoes = (float) $contrato->aditivosVigentes->sum('valor_supressao');
        $novoValorGlobal = $valorOriginal + $somaAcrescimos - $somaSupressoes;

        // Atualiza data_fim se algum aditivo de prazo existe (RN-012)
        $maxDataFim = $contrato->aditivosVigentes
            ->whereNotNull('nova_data_fim')
            ->max('nova_data_fim');

        $dadosUpdate = [
            'valor_global' => max(0, $novoValorGlobal),
        ];

        if ($maxDataFim && $maxDataFim > $contrato->data_fim) {
            $dadosUpdate['data_fim'] = $maxDataFim;
            $dadosUpdate['prazo_meses'] = ContratoService::calcularPrazoMeses(
                $contrato->data_inicio->toDateString(),
                $maxDataFim instanceof \Carbon\Carbon ? $maxDataFim->toDateString() : $maxDataFim
            );

            // Resolver alertas pendentes automaticamente (RN-017)
            AlertaService::resolverAlertasPorContrato($contrato);
        }

        $contrato->updateQuietly($dadosUpdate);

        // Recalcula percentual executado com novo valor_global
        $contrato->refresh();
        $somaExecucoes = (float) $contrato->execucoesFinanceiras()->sum('valor');
        $percentualExecutado = $contrato->valor_global > 0
            ? round(($somaExecucoes / (float) $contrato->valor_global) * 100, 2)
            : 0;

        // Recalcula score de risco (inclui criterios de aditivos — RN-106 a RN-108)
        $contrato->load('fiscalAtual', 'documentos', 'aditivos');
        $risco = RiscoService::calcular($contrato);

        $contrato->updateQuietly([
            'percentual_executado' => $percentualExecutado,
            'score_risco' => $risco['score'],
            'nivel_risco' => $risco['nivel']->value,
        ]);
    }

    /**
     * Cancela um aditivo vigente e recalcula o contrato pai (RN-116).
     * Apenas admin pode cancelar.
     */
    public static function cancelar(Aditivo $aditivo, User $user, string $ip): Aditivo
    {
        return DB::connection('tenant')->transaction(function () use ($aditivo, $user, $ip) {
            if ($aditivo->status !== StatusAditivo::Vigente) {
                throw new \RuntimeException('Apenas aditivos vigentes podem ser cancelados.');
            }

            $aditivo->update(['status' => StatusAditivo::Cancelado->value]);

            // Recalcula contrato pai sem este aditivo (RN-103)
            self::atualizarContratoPai($aditivo->contrato);

            // Registra auditoria (RN-117)
            AuditoriaService::registrar(
                $aditivo,
                'aditivo_cancelado',
                StatusAditivo::Vigente->label(),
                StatusAditivo::Cancelado->label() . ' por ' . $user->nome,
                $user,
                $ip
            );

            AuditoriaService::registrar(
                $aditivo->contrato,
                'aditivo_cancelado',
                null,
                $aditivo->numero_sequencial . 'o Termo Aditivo cancelado por ' . $user->nome,
                $user,
                $ip
            );

            return $aditivo->fresh();
        });
    }

    /**
     * Processa dados especificos de reequilibrio economico-financeiro (RN-095, ADR-029).
     * Calcula valor_acrescimo = valor_reajustado - valor_anterior_reequilibrio.
     */
    public static function processarReequilibrio(array $dados): array
    {
        $valorAnterior = (float) ($dados['valor_anterior_reequilibrio'] ?? 0);
        $valorReajustado = (float) ($dados['valor_reajustado'] ?? 0);

        $dados['valor_acrescimo'] = max(0, $valorReajustado - $valorAnterior);

        return $dados;
    }
}
