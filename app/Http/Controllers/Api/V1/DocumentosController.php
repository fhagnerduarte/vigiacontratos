<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TipoDocumentoContratual;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreDocumentoRequest;
use App\Http\Resources\DocumentoResource;
use App\Models\Contrato;
use App\Models\Documento;
use App\Services\DocumentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DocumentosController extends Controller
{
    public function store(StoreDocumentoRequest $request, int $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        $this->authorize('create', Documento::class);

        $tipoDocumento = TipoDocumentoContratual::from($request->validated('tipo_documento'));

        try {
            $documento = DocumentoService::upload(
                arquivo: $request->file('arquivo'),
                documentable: $contrato,
                tipoDocumento: $tipoDocumento,
                user: $request->user(),
                ip: $request->ip(),
                descricao: $request->validated('descricao'),
            );

            return (new DocumentoResource($documento))
                ->response()
                ->setStatusCode(201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
