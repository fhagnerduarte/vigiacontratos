<?php

namespace App\Services;

use App\Enums\CategoriaContrato;
use App\Enums\NivelRisco;
use App\Enums\StatusContrato;
use App\Models\Contrato;
use App\Models\DashboardAgregado;
use App\Models\Fornecedor;
use App\Models\HistoricoAlteracao;
use App\Models\Secretaria;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Indicadores financeiros: total ativos, valor contratado, executado, saldo, ticket medio (RN-058 a RN-061).
     */
    public static function indicadoresFinanceiros(?int $secretariaId = null): array
    {
        $query = Contrato::where('status', StatusContrato::Vigente->value);
        if ($secretariaId) {
            $query->where('secretaria_id', $secretariaId);
        }

        $dados = $query->selectRaw('
            COUNT(*) as total,
            COALESCE(SUM(valor_global), 0) as valor_contratado,
            COALESCE(SUM(valor_global * percentual_executado / 100), 0) as valor_executado
        ')->first();

        $totalAtivos = (int) $dados->total;
        $valorContratado = (float) $dados->valor_contratado;
        $valorExecutado = (float) $dados->valor_executado;
        $saldo = $valorContratado - $valorExecutado;
        $ticketMedio = $totalAtivos > 0 ? $valorContratado / $totalAtivos : 0;

        return [
            'total_contratos_ativos' => $totalAtivos,
            'valor_total_contratado' => round($valorContratado, 2),
            'valor_total_executado' => round($valorExecutado, 2),
            'saldo_remanescente' => round($saldo, 2),
            'ticket_medio' => round($ticketMedio, 2),
        ];
    }

    /**
     * Mapa de risco: distribuicao baixo/medio/alto (RN-062 a RN-065).
     */
    public static function mapaRisco(?int $secretariaId = null): array
    {
        $query = Contrato::where('status', StatusContrato::Vigente->value);
        if ($secretariaId) {
            $query->where('secretaria_id', $secretariaId);
        }

        $distribuicao = $query->selectRaw("
            SUM(CASE WHEN nivel_risco = 'baixo' THEN 1 ELSE 0 END) as baixo,
            SUM(CASE WHEN nivel_risco = 'medio' THEN 1 ELSE 0 END) as medio,
            SUM(CASE WHEN nivel_risco = 'alto' THEN 1 ELSE 0 END) as alto,
            COUNT(*) as total
        ")->first();

        $total = (int) $distribuicao->total;
        $pctConformes = $total > 0
            ? round(((int) $distribuicao->baixo / $total) * 100, 1)
            : 100;

        return [
            'baixo' => (int) $distribuicao->baixo,
            'medio' => (int) $distribuicao->medio,
            'alto' => (int) $distribuicao->alto,
            'total' => $total,
            'pct_conformes' => $pctConformes,
        ];
    }

    /**
     * Janelas de vencimento: 0-30d, 31-60d, 61-90d, 91-120d, >120d (RN-066/067).
     */
    public static function vencimentosPorJanela(?int $secretariaId = null): array
    {
        $hoje = now()->startOfDay();

        $query = Contrato::where('status', StatusContrato::Vigente->value)
            ->whereNotNull('data_fim')
            ->where('data_fim', '>=', $hoje);

        if ($secretariaId) {
            $query->where('secretaria_id', $secretariaId);
        }

        $janelas = $query->selectRaw("
            SUM(CASE WHEN DATEDIFF(data_fim, ?) BETWEEN 0 AND 30 THEN 1 ELSE 0 END) as j_0_30,
            SUM(CASE WHEN DATEDIFF(data_fim, ?) BETWEEN 31 AND 60 THEN 1 ELSE 0 END) as j_31_60,
            SUM(CASE WHEN DATEDIFF(data_fim, ?) BETWEEN 61 AND 90 THEN 1 ELSE 0 END) as j_61_90,
            SUM(CASE WHEN DATEDIFF(data_fim, ?) BETWEEN 91 AND 120 THEN 1 ELSE 0 END) as j_91_120,
            SUM(CASE WHEN DATEDIFF(data_fim, ?) > 120 THEN 1 ELSE 0 END) as j_120p
        ", [$hoje, $hoje, $hoje, $hoje, $hoje])->first();

        return [
            '0_30d' => (int) $janelas->j_0_30,
            '31_60d' => (int) $janelas->j_31_60,
            '61_90d' => (int) $janelas->j_61_90,
            '91_120d' => (int) $janelas->j_91_120,
            '120p' => (int) $janelas->j_120p,
        ];
    }

    /**
     * Ranking de secretarias: total contratos, valor, % em risco, vencimentos proximos (RN-068/069).
     */
    public static function rankingSecretarias(): array
    {
        $secretarias = Secretaria::withCount([
            'contratos as total_contratos' => fn ($q) => $q->where('status', StatusContrato::Vigente->value),
            'contratos as contratos_risco' => fn ($q) => $q->where('status', StatusContrato::Vigente->value)
                ->whereIn('nivel_risco', [NivelRisco::Medio->value, NivelRisco::Alto->value]),
            'contratos as vencendo_proximos' => fn ($q) => $q->where('status', StatusContrato::Vigente->value)
                ->whereNotNull('data_fim')
                ->whereBetween('data_fim', [now()->startOfDay(), now()->addDays(60)]),
        ])
            ->withSum(
                ['contratos as valor_total' => fn ($q) => $q->where('status', StatusContrato::Vigente->value)],
                'valor_global'
            )
            ->having('total_contratos', '>', 0)
            ->orderByDesc('valor_total')
            ->get()
            ->map(function ($sec) {
                $sec->pct_risco = $sec->total_contratos > 0
                    ? round(($sec->contratos_risco / $sec->total_contratos) * 100, 1)
                    : 0;

                return $sec;
            });

        return $secretarias->toArray();
    }

    /**
     * Contratos essenciais vencendo em 60 dias (RN-070 a RN-072).
     */
    public static function contratosEssenciais(): array
    {
        return Contrato::where('status', StatusContrato::Vigente->value)
            ->where('categoria', CategoriaContrato::Essencial->value)
            ->whereNotNull('data_fim')
            ->where('data_fim', '<=', now()->addDays(60))
            ->where('data_fim', '>=', now()->startOfDay())
            ->with(['secretaria:id,nome,sigla', 'fornecedor:id,razao_social'])
            ->orderBy('data_fim')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'numero' => $c->numero . '/' . $c->ano,
                'objeto' => $c->objeto,
                'secretaria' => $c->secretaria?->sigla ?? $c->secretaria?->nome,
                'categoria_servico' => $c->categoria_servico?->label(),
                'fornecedor' => $c->fornecedor?->razao_social,
                'data_fim' => $c->data_fim->format('d/m/Y'),
                'dias_restantes' => $c->dias_para_vencimento,
            ])
            ->toArray();
    }

    /**
     * Score de Gestao 0-100 (RN-075 a RN-077).
     * Penalidades: %vencidos × 3, %sem_fiscal × 2, %vencendo_30d × 1.
     */
    public static function scoreGestao(): array
    {
        $totalAtivos = Contrato::where('status', StatusContrato::Vigente->value)->count();
        $totalVencidos = Contrato::where('status', StatusContrato::Vencido->value)->count();
        $totalAtivosEVencidos = $totalAtivos + $totalVencidos;

        if ($totalAtivosEVencidos === 0) {
            return [
                'score' => 100,
                'classificacao' => 'Excelente',
                'cor' => 'success',
            ];
        }

        $semFiscal = Contrato::where('status', StatusContrato::Vigente->value)
            ->whereDoesntHave('fiscalAtual')
            ->count();

        $vencendo30d = Contrato::where('status', StatusContrato::Vigente->value)
            ->whereNotNull('data_fim')
            ->whereBetween('data_fim', [now()->startOfDay(), now()->addDays(30)])
            ->count();

        $pctVencidos = ($totalVencidos / $totalAtivosEVencidos) * 100;
        $pctSemFiscal = $totalAtivos > 0 ? ($semFiscal / $totalAtivos) * 100 : 0;
        $pctVencendo30 = $totalAtivos > 0 ? ($vencendo30d / $totalAtivos) * 100 : 0;

        $score = 100 - ($pctVencidos * 3) - ($pctSemFiscal * 2) - ($pctVencendo30 * 1);
        $score = max(0, min(100, (int) round($score)));

        [$classificacao, $cor] = match (true) {
            $score >= 80 => ['Excelente', 'success'],
            $score >= 60 => ['Controlada', 'info'],
            $score >= 40 => ['Atencao', 'warning'],
            default => ['Critica', 'danger'],
        };

        return [
            'score' => $score,
            'classificacao' => $classificacao,
            'cor' => $cor,
        ];
    }

    /**
     * Tendencias mensais: ultimos 12 meses — contratos ativos, risco medio, volume financeiro (RN-078).
     */
    public static function tendenciasMensais(): array
    {
        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $data = now()->subMonths($i);
            $mesInicio = $data->copy()->startOfMonth();
            $mesFim = $data->copy()->endOfMonth();

            $contratosAtivos = Contrato::where('data_inicio', '<=', $mesFim)
                ->where(function ($q) use ($mesInicio) {
                    $q->whereNull('data_fim')
                        ->orWhere('data_fim', '>=', $mesInicio);
                })
                ->whereNotIn('status', [StatusContrato::Cancelado->value])
                ->count();

            $volumeFinanceiro = (float) Contrato::where('data_inicio', '<=', $mesFim)
                ->where(function ($q) use ($mesInicio) {
                    $q->whereNull('data_fim')
                        ->orWhere('data_fim', '>=', $mesInicio);
                })
                ->whereNotIn('status', [StatusContrato::Cancelado->value])
                ->sum('valor_global');

            $riscoMedio = (float) Contrato::where('data_inicio', '<=', $mesFim)
                ->where(function ($q) use ($mesInicio) {
                    $q->whereNull('data_fim')
                        ->orWhere('data_fim', '>=', $mesInicio);
                })
                ->whereNotIn('status', [StatusContrato::Cancelado->value])
                ->avg('score_risco');

            $meses[] = [
                'label' => $data->translatedFormat('M/Y'),
                'contratos_ativos' => $contratosAtivos,
                'volume_financeiro' => round($volumeFinanceiro, 2),
                'risco_medio' => round($riscoMedio, 1),
            ];
        }

        return $meses;
    }

    /**
     * Top 10 fornecedores por volume financeiro (RN-079/080).
     */
    public static function rankingFornecedores(): array
    {
        $fornecedores = Fornecedor::select('fornecedores.*')
            ->withCount([
                'contratos as total_contratos' => fn ($q) => $q->whereIn('status', [
                    StatusContrato::Vigente->value,
                    StatusContrato::Encerrado->value,
                ]),
            ])
            ->withSum(
                ['contratos as volume_financeiro' => fn ($q) => $q->whereIn('status', [
                    StatusContrato::Vigente->value,
                    StatusContrato::Encerrado->value,
                ])],
                'valor_global'
            )
            ->having('total_contratos', '>', 0)
            ->orderByDesc('volume_financeiro')
            ->limit(10)
            ->get()
            ->map(function ($f) {
                // Indice de aditivos: total_aditivos / total_contratos (RN-080)
                $totalAditivos = DB::connection('tenant')
                    ->table('aditivos')
                    ->whereIn('contrato_id', function ($q) use ($f) {
                        $q->select('id')->from('contratos')
                            ->where('fornecedor_id', $f->id)
                            ->whereIn('status', [StatusContrato::Vigente->value, StatusContrato::Encerrado->value]);
                    })
                    ->whereNull('deleted_at')
                    ->count();

                $indice = $f->total_contratos > 0 ? round($totalAditivos / $f->total_contratos, 2) : 0;

                [$classificacaoIndice, $corIndice] = match (true) {
                    $indice > 1.0 => ['Alto', 'danger'],
                    $indice > 0.5 => ['Acima da media', 'warning'],
                    default => ['Normal', 'success'],
                };

                return [
                    'id' => $f->id,
                    'razao_social' => $f->razao_social,
                    'total_contratos' => (int) $f->total_contratos,
                    'volume_financeiro' => round((float) $f->volume_financeiro, 2),
                    'indice_aditivos' => $indice,
                    'classificacao_indice' => $classificacaoIndice,
                    'cor_indice' => $corIndice,
                ];
            })
            ->toArray();

        return $fornecedores;
    }

    /**
     * Visao do controlador: irregularidades + alteracoes recentes (RN-081 a RN-083).
     */
    public static function visaoControlador(): array
    {
        // Irregularidades
        $vencidos = Contrato::where('status', StatusContrato::Vencido->value)->count();
        $irregulares = Contrato::where('is_irregular', true)->count();

        $semFiscal = Contrato::where('status', StatusContrato::Vigente->value)
            ->whereDoesntHave('fiscalAtual')
            ->count();

        $semDocumento = Contrato::where('status', StatusContrato::Vigente->value)
            ->whereDoesntHave('documentos')
            ->count();

        // Aditivos > 25% do valor original (RN-083)
        $aditivosAcimaLimite = 0;
        $contratosComAditivos = Contrato::where('status', StatusContrato::Vigente->value)
            ->has('aditivosVigentes')
            ->with('aditivosVigentes')
            ->get();

        foreach ($contratosComAditivos as $contrato) {
            $valorOriginal = AditivoService::obterValorOriginal($contrato);
            if ($valorOriginal > 0) {
                $somaAcrescimos = (float) $contrato->aditivosVigentes->sum('valor_acrescimo');
                $percentual = ($somaAcrescimos / $valorOriginal) * 100;
                if ($percentual > 25) {
                    $aditivosAcimaLimite++;
                }
            }
        }

        // Alteracoes recentes (30 dias)
        $alteracoesRecentes = HistoricoAlteracao::where('created_at', '>=', now()->subDays(30))
            ->with('user:id,nome')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($h) => [
                'campo' => $h->campo_alterado,
                'anterior' => $h->valor_anterior,
                'novo' => $h->valor_novo,
                'usuario' => $h->user?->nome ?? 'Sistema',
                'data' => $h->created_at->format('d/m/Y H:i'),
            ])
            ->toArray();

        return [
            'irregularidades' => [
                ['label' => 'Contratos vencidos', 'total' => $vencidos, 'cor' => 'danger', 'icone' => 'solar:danger-triangle-bold'],
                ['label' => 'Contratos irregulares', 'total' => $irregulares, 'cor' => 'danger', 'icone' => 'solar:shield-warning-bold'],
                ['label' => 'Sem fiscal designado', 'total' => $semFiscal, 'cor' => 'warning', 'icone' => 'solar:user-cross-bold'],
                ['label' => 'Sem documentos', 'total' => $semDocumento, 'cor' => 'info', 'icone' => 'solar:document-cross-bold'],
                ['label' => 'Aditivos acima de 25%', 'total' => $aditivosAcimaLimite, 'cor' => 'danger', 'icone' => 'solar:chart-bold'],
            ],
            'alteracoes_recentes' => $alteracoesRecentes,
        ];
    }

    /**
     * Orquestra a agregacao completa e salva em dashboard_agregados (ADR-019/021).
     * Invalida cache Redis apos salvar (ADR-020).
     */
    public static function agregar(): DashboardAgregado
    {
        $financeiros = self::indicadoresFinanceiros();
        $risco = self::mapaRisco();
        $vencimentos = self::vencimentosPorJanela();
        $score = self::scoreGestao();

        $dados = [
            'ranking_secretarias' => self::rankingSecretarias(),
            'contratos_essenciais' => self::contratosEssenciais(),
            'tendencias_mensais' => self::tendenciasMensais(),
            'ranking_fornecedores' => self::rankingFornecedores(),
            'visao_controlador' => self::visaoControlador(),
            'score_detalhes' => $score,
        ];

        $agregado = DashboardAgregado::updateOrCreate(
            ['data_agregacao' => now()->toDateString()],
            [
                'total_contratos_ativos' => $financeiros['total_contratos_ativos'],
                'valor_total_contratado' => $financeiros['valor_total_contratado'],
                'valor_total_executado' => $financeiros['valor_total_executado'],
                'saldo_remanescente' => $financeiros['saldo_remanescente'],
                'ticket_medio' => $financeiros['ticket_medio'],
                'risco_baixo' => $risco['baixo'],
                'risco_medio' => $risco['medio'],
                'risco_alto' => $risco['alto'],
                'vencendo_0_30d' => $vencimentos['0_30d'],
                'vencendo_31_60d' => $vencimentos['31_60d'],
                'vencendo_61_90d' => $vencimentos['61_90d'],
                'vencendo_91_120d' => $vencimentos['91_120d'],
                'vencendo_120p' => $vencimentos['120p'],
                'score_gestao' => $score['score'],
                'dados_completos' => $dados,
            ]
        );

        // Invalidar cache Redis (ADR-020)
        $tenantDb = config('database.connections.tenant.database');
        Cache::forget("dashboard:{$tenantDb}");

        return $agregado;
    }

    /**
     * Obtem dados do dashboard via cache Redis ou tabela agregada (ADR-020, RN-084/085).
     * Se filtros aplicados, retorna dados em tempo real (RN-073/074).
     */
    public static function obterDadosCacheados(?array $filtros = null): array
    {
        // Com filtros: dados em tempo real (RN-073/074)
        if ($filtros && array_filter($filtros)) {
            return self::obterDadosTempoReal($filtros);
        }

        $tenantDb = config('database.connections.tenant.database');
        $cacheKey = "dashboard:{$tenantDb}";

        return Cache::remember($cacheKey, 86400, function () {
            // Tenta buscar da tabela agregada
            $agregado = DashboardAgregado::orderByDesc('data_agregacao')->first();

            if ($agregado) {
                $dadosCompletos = $agregado->dados_completos ?? [];

                return [
                    'financeiros' => [
                        'total_contratos_ativos' => $agregado->total_contratos_ativos,
                        'valor_total_contratado' => (float) $agregado->valor_total_contratado,
                        'valor_total_executado' => (float) $agregado->valor_total_executado,
                        'saldo_remanescente' => (float) $agregado->saldo_remanescente,
                        'ticket_medio' => (float) $agregado->ticket_medio,
                    ],
                    'mapa_risco' => [
                        'baixo' => $agregado->risco_baixo,
                        'medio' => $agregado->risco_medio,
                        'alto' => $agregado->risco_alto,
                        'total' => $agregado->risco_baixo + $agregado->risco_medio + $agregado->risco_alto,
                        'pct_conformes' => ($agregado->risco_baixo + $agregado->risco_medio + $agregado->risco_alto) > 0
                            ? round(($agregado->risco_baixo / ($agregado->risco_baixo + $agregado->risco_medio + $agregado->risco_alto)) * 100, 1)
                            : 100,
                    ],
                    'vencimentos' => [
                        '0_30d' => $agregado->vencendo_0_30d,
                        '31_60d' => $agregado->vencendo_31_60d,
                        '61_90d' => $agregado->vencendo_61_90d,
                        '91_120d' => $agregado->vencendo_91_120d,
                        '120p' => $agregado->vencendo_120p,
                    ],
                    'score_gestao' => $dadosCompletos['score_detalhes'] ?? self::scoreGestao(),
                    'ranking_secretarias' => $dadosCompletos['ranking_secretarias'] ?? [],
                    'contratos_essenciais' => $dadosCompletos['contratos_essenciais'] ?? [],
                    'tendencias_mensais' => $dadosCompletos['tendencias_mensais'] ?? [],
                    'ranking_fornecedores' => $dadosCompletos['ranking_fornecedores'] ?? [],
                    'visao_controlador' => $dadosCompletos['visao_controlador'] ?? [],
                    'data_agregacao' => $agregado->data_agregacao->format('d/m/Y H:i'),
                ];
            }

            // Sem dados agregados: calcula em tempo real
            return self::obterDadosTempoReal([]);
        });
    }

    /**
     * Calcula dados em tempo real (usado quando filtros aplicados ou sem cache).
     */
    private static function obterDadosTempoReal(array $filtros): array
    {
        $secretariaId = $filtros['secretaria_id'] ?? null;

        return [
            'financeiros' => self::indicadoresFinanceiros($secretariaId),
            'mapa_risco' => self::mapaRisco($secretariaId),
            'vencimentos' => self::vencimentosPorJanela($secretariaId),
            'score_gestao' => self::scoreGestao(),
            'ranking_secretarias' => self::rankingSecretarias(),
            'contratos_essenciais' => self::contratosEssenciais(),
            'tendencias_mensais' => self::tendenciasMensais(),
            'ranking_fornecedores' => self::rankingFornecedores(),
            'visao_controlador' => self::visaoControlador(),
            'data_agregacao' => now()->format('d/m/Y H:i'),
        ];
    }
}
