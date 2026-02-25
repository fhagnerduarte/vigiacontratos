<?php

namespace App\Services;

use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\TipoEventoAlerta;
use App\Enums\TipoExecucaoFinanceira;
use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\ExecucaoFinanceira;
use App\Models\User;
use Carbon\Carbon;

class ExecucaoFinanceiraService
{
    /**
     * Registra uma execucao financeira e recalcula o percentual executado (RN-032).
     * Recalcula saldo contratual (IMP-053).
     * Gera alerta imediato se data_execucao > contrato.data_fim (Regra 2, IMP-051).
     * Gera alerta se empenho insuficiente (Regra 7, IMP-053).
     *
     * @return array{execucao: ExecucaoFinanceira, alerta: bool, alerta_vencimento: bool, alerta_empenho: bool}
     */
    public static function registrar(Contrato $contrato, array $dados, User $user): array
    {
        $tipoExecucao = $dados['tipo_execucao'] ?? TipoExecucaoFinanceira::Pagamento->value;

        $execucao = ExecucaoFinanceira::create([
            'contrato_id' => $contrato->id,
            'tipo_execucao' => $tipoExecucao,
            'descricao' => $dados['descricao'],
            'valor' => $dados['valor'],
            'data_execucao' => $dados['data_execucao'],
            'numero_nota_fiscal' => $dados['numero_nota_fiscal'] ?? null,
            'numero_empenho' => $dados['numero_empenho'] ?? null,
            'competencia' => $dados['competencia'] ?? null,
            'observacoes' => $dados['observacoes'] ?? null,
            'registrado_por' => $user->id,
        ]);

        // Se e empenho adicional, atualiza valor_empenhado do contrato
        if ($tipoExecucao === TipoExecucaoFinanceira::EmpenhoAdicional->value
            || $tipoExecucao instanceof TipoExecucaoFinanceira && $tipoExecucao === TipoExecucaoFinanceira::EmpenhoAdicional
        ) {
            self::recalcularEmpenho($contrato);
        }

        // Recalcula percentual executado e saldo (RN-032, IMP-053)
        $somaExecucoes = $contrato->execucoesFinanceiras()
            ->where('tipo_execucao', '!=', TipoExecucaoFinanceira::EmpenhoAdicional->value)
            ->sum('valor');
        $percentual = $contrato->valor_global > 0
            ? round(($somaExecucoes / $contrato->valor_global) * 100, 2)
            : 0;

        // Recalcula saldo contratual
        $saldo = self::calcularSaldo($contrato);

        $contrato->updateQuietly([
            'percentual_executado' => $percentual,
            'saldo_contratual' => $saldo['saldo'],
        ]);

        // Alerta se > 100% (RN-033)
        $alerta = $percentual > 100;

        // Alerta imediato: execucao apos vencimento (Regra 2, IMP-051)
        $alertaVencimento = false;
        if ($contrato->data_fim) {
            $dataExecucao = Carbon::parse($dados['data_execucao']);
            if ($dataExecucao->greaterThan($contrato->data_fim)) {
                self::gerarAlertaExecucaoAposVencimento($contrato, $execucao);
                $alertaVencimento = true;
            }
        }

        // Alerta: empenho insuficiente (Regra 7, IMP-053)
        $alertaEmpenho = false;
        if ($saldo['empenho_insuficiente']) {
            self::gerarAlertaEmpenhoInsuficiente($contrato, $saldo);
            $alertaEmpenho = true;
        }

        return [
            'execucao' => $execucao,
            'alerta' => $alerta,
            'alerta_vencimento' => $alertaVencimento,
            'alerta_empenho' => $alertaEmpenho,
        ];
    }

    /**
     * Calcula o saldo contratual e verifica empenho insuficiente.
     *
     * @return array{saldo: float, total_pago: float, valor_empenhado: float, valor_global: float, empenho_insuficiente: bool}
     */
    public static function calcularSaldo(Contrato $contrato): array
    {
        $valorGlobal = (float) $contrato->valor_global;

        // Total pago = soma de pagamentos + liquidacoes (exclui empenhos adicionais)
        $totalPago = (float) $contrato->execucoesFinanceiras()
            ->where('tipo_execucao', '!=', TipoExecucaoFinanceira::EmpenhoAdicional->value)
            ->sum('valor');

        // Valor empenhado = campo do contrato (atualizado por recalcularEmpenho)
        $valorEmpenhado = (float) ($contrato->valor_empenhado ?? 0);

        // Saldo = valor global - total pago
        $saldo = round($valorGlobal - $totalPago, 2);

        // Empenho insuficiente: total pago > valor empenhado (quando empenho esta definido)
        $empenhoInsuficiente = $valorEmpenhado > 0 && $totalPago > $valorEmpenhado;

        return [
            'saldo' => $saldo,
            'total_pago' => $totalPago,
            'valor_empenhado' => $valorEmpenhado,
            'valor_global' => $valorGlobal,
            'empenho_insuficiente' => $empenhoInsuficiente,
        ];
    }

