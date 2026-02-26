<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServidorResource;
use App\Models\Servidor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServidoresController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Servidor::class);

        $query = Servidor::query()->with('secretaria');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qb) use ($q) {
                $qb->where('nome', 'like', "%{$q}%")
                    ->orWhere('matricula', 'like', "%{$q}%")
                    ->orWhere('cargo', 'like', "%{$q}%");
            });
        }

        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_id', $request->input('secretaria_id'));
        }

        if ($request->has('is_ativo')) {
            $query->where('is_ativo', $request->boolean('is_ativo'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        return ServidorResource::collection(
            $query->orderBy('nome')->paginate($perPage)
        );
    }

    public function show(Servidor $servidor): ServidorResource
    {
        $this->authorize('view', $servidor);

        $servidor->load('secretaria');

        return new ServidorResource($servidor);
    }
}
