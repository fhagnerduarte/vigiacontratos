<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SecretariaResource;
use App\Models\Secretaria;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SecretariasController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Secretaria::class);

        $query = Secretaria::query()->withCount('contratos');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qb) use ($q) {
                $qb->where('nome', 'like', "%{$q}%")
                    ->orWhere('sigla', 'like', "%{$q}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        return SecretariaResource::collection(
            $query->orderBy('nome')->paginate($perPage)
        );
    }

    public function show(Secretaria $secretaria): SecretariaResource
    {
        $this->authorize('view', $secretaria);

        $secretaria->loadCount('contratos');

        return new SecretariaResource($secretaria);
    }
}