    /**
     * Recalcula o valor empenhado baseado nos empenhos adicionais + empenho inicial do contrato.
     */
    public static function recalcularEmpenho(Contrato $contrato): void
    {
        $empenhoInicial = (float) ($contrato->valor_empenhado ?? 0);

        $empenhoAdicional = (float) $contrato->execucoesFinanceiras()
            ->where('tipo_execucao', TipoExecucaoFinanceira::EmpenhoAdicional->value)
            ->sum('valor');

        // Se nao ha empenho inicial definido, define como soma dos adicionais
        // Se ha empenho inicial, soma os adicionais
        $totalEmpenhado = $empenhoAdicional > 0
            ? max($empenhoInicial, 0) + $empenhoAdicional
            : $empenhoInicial;

        if ($totalEmpenhado > 0) {
            $contrato->updateQuietly(['valor_empenhado' => $totalEmpenhado]);
        }
    }

    /**
     * Retorna resumo financeiro do contrato (IMP-053).
     *
     * @return array{valor_global: float, valor_empenhado: float, total_pago: float, total_liquidado: float, saldo: float, percentual_executado: float, por_competencia: array}
     */
    public static function resumoFinanceiro(Contrato $contrato): array
    {
        $saldo = self::calcularSaldo($contrato);

        $totalLiquidado = (float) $contrato->execucoesFinanceiras()
            ->where('tipo_execucao', TipoExecucaoFinanceira::Liquidacao->value)
            ->sum('valor');

        $totalPagamentos = (float) $contrato->execucoesFinanceiras()
            ->where('tipo_execucao', TipoExecucaoFinanceira::Pagamento->value)
            ->sum('valor');

        // Agrupar por competencia
        $porCompetencia = $contrato->execucoesFinanceiras()
            ->whereNotNull('competencia')
            ->where('tipo_execucao', '!=', TipoExecucaoFinanceira::EmpenhoAdicional->value)
            ->selectRaw('competencia, SUM(valor) as total, COUNT(*) as quantidade')
            ->groupBy('competencia')
            ->orderBy('competencia')
            ->get()
            ->map(fn ($item) => [
                'competencia' => $item->competencia,
                'total' => (float) $item->total,
                'quantidade' => (int) $item->quantidade,
            ])
            ->toArray();

        return [
            'valor_global' => $saldo['valor_global'],
            'valor_empenhado' => $saldo['valor_empenhado'],
            'total_pago' => $totalPagamentos,
            'total_liquidado' => $totalLiquidado,
            'saldo' => $saldo['saldo'],
            'percentual_executado' => (float) ($contrato->percentual_executado ?? 0),
            'empenho_insuficiente' => $saldo['empenho_insuficiente'],
            'por_competencia' => $porCompetencia,
        ];
    }

    /**
     * Gera alerta de empenho insuficiente (Regra 7, IMP-053).
     */
    private static function gerarAlertaEmpenhoInsuficiente(Contrato $contrato, array $saldo): void
    {
        // Deduplicacao
        $existente = Alerta::where('contrato_id', $contrato->id)
            ->where('tipo_evento', TipoEventoAlerta::EmpenhoInsuficiente->value)
            ->naoResolvidos()
            ->exists();

        if ($existente) {
            return;
        }

        Alerta::create([
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::EmpenhoInsuficiente->value,
            'prioridade' => PrioridadeAlerta::Urgente->value,
            'status' => StatusAlerta::Pendente->value,
            'dias_para_vencimento' => $contrato->dias_para_vencimento ?? 0,
            'dias_antecedencia_config' => 0,
            'data_vencimento' => $contrato->data_fim,
            'data_disparo' => now(),
            'mensagem' => "CRITICO: Empenho insuficiente no contrato {$contrato->numero}. " .
                "Total pago: R$ " . number_format($saldo['total_pago'], 2, ',', '.') .
                " excede o valor empenhado: R$ " . number_format($saldo['valor_empenhado'], 2, ',', '.') .
                ". Risco de pagamento sem cobertura orcamentaria.",
            'tentativas_envio' => 0,
        ]);
    }

    /**
     * Gera alerta imediato de execucao apos vencimento (Regra 2).
     */
    private static function gerarAlertaExecucaoAposVencimento(
        Contrato $contrato,
        ExecucaoFinanceira $execucao
    ): void {
        // Deduplicacao
        $existente = Alerta::where('contrato_id', $contrato->id)
            ->where('tipo_evento', TipoEventoAlerta::ExecucaoAposVencimento->value)
            ->naoResolvidos()
            ->exists();

        if ($existente) {
            return;
        }

        Alerta::create([
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ExecucaoAposVencimento->value,
            'prioridade' => PrioridadeAlerta::Urgente->value,
            'status' => StatusAlerta::Pendente->value,
            'dias_para_vencimento' => 0,
            'dias_antecedencia_config' => 0,
            'data_vencimento' => $contrato->data_fim,
            'data_disparo' => now(),
            'mensagem' => "CRITICO: Execucao financeira de R$ " .
                number_format((float) $execucao->valor, 2, ',', '.') .
                " registrada em " . Carbon::parse($execucao->data_execucao)->format('d/m/Y') .
                " para o contrato {$contrato->numero} que venceu em " .
                $contrato->data_fim->format('d/m/Y') . ". Pagamento sem cobertura contratual.",
            'tentativas_envio' => 0,
        ]);
    }
}
