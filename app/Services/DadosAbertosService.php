<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Secretaria;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DadosAbertosService
{
    /**
     * Exporta contratos publicos em formato JSON (RN-413).
     */
    public static function exportarContratosJson(array $filtros = []): JsonResponse
    {
        $contratos = self::queryContratosPublicos($filtros)->get();

        $dados = $contratos->map(function ($contrato) {
            return [
                'numero' => $contrato->numero,
                'ano' => $contrato->ano,
                'objeto' => $contrato->objeto,
                'tipo' => $contrato->tipo?->value,
                'status' => $contrato->status?->value,
                'modalidade' => $contrato->modalidade_contratacao?->value,
                'fornecedor_razao_social' => $contrato->fornecedor?->razao_social,
                'fornecedor_cnpj' => $contrato->fornecedor?->cnpj,
                'secretaria' => $contrato->secretaria?->nome,
                'valor_global' => (float) $contrato->valor_global,
                'valor_mensal' => (float) $contrato->valor_mensal,
                'data_inicio' => $contrato->data_inicio?->format('Y-m-d'),
                'data_fim' => $contrato->data_fim?->format('Y-m-d'),
                'data_assinatura' => $contrato->data_assinatura?->format('Y-m-d'),
                'data_publicacao' => $contrato->data_publicacao?->format('Y-m-d'),
                'numero_processo' => $contrato->numero_processo,
                'fonte_recurso' => $contrato->fonte_recurso,
            ];
        });

        return response()->json([
            'metadata' => [
                'total' => $dados->count(),
                'gerado_em' => now()->toIso8601String(),
                'formato' => 'JSON',
            ],
            'dados' => $dados,
        ]);
    }

    /**
     * Exporta contratos publicos em formato CSV (RN-413).
     */
    public static function exportarContratosCsv(array $filtros = []): StreamedResponse
    {
        $contratos = self::queryContratosPublicos($filtros)->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="contratos_publicos_' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($contratos) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Numero',
                'Ano',
                'Objeto',
                'Tipo',
                'Status',
                'Modalidade',
                'Fornecedor',
                'CNPJ Fornecedor',
                'Secretaria',
                'Valor Global',
                'Valor Mensal',
                'Data Inicio',
                'Data Fim',
                'Data Assinatura',
                'Data Publicacao',
                'Numero Processo',
                'Fonte Recurso',
            ], ';');

            foreach ($contratos as $contrato) {
                fputcsv($handle, [
                    $contrato->numero,
                    $contrato->ano,
                    $contrato->objeto,
                    $contrato->tipo?->value,
                    $contrato->status?->value,
                    $contrato->modalidade_contratacao?->value,
                    $contrato->fornecedor?->razao_social,
                    $contrato->fornecedor?->cnpj,
                    $contrato->secretaria?->nome,
                    $contrato->valor_global,
                    $contrato->valor_mensal,
                    $contrato->data_inicio?->format('d/m/Y'),
                    $contrato->data_fim?->format('d/m/Y'),
                    $contrato->data_assinatura?->format('d/m/Y'),
                    $contrato->data_publicacao?->format('d/m/Y'),
                    $contrato->numero_processo,
                    $contrato->fonte_recurso,
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Retorna indicadores agregados para o portal publico (RN-410).
     */
    public static function obterIndicadoresPublicos(): array
    {
        $query = Contrato::withoutGlobalScopes()->visivelNoPortal();

        $totalContratos = (clone $query)->count();
        $valorTotal = (clone $query)->sum('valor_global');
        $contratosVigentes = (clone $query)->where('status', 'vigente')->count();

        $porSecretaria = Contrato::withoutGlobalScopes()
            ->visivelNoPortal()
            ->join('secretarias', 'contratos.secretaria_id', '=', 'secretarias.id')
            ->select('secretarias.nome', DB::raw('COUNT(*) as total'))
            ->groupBy('secretarias.nome')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'total_contratos' => $totalContratos,
            'valor_total' => $valorTotal,
            'contratos_vigentes' => $contratosVigentes,
            'por_secretaria' => $porSecretaria,
        ];
    }

    /**
     * Query base para contratos publicos visiveis no portal.
     */
    private static function queryContratosPublicos(array $filtros = [])
    {
        $query = Contrato::withoutGlobalScopes()
            ->visivelNoPortal()
            ->with(['fornecedor', 'secretaria']);

        if (! empty($filtros['ano'])) {
            $query->where('ano', $filtros['ano']);
        }

        if (! empty($filtros['secretaria'])) {
            $query->where('secretaria_id', $filtros['secretaria']);
        }

        if (! empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        return $query->orderByDesc('created_at');
    }
}
