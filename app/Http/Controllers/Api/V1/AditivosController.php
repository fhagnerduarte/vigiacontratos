<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusContrato;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreAditivoRequest;
use App\Http\Resources\AditivoResource;
use App\Models\Aditivo;
use App\Models\Contrato;
use App\Services\AditivoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AditivosController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Aditivo::class);

        $query = Aditivo::query()->with(['contrato', 'contrato.fornecedor']);

        if ($request->filled('contrato_id')) {
            $query->where('contrato_id', $request->input('contrato_id'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        return AditivoResource::collection(
            $query->orderByDesc('created_at')->paginate($perPage)
        );
    }

    public function show(Aditivo $aditivo): AditivoResource
    {
        $this->authorize('view', $aditivo);

        $aditivo->load(['contrato', 'contrato.fornecedor', 'documentosVersaoAtual', 'workflowAprovacoes']);

        return new AditivoResource($aditivo);
    }

    public function porContrato(Request $request, int $id): AnonymousResourceCollection
    {
        $contrato = Contrato::findOrFail($id);

        $this->authorize('view', $contrato);

        $perPage = min((int) $request->input('per_page', 15), 100);

        return AditivoResource::collection(
            $contrato->aditivos()
                ->orderByDesc('numero_sequencial')
                ->paginate($perPage)
        );
    }

    public function store(StoreAditivoRequest $request, int $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        $this->authorize('create', Aditivo::class);

        if (! in_array($contrato->status, [StatusContrato::Vigente, StatusContrato::Vencido])) {
            return response()->json([
                'message' => 'Aditivo so pode ser adicionado a contrato vigente ou vencido (RN-009/RN-052).',
            ], 422);
        }

        $dados = $request->validated();

        $novoAcrescimo = (float) ($dados['valor_acrescimo'] ?? 0);
        $percentualProjetado = AditivoService::calcularPercentualAcumulado($contrato, $novoAcrescimo);
        $limiteLegal = AditivoService::verificarLimiteLegal($contrato, $percentualProjetado);

        if (! $limiteLegal['dentro_limite']) {
            if ($limiteLegal['is_bloqueante']) {
                return response()->json([
                    'message' => "Percentual acumulado ({$percentualProjetado}%) ultrapassa limite legal de {$limiteLegal['limite']}% (RN-101).",
                ], 422);
            }

            if (empty($dados['justificativa_excesso_limite'])) {
                return response()->json([
                    'message' => 'Percentual acumulado ultrapassa limite legal. Justificativa obrigatoria (RN-102).',
                    'errors' => ['justificativa_excesso_limite' => ['Justificativa obrigatoria para prosseguir.']],
                ], 422);
            }
        }

        try {
            $aditivo = AditivoService::criar($dados, $contrato, $request->user(), $request->ip());

            return (new AditivoResource($aditivo))
                ->response()
                ->setStatusCode(201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
