<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\PainelRiscoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class PainelRiscoController extends Controller
{
    public function index(): View
    {
        $indicadores = PainelRiscoService::indicadores();
        $ranking = PainelRiscoService::rankingRisco();
        $mapaSecretarias = PainelRiscoService::mapaRiscoPorSecretaria();

        return view('tenant.painel-risco.index', compact(
            'indicadores',
            'ranking',
            'mapaSecretarias',
        ));
    }

    public function exportarRelatorioTCE()
    {
        $dados = PainelRiscoService::dadosRelatorioTCE();

        $pdf = Pdf::loadView('tenant.painel-risco.relatorio-tce', compact('dados'))
            ->setPaper('a4', 'landscape');

        $nomeArquivo = 'relatorio-risco-tce-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($nomeArquivo);
    }
}
