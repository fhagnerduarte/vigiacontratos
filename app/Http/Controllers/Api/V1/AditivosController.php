<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AditivoResource;
use App\Models\Aditivo;
use App\Models\Contrato;
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
}
