<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreExecucaoFinanceiraRequest;
use App\Models\Contrato;
use App\Services\ExecucaoFinanceiraService;
use Illuminate\Http\RedirectResponse;

class ExecucoesFinanceirasController extends Controller
{
    public function store(StoreExecucaoFinanceiraRequest $request, Contrato $contrato): RedirectResponse
    {
        $dados = $request->validated();

        $resultado = ExecucaoFinanceiraService::registrar($contrato, $dados, $request->user());

        $mensagem = 'Execucao financeira registrada com sucesso.';
        $tipo = 'success';

        if ($resultado['alerta']) {
            $mensagem .= ' ATENCAO: O valor executado ultrapassou o valor contratado (RN-033).';
            $tipo = 'warning';
        }

        if ($resultado['alerta_empenho']) {
            $mensagem .= ' ALERTA: Empenho insuficiente — pagamentos excedem o valor empenhado.';
            $tipo = 'warning';
        }

        if ($resultado['alerta_vencimento']) {
            $mensagem .= ' CRITICO: Execucao registrada apos o vencimento do contrato.';
            $tipo = 'danger';
        }

        return redirect()->route('tenant.contratos.show', $contrato)
            ->with($tipo, $mensagem);
    }
}
