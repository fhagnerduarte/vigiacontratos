<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreRelatorioFiscalRequest;
use App\Models\Contrato;
use App\Services\RelatorioFiscalService;
use Illuminate\Http\RedirectResponse;

class RelatoriosFiscaisController extends Controller
{
    public function store(StoreRelatorioFiscalRequest $request, Contrato $contrato): RedirectResponse
    {
        $dados = $request->validated();
        $resultado = RelatorioFiscalService::registrar($contrato, $dados, $request->user());

        $mensagem = 'Relatorio fiscal registrado com sucesso.';
        $tipo = 'success';

        if ($resultado['alerta_resolvido']) {
            $mensagem .= ' O alerta de fiscal sem relatorio foi resolvido automaticamente.';
        }

        return redirect()->route('tenant.contratos.show', $contrato)
            ->with($tipo, $mensagem);
    }
}
