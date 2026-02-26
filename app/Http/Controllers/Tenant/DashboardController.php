<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Enums\ModalidadeContratacao;
use App\Enums\NivelRisco;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Models\Contrato;
use App\Models\Secretaria;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Mapeamento de faixas de valor para min/max (RN-073).
     */
    private const FAIXAS_VALOR = [
        'ate_100k' => ['min' => null, 'max' => 100000],
        '100k_500k' => ['min' => 100000, 'max' => 500000],
        '500k_1m' => ['min' => 500000, 'max' => 1000000],
        'acima_1m' => ['min' => 1000000, 'max' => null],
    ];

    public function index(Request $request): View
    {
        $filtros = $request->only([
            'secretaria_id',
            'tipo_contrato',
            'modalidade',
            'nivel_risco',
            'faixa_valor',
            'fonte_recurso',
        ]);

        // Converter faixa_valor para min/max
        if (! empty($filtros['faixa_valor']) && isset(self::FAIXAS_VALOR[$filtros['faixa_valor']])) {
            $faixa = self::FAIXAS_VALOR[$filtros['faixa_valor']];
            if ($faixa['min']) {
                $filtros['faixa_valor_min'] = $faixa['min'];
            }
            if ($faixa['max']) {
                $filtros['faixa_valor_max'] = $faixa['max'];
            }
        }
        unset($filtros['faixa_valor']);

        $dados = DashboardService::obterDadosCacheados(array_filter($filtros) ?: null);

        $secretarias = Secretaria::orderBy('nome')->get(['id', 'nome', 'sigla']);
        $tiposContrato = TipoContrato::cases();
        $modalidades = ModalidadeContratacao::cases();
        $niveisRisco = NivelRisco::cases();

        $fontesRecurso = Contrato::where('status', StatusContrato::Vigente->value)
            ->whereNotNull('fonte_recurso')
            ->where('fonte_recurso', '!=', '')
            ->distinct()
            ->orderBy('fonte_recurso')
            ->pluck('fonte_recurso');

        $isControlador = $request->user()->hasRole('controladoria')
            || $request->user()->hasRole('administrador_geral');

        return view('tenant.dashboard.index', compact(
            'dados',
            'filtros',
            'secretarias',
            'tiposContrato',
            'modalidades',
            'niveisRisco',
            'fontesRecurso',
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
