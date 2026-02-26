<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreContratoRequest;
use App\Http\Requests\Tenant\UpdateContratoRequest;
use App\Http\Resources\ContratoResource;
use App\Http\Resources\DocumentoResource;
use App\Http\Resources\FiscalResource;
use App\Models\Contrato;
use App\Services\ContratoService;
use App\Services\FiscalService;
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

    public function store(StoreContratoRequest $request): JsonResponse
    {
        $this->authorize('create', Contrato::class);

        $dados = $request->validated();

        $dadosFiscal = [
            'servidor_id' => $dados['fiscal_servidor_id'] ?? null,
            'portaria_designacao' => $dados['portaria_designacao'] ?? null,
        ];
        unset($dados['fiscal_servidor_id'], $dados['portaria_designacao']);

        $dadosFiscalSubstituto = null;
        if (! empty($dados['fiscal_substituto_servidor_id'])) {
            $dadosFiscalSubstituto = [
                'servidor_id' => $dados['fiscal_substituto_servidor_id'],
                'portaria_designacao' => $dadosFiscal['portaria_designacao'] ?? null,
            ];
        }
        unset($dados['fiscal_substituto_servidor_id']);

        $dados['prorrogacao_automatica'] = $request->boolean('prorrogacao_automatica');

        $contrato = ContratoService::criar($dados, $dadosFiscal, $request->user(), $request->ip());

        if ($dadosFiscalSubstituto) {
            FiscalService::designarSubstituto($contrato, $dadosFiscalSubstituto);
        }

        $contrato->load(['fornecedor', 'secretaria']);

        return (new ContratoResource($contrato))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateContratoRequest $request, int $id): ContratoResource|JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        $this->authorize('update', $contrato);

        if ($contrato->status === \App\Enums\StatusContrato::Vencido) {
            return response()->json([
                'message' => 'Contrato vencido não pode ser editado (RN-006).',
            ], 422);
        }

        $dados = $request->validated();
        $dados['prorrogacao_automatica'] = $request->boolean('prorrogacao_automatica');

        $contrato = ContratoService::atualizar($contrato, $dados, $request->user(), $request->ip());
        $contrato->load(['fornecedor', 'secretaria']);

        return new ContratoResource($contrato);
    }

    public function destroy(int $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        $this->authorize('delete', $contrato);

        $contrato->delete();

        return response()->json(null, 204);
    }

    public function fiscais(int $id): AnonymousResourceCollection
    {
        $contrato = Contrato::findOrFail($id);

        $this->authorize('view', $contrato);

        $contrato->load('fiscais.servidor');

        return FiscalResource::collection($contrato->fiscais);
    }

    public function documentos(Request $request, int $id): AnonymousResourceCollection
    {
        $contrato = Contrato::findOrFail($id);

        $this->authorize('view', $contrato);

        $query = $contrato->documentos()->versaoAtual();

        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->input('tipo_documento'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        return DocumentoResource::collection(
            $query->orderByDesc('created_at')->paginate($perPage)
        );
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
