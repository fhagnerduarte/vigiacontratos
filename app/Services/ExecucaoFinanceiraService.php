<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\ExecucaoFinanceira;
use App\Models\User;

class ExecucaoFinanceiraService
{
    /**
     * Registra uma execucao financeira e recalcula o percentual executado (RN-032).
     *
     * @return array{execucao: ExecucaoFinanceira, alerta: bool}
     */
    public static function registrar(Contrato $contrato, array $dados, User $user): array
    {
        $execucao = ExecucaoFinanceira::create([
            'contrato_id' => $contrato->id,
            'descricao' => $dados['descricao'],
            'valor' => $dados['valor'],
            'data_execucao' => $dados['data_execucao'],
            'numero_nota_fiscal' => $dados['numero_nota_fiscal'] ?? null,
            'observacoes' => $dados['observacoes'] ?? null,
            'registrado_por' => $user->id,
        ]);

        // Recalcula percentual executado (RN-032)
        $somaExecucoes = $contrato->execucoesFinanceiras()->sum('valor');
        $percentual = $contrato->valor_global > 0
            ? round(($somaExecucoes / $contrato->valor_global) * 100, 2)
            : 0;

        $contrato->updateQuietly(['percentual_executado' => $percentual]);

        // Alerta se > 100% (RN-033)
        $alerta = $percentual > 100;

        return ['execucao' => $execucao, 'alerta' => $alerta];
    }
}
