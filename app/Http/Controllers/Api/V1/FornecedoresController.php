<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreFornecedorRequest;
use App\Http\Requests\Tenant\UpdateFornecedorRequest;
use App\Http\Resources\FornecedorResource;
use App\Models\Fornecedor;
use App\Services\FornecedorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FornecedoresController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Fornecedor::class);

        $query = Fornecedor::query()->withCount('contratos');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qb) use ($q) {
                $qb->where('razao_social', 'like', "%{$q}%")
                    ->orWhere('nome_fantasia', 'like', "%{$q}%")
                    ->orWhere('cnpj', 'like', "%{$q}%");
            });
        }

        if ($request->filled('uf')) {
            $query->where('uf', $request->input('uf'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        return FornecedorResource::collection(
            $query->orderBy('razao_social')->paginate($perPage)
        );
    }

    public function show(int $id): FornecedorResource
    {
        $fornecedor = Fornecedor::withCount('contratos')->findOrFail($id);

        $this->authorize('view', $fornecedor);

        return new FornecedorResource($fornecedor);
    }

    public function store(StoreFornecedorRequest $request): JsonResponse
    {
        $this->authorize('create', Fornecedor::class);

        $data = $request->validated();
        $data['cnpj'] = FornecedorService::formatarCnpj($data['cnpj']);

        $fornecedor = Fornecedor::create($data);

        return (new FornecedorResource($fornecedor))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateFornecedorRequest $request, int $id): FornecedorResource
    {
        $fornecedor = Fornecedor::findOrFail($id);

        $this->authorize('update', $fornecedor);

        $data = $request->validated();
        $data['cnpj'] = FornecedorService::formatarCnpj($data['cnpj']);

        $fornecedor->update($data);

        return new FornecedorResource($fornecedor->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        $fornecedor = Fornecedor::findOrFail($id);

        $this->authorize('delete', $fornecedor);

        $fornecedor->delete();

        return response()->json(null, 204);
    }
}
