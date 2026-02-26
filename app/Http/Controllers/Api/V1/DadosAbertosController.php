<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DatasetDadosAbertos;
use App\Enums\FormatoDadosAbertos;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExportacaoDadosAbertosResource;
use App\Models\Contrato;
use App\Models\ExportacaoDadosAbertos;
use App\Services\DadosAbertosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class DadosAbertosController extends Controller
{
    public function catalogo(): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $datasets = DadosAbertosService::catalogo();

        return response()->json(['data' => $datasets]);
    }

    public function contratos(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $filtros = $request->only(['ano', 'status', 'secretaria_id', 'modalidade', 'per_page']);

        $resultado = DadosAbertosService::exportarContratosExpandido($filtros);

        return response()->json($resultado);
    }

    public function fornecedores(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $filtros = $request->only(['busca', 'per_page']);

        $resultado = DadosAbertosService::exportarFornecedores($filtros);

        return response()->json($resultado);
    }

    public function licitacoes(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $filtros = $request->only(['ano', 'modalidade', 'per_page']);

        $resultado = DadosAbertosService::exportarLicitacoes($filtros);

        return response()->json($resultado);
    }

    public function exportar(Request $request): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('viewAny', Contrato::class);

        $request->validate([
            'dataset' => ['required', new Enum(DatasetDadosAbertos::class)],
            'formato' => ['required', new Enum(FormatoDadosAbertos::class)],
        ]);

        $dataset = DatasetDadosAbertos::from($request->input('dataset'));
        $formato = FormatoDadosAbertos::from($request->input('formato'));
        $filtros = $request->except(['dataset', 'formato']);

        $dados = match ($dataset) {
            DatasetDadosAbertos::Contratos => DadosAbertosService::exportarContratosExpandido($filtros)['data'],
            DatasetDadosAbertos::Fornecedores => DadosAbertosService::exportarFornecedores($filtros)['data'],
            DatasetDadosAbertos::Licitacoes => DadosAbertosService::exportarLicitacoes($filtros)['data'],
        };

        DadosAbertosService::registrarExportacao(
            $dataset,
            $formato,
            ! empty($filtros) ? $filtros : null,
            count($dados),
            auth()->id(),
            $request->ip(),
        );

        if ($formato === FormatoDadosAbertos::Xml) {
            $xml = DadosAbertosService::gerarXml($dataset->value, $dados);

            return response($xml, 200, [
                'Content-Type' => $formato->contentType(),
                'Content-Disposition' => 'attachment; filename="' . $dataset->value . '_' . now()->format('Y-m-d') . '.' . $formato->extensao() . '"',
            ]);
        }

        if ($formato === FormatoDadosAbertos::Csv) {
            $headers = [
                'Content-Type' => $formato->contentType(),
                'Content-Disposition' => 'attachment; filename="' . $dataset->value . '_' . now()->format('Y-m-d') . '.' . $formato->extensao() . '"',
            ];

            return response()->stream(function () use ($dados) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                if (! empty($dados)) {
                    fputcsv($handle, array_keys($dados[0]), ';');
                    foreach ($dados as $row) {
                        fputcsv($handle, array_values($row), ';');
                    }
                }

                fclose($handle);
            }, 200, $headers);
        }

        // JSON (default)
        return response()->json([
            'metadata' => [
                'dataset' => $dataset->value,
                'formato' => $formato->value,
                'total' => count($dados),
                'gerado_em' => now()->toIso8601String(),
            ],
            'dados' => $dados,
        ]);
    }

    public function estatisticas(): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $stats = DadosAbertosService::estatisticas();

        return response()->json(['data' => $stats]);
    }

    public function historico(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $exportacoes = ExportacaoDadosAbertos::with('solicitante')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return ExportacaoDadosAbertosResource::collection($exportacoes)
            ->response();
    }
}
