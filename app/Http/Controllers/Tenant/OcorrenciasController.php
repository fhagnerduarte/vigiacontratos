<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreOcorrenciaRequest;
use App\Models\Contrato;
use App\Models\Ocorrencia;
use App\Services\OcorrenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OcorrenciasController extends Controller
{
    public function store(StoreOcorrenciaRequest $request, Contrato $contrato): RedirectResponse
    {
        $dados = $request->validated();
        $resultado = OcorrenciaService::registrar($contrato, $dados, $request->user(), $request->ip());

        $mensagem = 'Ocorrencia registrada com sucesso.';
        $tipo = 'success';

        if ($resultado['vencidas_count'] > 0) {
            $mensagem .= " ATENCAO: {$resultado['vencidas_count']} ocorrencia(s) com prazo vencido.";
            $tipo = 'warning';
        }

        return redirect()->route('tenant.contratos.show', $contrato)
            ->with($tipo, $mensagem);
    }

    public function resolver(Request $request, Ocorrencia $ocorrencia): RedirectResponse
    {
        $request->validate([
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        OcorrenciaService::resolver(
            $ocorrencia,
            $request->user(),
            $request->input('observacoes'),
            $request->ip()
        );

        return redirect()->route('tenant.contratos.show', $ocorrencia->contrato)
            ->with('success', 'Ocorrencia resolvida com sucesso.');
    }
}
