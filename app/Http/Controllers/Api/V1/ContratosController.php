<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContratoResource;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContratosController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Contrato::class);

        $query = Contrato::query()
            ->with(['fornecedor', 'secretaria']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_id', $request->input('secretaria_id'));
        }

        if ($request->filled('modalidade')) {
            $query->where('modalidade_contratacao', $request->input('modalidade'));
        }

        if ($request->filled('nivel_risco')) {
            $query->where('nivel_risco', $request->input('nivel_risco'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('data_inicio_de')) {
            $query->where('data_inicio', '>=', $request->input('data_inicio_de'));
        }

        if ($request->filled('data_inicio_ate')) {
            $query->where('data_inicio', '<=', $request->input('data_inicio_ate'));
        }

        if ($request->filled('valor_min')) {
            $query->where('valor_global', '>=', $request->input('valor_min'));
        }

        if ($request->filled('valor_max')) {
            $query->where('valor_global', '<=', $request->input('valor_max'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qb) use ($q) {
                $qb->where('numero', 'like', "%{$q}%")
                    ->orWhere('objeto', 'like', "%{$q}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        return ContratoResource::collection(
            $query->orderByDesc('created_at')->paginate($perPage)
        );
    }

    public function show(Request $request, int $id): ContratoResource
    {
        $contrato = Contrato::findOrFail($id);

        $this->authorize('view', $contrato);

        $includes = $this->parseIncludes($request);
        $contrato->load($includes);

        return new ContratoResource($contrato);
    }

    private function parseIncludes(Request $request): array
    {
        $allowed = [
            'fornecedor',
            'secretaria',
            'gestor',
            'fiscalAtual',
            'fiscalSubstituto',
            'aditivos',
            'documentosVersaoAtual',
            'alertasPendentes',
            'execucoesFinanceiras',
            'ocorrencias',
            'relatoriosFiscais',
            'encerramento',
        ];

        if (! $request->filled('include')) {
            return ['fornecedor', 'secretaria'];
        }

        $requested = explode(',', $request->input('include'));

        return array_values(array_intersect(
            array_map('trim', $requested),
            $allowed
        ));
    }
}
