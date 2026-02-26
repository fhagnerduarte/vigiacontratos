<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertaResource;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertasController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Alerta::query()->with('contrato');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('tipo_evento')) {
            $query->where('tipo_evento', $request->input('tipo_evento'));
        }

        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->input('prioridade'));
        }

        if ($request->filled('contrato_id')) {
            $query->where('contrato_id', $request->input('contrato_id'));
        }

        if ($request->boolean('pendentes')) {
            $query->pendentes();
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        return AlertaResource::collection(
            $query->orderByDesc('created_at')->paginate($perPage)
        );
    }

    public function show(Alerta $alerta): AlertaResource
    {
        $alerta->load('contrato');

        return new AlertaResource($alerta);
    }
}
