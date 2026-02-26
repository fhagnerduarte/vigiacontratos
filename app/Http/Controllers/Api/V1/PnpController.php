<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CategoriaServico;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePrecoReferencialRequest;
use App\Http\Resources\ComparativoPrecoResource;
use App\Http\Resources\PrecoReferencialResource;
use App\Models\Contrato;
use App\Models\PrecoReferencial;
use App\Services\PnpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PnpController extends Controller
{
    /**
     * GET /pnp/precos — Listar precos referenciais com filtros.
     */
    public function precos(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $filtros = $request->only(['categoria_servico', 'is_ativo', 'vigentes', 'search', 'per_page']);
        $precos = PnpService::listarPrecos($filtros);

        return PrecoReferencialResource::collection($precos)->response();
    }

    /**
     * GET /pnp/precos/{id} — Detalhe de um preco referencial.
     */
    public function showPreco(int $id): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $preco = PrecoReferencial::with('registrador')->findOrFail($id);

        return response()->json([
            'data' => new PrecoReferencialResource($preco),
        ]);
    }

    /**
     * POST /pnp/precos — Registrar novo preco referencial.
     */
    public function storePreco(StorePrecoReferencialRequest $request): JsonResponse
    {
        $this->authorize('create', Contrato::class);

        $preco = PnpService::registrarPreco(
            $request->validated(),
            auth()->id()
        );

        return response()->json([
            'data' => new PrecoReferencialResource($preco->load('registrador')),
            'message' => 'Preco referencial registrado com sucesso.',
        ], 201);
    }

    /**
     * PUT /pnp/precos/{id} — Atualizar preco referencial.
     */
    public function updatePreco(StorePrecoReferencialRequest $request, int $id): JsonResponse
    {
        $this->authorize('create', Contrato::class);

        $preco = PnpService::atualizarPreco($id, $request->validated());

        return response()->json([
            'data' => new PrecoReferencialResource($preco->load('registrador')),
            'message' => 'Preco referencial atualizado com sucesso.',
        ]);
    }

    /**
     * GET /pnp/categorias — Listar categorias com contagem de precos.
     */
    public function categorias(): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $contagens = PrecoReferencial::ativos()
            ->selectRaw('categoria_servico, COUNT(*) as total')
            ->groupBy('categoria_servico')
            ->pluck('total', 'categoria_servico');

        $categorias = collect(CategoriaServico::cases())->map(fn (CategoriaServico $cat) => [
            'value' => $cat->value,
            'label' => $cat->label(),
            'total_precos' => $contagens->get($cat->value, 0),
        ]);

        return response()->json([
            'data' => $categorias->values(),
        ]);
    }

    /**
     * GET /pnp/comparativo — Comparativo geral contratos vs referencias.
     */
    public function comparativo(): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $resultado = PnpService::gerarComparativoGeral(auth()->id());

        return response()->json($resultado);
    }

    /**
     * POST /pnp/contratos/{contrato}/comparar — Comparar contrato especifico.
     */
    public function compararContrato(int $id): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $contrato = Contrato::findOrFail($id);
        $comparativo = PnpService::compararContrato($contrato, auth()->id());

        if ($comparativo === null) {
            return response()->json([
                'message' => 'Nao ha preco referencial vigente para a categoria deste contrato.',
                'contrato_id' => $contrato->id,
                'categoria_servico' => $contrato->categoria_servico?->value,
            ], 422);
        }

        return response()->json([
            'data' => new ComparativoPrecoResource($comparativo->load(['contrato', 'precoReferencial', 'geradoPor'])),
        ]);
    }

    /**
     * GET /pnp/indicadores — Indicadores agregados PNP.
     */
    public function indicadores(): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $indicadores = PnpService::indicadores();

        return response()->json($indicadores);
    }

    /**
     * GET /pnp/historico — Historico de precos por categoria.
     */
    public function historico(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $request->validate([
            'categoria_servico' => ['required', 'string'],
        ]);

        $categoria = CategoriaServico::from($request->input('categoria_servico'));
        $historico = PnpService::historicoPorCategoria($categoria);

        return response()->json([
            'categoria' => [
                'value' => $categoria->value,
                'label' => $categoria->label(),
            ],
            'data' => $historico,
        ]);
    }
}
