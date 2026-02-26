<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FornecedorResource;
use App\Models\Fornecedor;
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
}
