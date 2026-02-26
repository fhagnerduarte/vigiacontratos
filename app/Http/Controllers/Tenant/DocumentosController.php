<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\TipoDocumentoContratual;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreDocumentoRequest;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\Secretaria;
use App\Services\DocumentoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentosController extends Controller
{
    /**
     * Central de Documentos — listagem de contratos com indicadores de completude (RN-132).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Documento::class);

        $indicadores = DocumentoService::gerarIndicadoresDashboard();

        $query = Contrato::with([
            'secretaria',
            'documentos' => fn ($q) => $q->versaoAtual(),
        ])->orderBy('created_at', 'desc');

        // Filtros (RN-131)
        if ($request->filled('numero_contrato')) {
            $query->where('numero', 'like', '%' . $request->numero_contrato . '%');
        }

        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_id', $request->secretaria_id);
        }

        if ($request->filled('tipo_documento')) {
            $tipoFiltro = $request->tipo_documento;
            $query->whereHas('documentos', function ($q) use ($tipoFiltro) {
                $q->where('tipo_documento', $tipoFiltro)->versaoAtual();
            });
        }

        if ($request->filled('completude')) {
            // Filtro por completude será aplicado após query (calculado em memória)
        }

        if ($request->filled('data_upload_de')) {
            $query->whereHas('documentos', function ($q) use ($request) {
                $q->where('created_at', '>=', $request->data_upload_de);
            });
        }

        if ($request->filled('data_upload_ate')) {
            $query->whereHas('documentos', function ($q) use ($request) {
                $q->where('created_at', '<=', $request->data_upload_ate . ' 23:59:59');
            });
        }

        $contratos = $query->paginate(25)->withQueryString();

        // Filtro de completude pós-query (campo calculado)
        if ($request->filled('completude')) {
            $completudeFiltro = $request->completude;
            $contratos->setCollection(
                $contratos->getCollection()->filter(
                    fn ($c) => $c->status_completude->value === $completudeFiltro
                )
            );
        }

        $secretarias = Secretaria::orderBy('nome')->get();
        $tiposDocumento = TipoDocumentoContratual::cases();

        return view('tenant.documentos.index', compact(
            'indicadores',
            'contratos',
            'secretarias',
            'tiposDocumento',
        ));
    }

    /**
     * Upload de documento vinculado a um contrato (RN-039, RN-040).
     */
    public function store(StoreDocumentoRequest $request, Contrato $contrato): RedirectResponse
    {
        $this->authorize('create', Documento::class);

        $tipoDocumento = TipoDocumentoContratual::from($request->validated('tipo_documento'));

        try {
            DocumentoService::upload(
                arquivo: $request->file('arquivo'),
                documentable: $contrato,
                tipoDocumento: $tipoDocumento,
                user: $request->user(),
                ip: $request->ip(),
                descricao: $request->validated('descricao'),
            );

            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('success', 'Documento enviado com sucesso.');
        } catch (\RuntimeException $e) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Download autenticado de documento (RN-130).
     */
    public function download(Request $request, Documento $documento): StreamedResponse|RedirectResponse
    {
        $this->authorize('download', $documento);

        try {
            return DocumentoService::download($documento, $request->user(), $request->ip());
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Verificar integridade SHA-256 de um documento (RN-221).
     */
    public function verificarIntegridade(Request $request, Documento $documento): RedirectResponse
    {
        $this->authorize('verificarIntegridade', $documento);

        $status = DocumentoService::verificarIntegridade($documento);

        $mensagem = match ($status) {
            \App\Enums\StatusIntegridade::Ok => 'Integridade verificada: documento íntegro.',
            \App\Enums\StatusIntegridade::Divergente => 'ATENÇÃO: Integridade comprometida! Download bloqueado.',
            \App\Enums\StatusIntegridade::ArquivoAusente => 'Arquivo não encontrado no storage.',
        };

        $flash = $status === \App\Enums\StatusIntegridade::Ok ? 'success' : 'error';

        return redirect()->back()->with($flash, $mensagem);
    }

    /**
     * Exclusão lógica de documento (RN-134).
     */
    public function destroy(Request $request, Documento $documento): RedirectResponse
    {
        $this->authorize('delete', $documento);

        DocumentoService::excluir($documento, $request->user(), $request->ip());

        $contrato = $documento->documentable;

        return redirect()->route('tenant.contratos.show', $contrato)
            ->with('success', 'Documento excluído com sucesso.');
    }
}
