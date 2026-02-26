<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\FormatoExportacaoTce;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExportacaoTceResource;
use App\Models\Contrato;
use App\Models\ExportacaoTce;
use App\Services\TceExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class TceController extends Controller
{
    public function dados(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $filtros = $request->only(['status', 'secretaria_id', 'nivel_risco']);
        $dados = TceExportService::coletarDados($filtros);

        return response()->json([
            'municipio' => $dados['municipio'],
            'data_geracao' => $dados['data_geracao'],
            'resumo' => $dados['resumo'],
            'total_pendencias' => $dados['total_pendencias'],
            'contratos' => $dados['contratos'],
        ]);
    }

    public function validar(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $filtros = $request->only(['status', 'secretaria_id', 'nivel_risco']);
        $dados = TceExportService::coletarDados($filtros);

        return response()->json([
            'total_contratos' => $dados['resumo']['total_monitorados'],
            'total_pendencias' => $dados['total_pendencias'],
            'pendencias' => $dados['pendencias'],
        ]);
    }

    public function exportar(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $request->validate([
            'formato' => ['required', new Enum(FormatoExportacaoTce::class)],
            'filtros' => ['nullable', 'array'],
            'filtros.status' => ['nullable', 'string'],
            'filtros.secretaria_id' => ['nullable', 'integer'],
            'filtros.nivel_risco' => ['nullable', 'string'],
        ]);

        $formato = FormatoExportacaoTce::from($request->input('formato'));
        $filtros = $request->input('filtros', []);
        $dados = TceExportService::coletarDados($filtros);

        // Registrar exportação no histórico
        $exportacao = TceExportService::registrarExportacao(
            $formato,
            $filtros ?: null,
            $dados['resumo']['total_monitorados'],
            $dados['total_pendencias'],
        );

        // Gerar conteúdo conforme formato
        $conteudo = match ($formato) {
            FormatoExportacaoTce::Xml => TceExportService::gerarXml($dados),
            FormatoExportacaoTce::Csv => null,
            FormatoExportacaoTce::Excel => null,
            FormatoExportacaoTce::Pdf => null,
        };

        if ($formato === FormatoExportacaoTce::Xml) {
            return response()->json([
                'exportacao' => new ExportacaoTceResource($exportacao->load('geradoPor')),
                'conteudo' => $conteudo,
                'content_type' => $formato->contentType(),
            ]);
        }

        // CSV e Excel retornam informação de que a exportação foi registrada
        // (download feito via endpoints web ou direto via service)
        return response()->json([
            'exportacao' => new ExportacaoTceResource($exportacao->load('geradoPor')),
            'message' => 'Exportação registrada com sucesso. Use o endpoint de download para obter o arquivo.',
        ]);
    }

    public function historico(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrato::class);

        $perPage = min((int) $request->input('per_page', 15), 100);

        $exportacoes = ExportacaoTce::with('geradoPor')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return ExportacaoTceResource::collection($exportacoes)->response();
    }
}
