<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreOcorrenciaRequest;
use App\Models\Contrato;
use App\Models\Ocorrencia;
use App\Services\OcorrenciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OcorrenciasController extends Controller
{
    public function store(StoreOcorrenciaRequest $request, int $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        $dados = $request->validated();
        $resultado = OcorrenciaService::registrar($contrato, $dados, $request->user(), $request->ip());

        $alertas = [];
        if ($resultado['vencidas_count'] > 0) {
            $alertas[] = "{$resultado['vencidas_count']} ocorrencia(s) com prazo vencido.";
        }

        return response()->json([
            'message' => 'Ocorrencia registrada com sucesso.',
            'alertas' => $alertas,
        ], 201);
    }

    public function resolver(Request $request, int $id): JsonResponse
    {
        $ocorrencia = Ocorrencia::findOrFail($id);

        $request->validate([
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        OcorrenciaService::resolver(
            $ocorrencia,
            $request->user(),
            $request->input('observacoes'),
            $request->ip()
        );

        return response()->json([
            'message' => 'Ocorrencia resolvida com sucesso.',
        ]);
    }
}
