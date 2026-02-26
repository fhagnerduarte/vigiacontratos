<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ClassificacaoSigilo;
use App\Enums\ModalidadeContratacao;
use App\Enums\StatusContrato;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Services\DadosAbertosService;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function index(string $slug)
    {
        $tenant = app('tenant');
        $indicadores = DadosAbertosService::obterIndicadoresPublicos();

        return view('portal.index', compact('tenant', 'indicadores'));
    }

    public function contratos(Request $request, string $slug)
    {
        $tenant = app('tenant');

        $query = Contrato::withoutGlobalScopes()
            ->visivelNoPortal()
            ->with(['fornecedor', 'secretaria']);

        if ($request->filled('secretaria')) {
            $query->where('secretaria_id', $request->secretaria);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('modalidade')) {
            $query->where('modalidade_contratacao', $request->modalidade);
        }

        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('numero', 'like', "%{$busca}%")
                    ->orWhere('objeto', 'like', "%{$busca}%");
            });
        }

        $contratos = $query->orderByDesc('created_at')->paginate(20);

        $secretarias = Secretaria::orderBy('nome')->get();
        $anos = Contrato::withoutGlobalScopes()
            ->visivelNoPortal()
            ->select('ano')
            ->distinct()
            ->orderByDesc('ano')
            ->pluck('ano');

        return view('portal.contratos.index', compact('tenant', 'contratos', 'secretarias', 'anos'));
    }

    public function contratoDetalhe(string $slug, string $numero)
    {
        $tenant = app('tenant');

        $contrato = Contrato::withoutGlobalScopes()
            ->visivelNoPortal()
            ->where('numero', $numero)
            ->with(['fornecedor', 'secretaria', 'aditivos'])
            ->firstOrFail();

        return view('portal.contratos.show', compact('tenant', 'contrato'));
    }

    public function fornecedores(Request $request, string $slug)
    {
        $tenant = app('tenant');

        $query = Fornecedor::query();

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('razao_social', 'like', "%{$busca}%")
                    ->orWhere('cnpj', 'like', "%{$busca}%");
            });
        }

        $fornecedores = $query->orderBy('razao_social')->paginate(20);

        return view('portal.fornecedores.index', compact('tenant', 'fornecedores'));
    }

    public function dadosAbertos(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $formato = $request->input('formato');

        if ($formato === 'json') {
            return DadosAbertosService::exportarContratosJson($request->all());
        }

        if ($formato === 'csv') {
            return DadosAbertosService::exportarContratosCsv($request->all());
        }

        return view('portal.dados-abertos', compact('tenant'));
    }
}
