<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreExecucaoFinanceiraRequest;
use App\Models\Contrato;
use App\Services\ExecucaoFinanceiraService;
use Illuminate\Http\JsonResponse;

class ExecucoesFinanceirasController extends Controller
{
    public function store(StoreExecucaoFinanceiraRequest $request, int $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        $dados = $request->validated();
        $resultado = ExecucaoFinanceiraService::registrar($contrato, $dados, $request->user());

        $alertas = [];
        if ($resultado['alerta']) {
            $alertas[] = 'Valor executado ultrapassou o valor contratado (RN-033).';
        }
        if ($resultado['alerta_empenho']) {
            $alertas[] = 'Empenho insuficiente — pagamentos excedem o valor empenhado.';
        }
        if ($resultado['alerta_vencimento']) {
            $alertas[] = 'Execucao registrada apos o vencimento do contrato.';
        }

        return response()->json([
            'message' => 'Execucao financeira registrada com sucesso.',
            'alertas' => $alertas,
        ], 201);
    }
}
