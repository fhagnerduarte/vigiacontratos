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
        if ($resultado['alerta']) {
            $mensagem .= ' ATENCAO: O valor executado ultrapassou o valor contratado (RN-033).';
        }

        return redirect()->route('tenant.contratos.show', $contrato)
            ->with($resultado['alerta'] ? 'warning' : 'success', $mensagem);
    }
}
