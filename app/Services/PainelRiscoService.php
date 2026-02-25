<?php

namespace App\Services;

use App\Enums\NivelRisco;
use App\Enums\StatusContrato;
use App\Models\Contrato;
use App\Models\Scopes\SecretariaScope;
use App\Models\Secretaria;
use App\Models\Tenant;

class PainelRiscoService
{
    /**
     * 5 indicadores principais do Painel de Risco (RN-144).
     */
    public static function indicadores(): array
    {
        $totalAtivos = Contrato::where('status', StatusContrato::Vigente->value)->count();

        $altoRisco = Contrato::where('status', StatusContrato::Vigente->value)
            ->where('nivel_risco', NivelRisco::Alto->value)
            ->count();

        $pctAltoRisco = $totalAtivos > 0 ? round(($altoRisco / $totalAtivos) * 100, 1) : 0;

        $vencendo30d = Contrato::where('status', StatusContrato::Vigente->value)
            ->whereNotNull('data_fim')
            ->whereBetween('data_fim', [now()->startOfDay(), now()->addDays(30)])
            ->count();

        // Aditivos >20% do valor original
        $aditivosAcima20 = 0;
        $contratosComAditivos = Contrato::where('status', StatusContrato::Vigente->value)
            ->has('aditivosVigentes')
            ->with('aditivosVigentes')
            ->get();

        foreach ($contratosComAditivos as $contrato) {
            $valorOriginal = AditivoService::obterValorOriginal($contrato);
            if ($valorOriginal > 0) {
                $somaAcrescimos = (float) $contrato->aditivosVigentes->sum('valor_acrescimo');
                if (($somaAcrescimos / $valorOriginal) * 100 > 20) {
                    $aditivosAcima20++;
                }
            }
        }

        // Contratos sem documentacao obrigatoria
        $semDocObrigatoria = Contrato::where('status', StatusContrato::Vigente->value)
            ->with('documentos')
            ->get()
            ->filter(fn ($c) => $c->status_completude !== \App\Enums\StatusCompletudeDocumental::Completo)
            ->count();

        return [
            'total_ativos' => $totalAtivos,
            'pct_alto_risco' => $pctAltoRisco,
            'alto_risco' => $altoRisco,
            'vencendo_30d' => $vencendo30d,
            'aditivos_acima_20' => $aditivosAcima20,
            'sem_doc_obrigatoria' => $semDocObrigatoria,
        ];
    }

    /**
     * Ranking de risco: contratos ordenados por score DESC com categorias (RN-146/147).
     */
    public static function rankingRisco(int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $contratos = Contrato::where('status', StatusContrato::Vigente->value)
            ->with(['secretaria:id,nome,sigla', 'fornecedor:id,razao_social', 'fiscalAtual', 'documentos', 'aditivos'])
            ->orderByDesc('score_risco')
            ->paginate($perPage);

        $contratos->getCollection()->transform(function ($contrato) {
            $expandido = RiscoService::calcularExpandido($contrato);

            $categoriasAtivas = [];
            foreach ($expandido['categorias'] as $nome => $cat) {
                if ($cat['score'] > 0) {
                    $categoriasAtivas[] = [
                        'nome' => $nome,
                        'label' => self::labelCategoria($nome),
                        'cor' => self::corCategoria($nome),
                        'score' => $cat['score'],
                        'criterios' => $cat['criterios'],
                    ];
                }
            }

            $contrato->categorias_risco = $categoriasAtivas;
            $contrato->score_expandido = $expandido['score'];
            $contrato->nivel_expandido = $expandido['nivel'];

            return $contrato;
        });

        return $contratos;
    }

