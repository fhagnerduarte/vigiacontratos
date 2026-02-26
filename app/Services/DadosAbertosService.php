<?php

namespace App\Services;

use App\Enums\DatasetDadosAbertos;
use App\Enums\FormatoDadosAbertos;
use App\Models\Contrato;
use App\Models\ExportacaoDadosAbertos;
use App\Models\Fornecedor;
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

        $totalSecretarias = Contrato::withoutGlobalScopes()
            ->visivelNoPortal()
            ->distinct('secretaria_id')
            ->count('secretaria_id');

        return [
            'total_contratos' => $totalContratos,
            'valor_total' => $valorTotal,
            'contratos_vigentes' => $contratosVigentes,
            'total_secretarias' => $totalSecretarias,
            'por_secretaria' => $porSecretaria,
        ];
    }

    /**
     * Exporta contratos publicos com campos expandidos (30 campos) para API.
     */
    public static function exportarContratosExpandido(array $filtros = []): array
    {
        $query = self::queryContratosPublicos($filtros)
            ->with(['fiscalAtual', 'aditivos']);

        if (! empty($filtros['modalidade'])) {
            $query->where('modalidade_contratacao', $filtros['modalidade']);
        }

        $contratos = $query->paginate($filtros['per_page'] ?? 20);

        $dados = collect($contratos->items())->map(function ($contrato) {
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
                'fundamento_legal' => $contrato->fundamento_legal,
                'regime_execucao' => $contrato->regime_execucao?->value,
                'categoria_servico' => $contrato->categoria_servico?->value,
                'dotacao_orcamentaria' => $contrato->dotacao_orcamentaria,
                'nivel_risco' => $contrato->nivel_risco?->value,
                'score_risco' => $contrato->score_risco,
                'percentual_executado' => $contrato->percentual_executado ? (float) $contrato->percentual_executado : null,
                'valor_empenhado' => $contrato->valor_empenhado ? (float) $contrato->valor_empenhado : null,
                'saldo_contratual' => $contrato->saldo_contratual ? (float) $contrato->saldo_contratual : null,
                'veiculo_publicacao' => $contrato->veiculo_publicacao,
                'fiscal_titular' => $contrato->fiscalAtual?->nome,
                'qtd_aditivos' => $contrato->aditivos->count(),
                'valor_total_aditivos' => (float) $contrato->aditivos->sum('valor_acrescimo'),
            ];
        });

        return [
            'data' => $dados->values()->toArray(),
            'meta' => [
                'total' => $contratos->total(),
                'per_page' => $contratos->perPage(),
                'current_page' => $contratos->currentPage(),
                'last_page' => $contratos->lastPage(),
                'gerado_em' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Exporta fornecedores publicos com totais agregados.
     */
    public static function exportarFornecedores(array $filtros = []): array
    {
        $query = Fornecedor::query();

        if (! empty($filtros['busca'])) {
            $busca = $filtros['busca'];
            $query->where(function ($q) use ($busca) {
                $q->where('razao_social', 'like', "%{$busca}%")
                    ->orWhere('cnpj', 'like', "%{$busca}%");
            });
        }

        $fornecedores = $query->orderBy('razao_social')
            ->paginate($filtros['per_page'] ?? 20);

        $dados = collect($fornecedores->items())->map(function ($fornecedor) {
            $contratos = Contrato::withoutGlobalScopes()
                ->visivelNoPortal()
                ->where('fornecedor_id', $fornecedor->id);

            return [
                'cnpj' => $fornecedor->cnpj,
                'razao_social' => $fornecedor->razao_social,
                'nome_fantasia' => $fornecedor->nome_fantasia,
                'cidade' => $fornecedor->cidade,
                'uf' => $fornecedor->uf,
                'total_contratos' => (clone $contratos)->count(),
                'valor_total_contratado' => (float) (clone $contratos)->sum('valor_global'),
                'contratos_vigentes' => (clone $contratos)->where('status', 'vigente')->count(),
            ];
        });

        return [
            'data' => $dados->values()->toArray(),
            'meta' => [
                'total' => $fornecedores->total(),
                'per_page' => $fornecedores->perPage(),
                'current_page' => $fornecedores->currentPage(),
                'last_page' => $fornecedores->lastPage(),
                'gerado_em' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Exporta dados de licitacoes (processos) publicos.
     */
    public static function exportarLicitacoes(array $filtros = []): array
    {
        $query = Contrato::withoutGlobalScopes()
            ->visivelNoPortal()
            ->whereNotNull('numero_processo')
            ->with(['fornecedor', 'secretaria']);

        if (! empty($filtros['modalidade'])) {
            $query->where('modalidade_contratacao', $filtros['modalidade']);
        }

        if (! empty($filtros['ano'])) {
            $query->where('ano', $filtros['ano']);
        }

        $contratos = $query->orderByDesc('created_at')
            ->paginate($filtros['per_page'] ?? 20);

        $dados = collect($contratos->items())->map(function ($contrato) {
            return [
                'numero_processo' => $contrato->numero_processo,
                'modalidade' => $contrato->modalidade_contratacao?->value,
                'objeto' => $contrato->objeto,
                'valor_estimado' => (float) $contrato->valor_global,
                'fornecedor_vencedor' => $contrato->fornecedor?->razao_social,
                'fornecedor_cnpj' => $contrato->fornecedor?->cnpj,
                'status' => $contrato->status?->value,
                'numero_contrato' => $contrato->numero,
                'ano' => $contrato->ano,
                'secretaria' => $contrato->secretaria?->nome,
                'data_assinatura' => $contrato->data_assinatura?->format('Y-m-d'),
                'data_publicacao' => $contrato->data_publicacao?->format('Y-m-d'),
                'fundamento_legal' => $contrato->fundamento_legal,
            ];
        });

        return [
            'data' => $dados->values()->toArray(),
            'meta' => [
                'total' => $contratos->total(),
                'per_page' => $contratos->perPage(),
                'current_page' => $contratos->currentPage(),
                'last_page' => $contratos->lastPage(),
                'gerado_em' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Gera XML no padrao dados abertos para o dataset informado.
     */
    public static function gerarXml(string $dataset, array $dados): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><DadosAbertos/>');
        $xml->addAttribute('versao', '1.0');
        $xml->addAttribute('dataset', $dataset);

        $cabecalho = $xml->addChild('Cabecalho');
        $cabecalho->addChild('GeradoEm', now()->toIso8601String());
        $cabecalho->addChild('TotalRegistros', (string) count($dados));
        $cabecalho->addChild('Dataset', $dataset);

        $registros = $xml->addChild('Registros');

        foreach ($dados as $item) {
            $registro = $registros->addChild('Registro');
            foreach ($item as $campo => $valor) {
                if ($valor !== null) {
                    $tagName = ucfirst(str_replace('_', '', ucwords((string) $campo, '_')));
                    $registro->addChild($tagName, htmlspecialchars((string) $valor));
                }
            }
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Retorna catalogo de datasets disponiveis com metadados.
     */
    public static function catalogo(): array
    {
        $datasets = [];

        foreach (DatasetDadosAbertos::cases() as $dataset) {
            $totalRegistros = match ($dataset) {
                DatasetDadosAbertos::Contratos => Contrato::withoutGlobalScopes()->visivelNoPortal()->count(),
                DatasetDadosAbertos::Fornecedores => Fornecedor::count(),
                DatasetDadosAbertos::Licitacoes => Contrato::withoutGlobalScopes()->visivelNoPortal()->whereNotNull('numero_processo')->count(),
            };

            $campos = match ($dataset) {
                DatasetDadosAbertos::Contratos => [
                    'numero', 'ano', 'objeto', 'tipo', 'status', 'modalidade',
                    'fornecedor_razao_social', 'fornecedor_cnpj', 'secretaria',
                    'valor_global', 'valor_mensal', 'data_inicio', 'data_fim',
                    'data_assinatura', 'data_publicacao', 'numero_processo',
                    'fonte_recurso', 'fundamento_legal', 'regime_execucao',
                    'categoria_servico', 'dotacao_orcamentaria', 'nivel_risco',
                    'score_risco', 'percentual_executado', 'valor_empenhado',
                    'saldo_contratual', 'veiculo_publicacao', 'fiscal_titular',
                    'qtd_aditivos', 'valor_total_aditivos',
                ],
                DatasetDadosAbertos::Fornecedores => [
                    'cnpj', 'razao_social', 'nome_fantasia', 'cidade', 'uf',
                    'total_contratos', 'valor_total_contratado', 'contratos_vigentes',
                ],
                DatasetDadosAbertos::Licitacoes => [
                    'numero_processo', 'modalidade', 'objeto', 'valor_estimado',
                    'fornecedor_vencedor', 'fornecedor_cnpj', 'status',
                    'numero_contrato', 'ano', 'secretaria', 'data_assinatura',
                    'data_publicacao', 'fundamento_legal',
                ],
            };

            $filtrosDisponiveis = match ($dataset) {
                DatasetDadosAbertos::Contratos => ['ano', 'status', 'secretaria_id', 'modalidade'],
                DatasetDadosAbertos::Fornecedores => ['busca'],
                DatasetDadosAbertos::Licitacoes => ['ano', 'modalidade'],
            };

            $datasets[] = [
                'dataset' => $dataset->value,
                'nome' => $dataset->label(),
                'descricao' => $dataset->descricao(),
                'campos' => $campos,
                'filtros_disponiveis' => $filtrosDisponiveis,
                'formatos' => collect(FormatoDadosAbertos::cases())->map(fn ($f) => $f->value)->toArray(),
                'total_registros' => $totalRegistros,
            ];
        }

        return $datasets;
    }

    /**
     * Registra exportacao no historico para auditoria.
     */
    public static function registrarExportacao(
        DatasetDadosAbertos $dataset,
        FormatoDadosAbertos $formato,
        ?array $filtros,
        int $totalRegistros,
        ?int $userId,
        ?string $ip,
    ): ExportacaoDadosAbertos {
        $exportacao = ExportacaoDadosAbertos::create([
            'dataset' => $dataset->value,
            'formato' => $formato->value,
            'filtros' => $filtros,
            'total_registros' => $totalRegistros,
            'solicitado_por' => $userId,
            'ip_solicitante' => $ip,
        ]);

        WebhookService::disparar('dados_abertos.exportacao', [
            'dataset' => $dataset->value,
            'formato' => $formato->value,
            'total_registros' => $totalRegistros,
        ]);

        return $exportacao;
    }

    /**
     * Retorna estatisticas de uso dos dados abertos.
     */
    public static function estatisticas(): array
    {
        $total = ExportacaoDadosAbertos::count();

        $porDataset = ExportacaoDadosAbertos::select('dataset', DB::raw('COUNT(*) as total'))
            ->groupBy('dataset')
            ->pluck('total', 'dataset')
            ->toArray();

        $porFormato = ExportacaoDadosAbertos::select('formato', DB::raw('COUNT(*) as total'))
            ->groupBy('formato')
            ->pluck('total', 'formato')
            ->toArray();

        $ultimaExportacao = ExportacaoDadosAbertos::orderByDesc('created_at')
            ->first();

        return [
            'total_exportacoes' => $total,
            'por_dataset' => $porDataset,
            'por_formato' => $porFormato,
            'ultima_exportacao' => $ultimaExportacao?->created_at?->toIso8601String(),
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

        if (! empty($filtros['secretaria']) || ! empty($filtros['secretaria_id'])) {
            $query->where('secretaria_id', $filtros['secretaria'] ?? $filtros['secretaria_id']);
        }

        if (! empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        return $query->orderByDesc('created_at');
    }
}
