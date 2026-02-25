<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Enums\ModalidadeContratacao;
use App\Enums\NivelRisco;
use App\Enums\TipoContrato;
use App\Models\Secretaria;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->only([
            'secretaria_id',
            'tipo_contrato',
            'modalidade',
            'faixa_valor_min',
            'faixa_valor_max',
            'nivel_risco',
        ]);

        $dados = DashboardService::obterDadosCacheados(array_filter($filtros) ?: null);

        $secretarias = Secretaria::orderBy('nome')->get(['id', 'nome', 'sigla']);
        $tiposContrato = TipoContrato::cases();
        $modalidades = ModalidadeContratacao::cases();
        $niveisRisco = NivelRisco::cases();

        $isControlador = $request->user()->hasRole('controladoria')
            || $request->user()->hasRole('administrador_geral');

        return view('tenant.dashboard.index', compact(
            'dados',
            'filtros',
            'secretarias',
            'tiposContrato',
            'modalidades',
            'niveisRisco',
            'isControlador',
        ));
    }

    public function atualizar(Request $request): RedirectResponse|JsonResponse
    {
        DashboardService::agregar();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dados do dashboard atualizados com sucesso.',
            ]);
        }

        return redirect()->route('tenant.dashboard')
            ->with('success', 'Dados do dashboard atualizados com sucesso.');
    }
}