    /**
     * Mapa de risco por secretaria (RN-148/149).
     */
    public static function mapaRiscoPorSecretaria(): array
    {
        return Secretaria::withCount([
            'contratos as total_contratos' => fn ($q) => $q->where('status', StatusContrato::Vigente->value),
            'contratos as contratos_criticos' => fn ($q) => $q->where('status', StatusContrato::Vigente->value)
                ->where('nivel_risco', NivelRisco::Alto->value),
        ])
            ->having('total_contratos', '>', 0)
            ->orderByDesc('contratos_criticos')
            ->get()
            ->map(function ($sec) {
                $sec->pct_risco = $sec->total_contratos > 0
                    ? round(($sec->contratos_criticos / $sec->total_contratos) * 100, 1)
                    : 0;
                $sec->destaque = $sec->pct_risco > 30;

                return $sec;
            })
            ->toArray();
    }

    /**
     * Dados para o relatorio TCE em PDF (RN-150/151).
     */
    public static function dadosRelatorioTCE(): array
    {
        $tenant = Tenant::where('is_ativo', true)->first();

        // Relatorio TCE e documento global de compliance — desativa scope por secretaria
        $contratos = Contrato::withoutGlobalScope(SecretariaScope::class)
            ->where('status', StatusContrato::Vigente->value)
            ->with(['secretaria:id,nome,sigla', 'fornecedor:id,razao_social,cnpj', 'fiscalAtual', 'documentos', 'aditivos'])
            ->orderByDesc('score_risco')
            ->get()
            ->map(function ($contrato) {
                $expandido = RiscoService::calcularExpandido($contrato);

                $categoriasAtivas = [];
                $justificativas = [];
                foreach ($expandido['categorias'] as $nome => $cat) {
                    if ($cat['score'] > 0) {
                        $categoriasAtivas[] = self::labelCategoria($nome);
                        foreach ($cat['criterios'] as $criterio) {
                            $justificativas[] = $criterio;
                        }
                    }
                }

                return [
                    'numero' => $contrato->numero . '/' . $contrato->ano,
                    'objeto' => $contrato->objeto,
                    'fornecedor' => $contrato->fornecedor?->razao_social,
                    'cnpj_fornecedor' => $contrato->fornecedor?->cnpj,
                    'secretaria' => $contrato->secretaria?->nome,
                    'valor_global' => (float) $contrato->valor_global,
                    'data_inicio' => $contrato->data_inicio->format('d/m/Y'),
                    'data_fim' => $contrato->data_fim?->format('d/m/Y'),
                    'score' => $expandido['score'],
                    'nivel' => $expandido['nivel']->label(),
                    'cor_nivel' => $expandido['nivel']->cor(),
                    'categorias' => $categoriasAtivas,
                    'justificativas' => $justificativas,
                ];
            })
            ->toArray();

        // Resumo
        $totalContratos = count($contratos);
        $altoRisco = collect($contratos)->where('nivel', NivelRisco::Alto->label())->count();
        $medioRisco = collect($contratos)->where('nivel', NivelRisco::Medio->label())->count();
        $baixoRisco = collect($contratos)->where('nivel', NivelRisco::Baixo->label())->count();

        return [
            'municipio' => $tenant?->nome ?? 'Municipio',
            'data_geracao' => now()->format('d/m/Y H:i'),
            'resumo' => [
                'total_monitorados' => $totalContratos,
                'alto_risco' => $altoRisco,
                'medio_risco' => $medioRisco,
                'baixo_risco' => $baixoRisco,
            ],
            'contratos' => $contratos,
        ];
    }

    /**
     * Retorna label traduzido da categoria de risco.
     */
    public static function labelCategoria(string $categoria): string
    {
        return match ($categoria) {
            'vencimento' => 'Vencimento',
            'financeiro' => 'Financeiro',
            'documental' => 'Documental',
            'juridico' => 'Juridico',
            'operacional' => 'Operacional',
            default => ucfirst($categoria),
        };
    }

    /**
     * Retorna cor Bootstrap da categoria de risco (RN-147).
     */
    public static function corCategoria(string $categoria): string
    {
        return match ($categoria) {
            'vencimento' => 'warning',
            'financeiro' => 'danger',
            'documental' => 'info',
            'juridico' => 'primary',
            'operacional' => 'secondary',
            default => 'dark',
        };
    }
}
