<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\AlertasExport;
use App\Exports\ContratosExport;
use App\Exports\FornecedoresExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\GerarRelatorioAuditoriaRequest;
use App\Models\Contrato;
use App\Models\User;
use App\Services\RelatorioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatoriosController extends Controller
{
    /**
     * Pagina central de relatorios com cards agrupados por categoria.
     */
    public function index(): View
    {
        $contratos = Contrato::select('id', 'numero', 'ano', 'objeto')
            ->orderBy('numero')
            ->get();

        return view('tenant.relatorios.index', compact('contratos'));
    }

    /**
     * RN-133: Gerar PDF de documentos por contrato (TCE).
     */
    public function documentosContratoPdf(Contrato $contrato)
    {
        $dados = RelatorioService::dadosDocumentosContrato($contrato);

        $pdf = Pdf::loadView('tenant.relatorios.pdf.documentos-contrato', compact('dados'))
            ->setPaper('a4', 'portrait');

        $nomeArquivo = 'relatorio-documentos-contrato-' . str_replace('/', '-', $contrato->numero) . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * RN-222: Formulario de filtros para o relatorio de auditoria.
     */
    public function auditoriaFiltros(): View
    {
        $usuarios = User::select('id', 'nome')->orderBy('nome')->get();

        return view('tenant.relatorios.auditoria-filtros', compact('usuarios'));
    }

    /**
     * RN-222: Gerar PDF do relatorio de auditoria filtrado.
     */
    public function auditoriaPdf(GerarRelatorioAuditoriaRequest $request)
    {
        $dados = RelatorioService::dadosAuditoria($request->validated());

        $pdf = Pdf::loadView('tenant.relatorios.pdf.auditoria', compact('dados'))
            ->setPaper('a4', 'landscape');

        $nomeArquivo = 'relatorio-auditoria-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * RN-222: Gerar CSV do relatorio de auditoria filtrado.
     */
    public function auditoriaCsv(GerarRelatorioAuditoriaRequest $request): StreamedResponse
    {
        $registros = RelatorioService::dadosAuditoriaCSV($request->validated());

        $nomeArquivo = 'relatorio-auditoria-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($registros) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para Excel reconhecer acentos
            fwrite($handle, "\xEF\xBB\xBF");

            // Header
            if ($registros->isNotEmpty()) {
                fputcsv($handle, array_keys($registros->first()), ';');
            }

            // Dados
            foreach ($registros as $registro) {
                fputcsv($handle, $registro, ';');
            }

            fclose($handle);
        }, $nomeArquivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * RN-225: Gerar PDF do relatorio de conformidade documental.
     */
    public function conformidadeDocumentalPdf()
    {
        $dados = RelatorioService::dadosConformidadeDocumental();

        $pdf = Pdf::loadView('tenant.relatorios.pdf.conformidade-documental', compact('dados'))
            ->setPaper('a4', 'landscape');

        $nomeArquivo = 'relatorio-conformidade-documental-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * Exportar listagem de contratos em Excel.
     */
    public function contratosExcel(Request $request): BinaryFileResponse
    {
        $filtros = $request->only(['status', 'secretaria_id', 'modalidade', 'nivel_risco']);

        $nomeArquivo = 'contratos-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new ContratosExport($filtros), $nomeArquivo);
    }

    /**
     * Exportar listagem de alertas em Excel.
     */
    public function alertasExcel(Request $request): BinaryFileResponse
    {
        $filtros = $request->only(['status', 'prioridade', 'tipo_evento', 'secretaria_id', 'tipo_contrato']);

        $nomeArquivo = 'alertas-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new AlertasExport($filtros), $nomeArquivo);
    }

    /**
     * Exportar listagem de fornecedores em Excel.
     */
    public function fornecedoresExcel(): BinaryFileResponse
    {
        $nomeArquivo = 'fornecedores-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new FornecedoresExport(), $nomeArquivo);
    }
}
