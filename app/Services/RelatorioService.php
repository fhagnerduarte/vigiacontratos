<?php

namespace App\Services;

use App\Enums\StatusContrato;
use App\Enums\TipoAditivo;
use App\Models\Aditivo;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\HistoricoAlteracao;
use App\Models\LoginLog;
use App\Models\LogAcessoDocumento;
use App\Models\Secretaria;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class RelatorioService
{
    /**
     * RN-133: Dados do relatorio de documentos por contrato (TCE).
     */
    public static function dadosDocumentosContrato(Contrato $contrato): array
    {
        $contrato->load(['fornecedor:id,razao_social,cnpj', 'secretaria:id,nome', 'documentos' => function ($q) {
            $q->versaoAtual()->with('uploader:id,nome')->orderBy('tipo_documento');
        }]);

        $tenant = Tenant::where('is_ativo', true)->first();

        $documentos = $contrato->documentos->map(function (Documento $doc) {
            return [
                'tipo_documento' => $doc->tipo_documento->label(),
                'nome_arquivo' => $doc->nome_arquivo,
                'versao' => $doc->versao,
                'data_upload' => $doc->created_at->format('d/m/Y H:i'),
                'responsavel' => $doc->uploader?->nome ?? '-',
                'tamanho_kb' => $doc->tamanho > 0 ? round($doc->tamanho / 1024, 1) : '-',
            ];
        })->toArray();

        $statusCompletude = $contrato->status_completude;

        return [
            'municipio' => $tenant?->nome ?? 'Municipio',
            'data_geracao' => now()->format('d/m/Y H:i'),
            'contrato' => [
                'numero' => $contrato->numero . '/' . $contrato->ano,
                'objeto' => $contrato->objeto,
                'fornecedor' => $contrato->fornecedor?->razao_social ?? '-',
                'cnpj' => $contrato->fornecedor?->cnpj ?? '-',
                'secretaria' => $contrato->secretaria?->nome ?? '-',
                'valor_global' => (float) $contrato->valor_global,
                'data_inicio' => $contrato->data_inicio->format('d/m/Y'),
                'data_fim' => $contrato->data_fim?->format('d/m/Y') ?? '-',
                'status' => $contrato->status->label(),
            ],
            'completude' => [
                'status' => $statusCompletude->label(),
                'cor' => $statusCompletude->cor(),
            ],
            'documentos' => $documentos,
            'total_documentos' => count($documentos),
        ];
    }

    /**
     * RN-222: Dados do relatorio de auditoria (3 fontes unificadas).
     */
    public static function dadosAuditoria(array $filtros): array
    {
        $dataInicio = $filtros['data_inicio'];
        $dataFim = $filtros['data_fim'] . ' 23:59:59';

        $registros = collect();

        // Fonte 1: historico_alteracoes
        if (! isset($filtros['tipo_acao']) || $filtros['tipo_acao'] === 'alteracao') {
            $query = HistoricoAlteracao::with('user:id,nome')
                ->whereBetween('created_at', [$dataInicio, $dataFim]);

            if (isset($filtros['user_id'])) {
                $query->where('user_id', $filtros['user_id']);
            }
            if (isset($filtros['entidade'])) {
                $query->where('auditable_type', 'like', '%' . ucfirst($filtros['entidade']));
            }

            $historicos = $query->orderByDesc('created_at')->limit(2000)->get();

            foreach ($historicos as $h) {
                $entidade = class_basename($h->auditable_type);
                $registros->push([
                    'data' => $h->created_at->format('d/m/Y H:i:s'),
                    'data_sort' => $h->created_at->timestamp,
                    'tipo' => 'Alteracao',
                    'usuario' => $h->user?->nome ?? '-',
                    'perfil' => $h->role_nome ?? '-',
                    'descricao' => "{$entidade} #{$h->auditable_id}: {$h->campo_alterado}",
                    'detalhes' => "De: {$h->valor_anterior} → Para: {$h->valor_novo}",
                    'ip' => $h->ip_address ?? '-',
                ]);
            }
        }

        // Fonte 2: login_logs
        if (! isset($filtros['tipo_acao']) || $filtros['tipo_acao'] === 'login') {
            if (! isset($filtros['entidade'])) {
                $query = LoginLog::with('user:id,nome')
                    ->whereBetween('created_at', [$dataInicio, $dataFim]);

                if (isset($filtros['user_id'])) {
                    $query->where('user_id', $filtros['user_id']);
                }

                $logins = $query->orderByDesc('created_at')->limit(2000)->get();

                foreach ($logins as $l) {
                    $registros->push([
                        'data' => $l->created_at->format('d/m/Y H:i:s'),
                        'data_sort' => $l->created_at->timestamp,
                        'tipo' => 'Login',
                        'usuario' => $l->user?->nome ?? '-',
                        'perfil' => '-',
                        'descricao' => $l->success ? 'Login bem-sucedido' : 'Tentativa de login falhada',
                        'detalhes' => $l->user_agent ? \Illuminate\Support\Str::limit($l->user_agent, 60) : '-',
                        'ip' => $l->ip_address ?? '-',
                    ]);
                }
            }
        }

        // Fonte 3: log_acesso_documentos
        if (! isset($filtros['tipo_acao']) || $filtros['tipo_acao'] === 'acesso_documento') {
            if (! isset($filtros['entidade'])) {
                $query = LogAcessoDocumento::with(['user:id,nome', 'documento:id,nome_arquivo,tipo_documento'])
                    ->whereBetween('created_at', [$dataInicio, $dataFim]);

                if (isset($filtros['user_id'])) {
                    $query->where('user_id', $filtros['user_id']);
                }

                $acessos = $query->orderByDesc('created_at')->limit(2000)->get();

                foreach ($acessos as $a) {
                    $registros->push([
                        'data' => $a->created_at->format('d/m/Y H:i:s'),
                        'data_sort' => $a->created_at->timestamp,
                        'tipo' => 'Acesso Documento',
                        'usuario' => $a->user?->nome ?? '-',
                        'perfil' => '-',
                        'descricao' => "{$a->acao->value}: {$a->documento?->nome_arquivo}",
                        'detalhes' => $a->documento?->tipo_documento?->label() ?? '-',
                        'ip' => $a->ip_address ?? '-',
                    ]);
                }
            }
        }

        // Ordenar por data DESC
        $registros = $registros->sortByDesc('data_sort')->values();

        $tenant = Tenant::where('is_ativo', true)->first();

        // Resumo por tipo
        $resumo = $registros->groupBy('tipo')->map->count()->toArray();

        return [
            'municipio' => $tenant?->nome ?? 'Municipio',
            'data_geracao' => now()->format('d/m/Y H:i'),
            'filtros' => [
                'data_inicio' => \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y'),
                'data_fim' => \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y'),
                'tipo_acao' => $filtros['tipo_acao'] ?? 'Todos',
                'entidade' => isset($filtros['entidade']) ? ucfirst($filtros['entidade']) : 'Todas',
            ],
            'resumo' => $resumo,
            'total_registros' => $registros->count(),
            'registros' => $registros->toArray(),
        ];
    }

    /**
     * RN-222: Dados para exportacao CSV de auditoria.
     */
    public static function dadosAuditoriaCSV(array $filtros): Collection
    {
        $dados = self::dadosAuditoria($filtros);

        return collect($dados['registros'])->map(function ($registro) {
            return [
                'Data' => $registro['data'],
                'Tipo' => $registro['tipo'],
                'Usuario' => $registro['usuario'],
                'Perfil' => $registro['perfil'],
                'Descricao' => $registro['descricao'],
                'Detalhes' => $registro['detalhes'],
                'IP' => $registro['ip'],
            ];
        });
    }

    /**
     * RN-225: Dados do relatorio de conformidade documental com verificacao de integridade.
     */
    public static function dadosConformidadeDocumental(): array
    {
        $documentos = Documento::versaoAtual()
            ->with(['uploader:id,nome', 'documentable'])
            ->orderBy('documentable_type')
            ->orderBy('documentable_id')
            ->get();

        $tenant = Tenant::where('is_ativo', true)->first();

        $itens = $documentos->map(function (Documento $doc) {
            $contrato = $doc->documentable;
            $numeroContrato = '-';
            if ($contrato instanceof Contrato) {
                $numeroContrato = $contrato->numero . '/' . $contrato->ano;
            }

            return [
                'contrato' => $numeroContrato,
                'tipo_documento' => $doc->tipo_documento->label(),
                'nome_arquivo' => $doc->nome_arquivo,
                'hash_sha256' => $doc->hash_integridade ?? '-',
                'data_upload' => $doc->created_at->format('d/m/Y H:i'),
                'responsavel' => $doc->uploader?->nome ?? '-',
                'status_integridade' => self::verificarIntegridade($doc),
            ];
        })->toArray();

        $totalIntegros = collect($itens)->where('status_integridade', 'integro')->count();
        $totalCorrompidos = collect($itens)->where('status_integridade', 'corrompido')->count();
        $totalAusentes = collect($itens)->where('status_integridade', 'arquivo_ausente')->count();

        return [
            'municipio' => $tenant?->nome ?? 'Municipio',
            'data_geracao' => now()->format('d/m/Y H:i'),
            'resumo' => [
                'total_documentos' => count($itens),
                'integros' => $totalIntegros,
                'corrompidos' => $totalCorrompidos,
                'ausentes' => $totalAusentes,
            ],
            'documentos' => $itens,
        ];
    }

    /**
     * RN-057: Dados do relatorio de efetividade mensal dos alertas.
     * Contratos regularizados a tempo vs. vencidos, tempo medio de antecipacao.
     */
    public static function dadosEfetividadeMensal(array $filtros): array
    {
        $mes = (int) $filtros['mes'];
        $ano = (int) $filtros['ano'];
        $secretariaId = $filtros['secretaria_id'] ?? null;

        $mesInicio = Carbon::create($ano, $mes, 1)->startOfDay();
        $mesFim = $mesInicio->copy()->endOfMonth()->endOfDay();

        $tenant = Tenant::where('is_ativo', true)->first();

        // Contratos elegiveis: data_fim dentro do mes analisado
        $queryElegiveis = Contrato::withoutGlobalScope(\App\Models\Scopes\SecretariaScope::class)
            ->with(['secretaria:id,nome', 'fornecedor:id,razao_social'])
            ->whereBetween('data_fim', [$mesInicio, $mesFim])
            ->whereNotIn('status', [StatusContrato::Cancelado->value]);

        if ($secretariaId) {
            $queryElegiveis->where('secretaria_id', $secretariaId);
        }

        $contratosElegiveis = $queryElegiveis->get();

        // Classificar cada contrato
        $contratos = [];
        $regularizadosATempo = 0;
        $vencidosSemAcao = 0;
        $regularizadosRetroativos = 0;
        $diasAntecipacao = [];

        foreach ($contratosElegiveis as $contrato) {
            // Buscar aditivos de prazo deste contrato (vigentes/aprovados)
            $aditivoPrazo = Aditivo::where('contrato_id', $contrato->id)
                ->whereIn('tipo', [
                    TipoAditivo::Prazo->value,
                    TipoAditivo::PrazoEValor->value,
                    TipoAditivo::Misto->value,
                ])
                ->whereNotIn('status', ['cancelado'])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($aditivoPrazo && $aditivoPrazo->created_at->lte($contrato->data_fim)) {
                // Regularizado a tempo (aditivo criado ANTES do vencimento)
                $statusEfetividade = 'regularizado_a_tempo';
                $regularizadosATempo++;
                $dias = (int) $aditivoPrazo->created_at->startOfDay()->diffInDays($contrato->data_fim->startOfDay(), false);
                $diasAntecipacao[] = $dias;
            } elseif ($aditivoPrazo && $aditivoPrazo->created_at->gt($contrato->data_fim)) {
                // Regularizado apos vencimento (aditivo retroativo)
                $statusEfetividade = 'regularizado_retroativo';
                $regularizadosRetroativos++;
            } else {
                // Vencido sem acao
                $statusEfetividade = 'vencido_sem_acao';
                $vencidosSemAcao++;
            }

            $contratos[] = [
                'id' => $contrato->id,
                'numero' => $contrato->numero . '/' . $contrato->ano,
                'objeto' => $contrato->objeto,
                'secretaria' => $contrato->secretaria?->nome ?? '-',
                'secretaria_id' => $contrato->secretaria_id,
                'fornecedor' => $contrato->fornecedor?->razao_social ?? '-',
                'valor_global' => (float) $contrato->valor_global,
                'data_fim' => $contrato->data_fim->format('d/m/Y'),
                'status_atual' => $contrato->status->label(),
                'is_irregular' => $contrato->is_irregular,
                'status_efetividade' => $statusEfetividade,
                'aditivo' => $aditivoPrazo ? 'Aditivo #' . $aditivoPrazo->numero_sequencial . ' (' . $aditivoPrazo->tipo->label() . ')' : '-',
                'dias_antecipacao' => ($statusEfetividade === 'regularizado_a_tempo' && $aditivoPrazo)
                    ? (int) $aditivoPrazo->created_at->startOfDay()->diffInDays($contrato->data_fim->startOfDay(), false)
                    : null,
            ];
        }

        $totalElegiveis = count($contratos);
        $taxaEfetividade = $totalElegiveis > 0
            ? round(($regularizadosATempo / $totalElegiveis) * 100, 1)
            : 0;
        $tempoMedioAntecipacao = count($diasAntecipacao) > 0
            ? round(array_sum($diasAntecipacao) / count($diasAntecipacao), 1)
            : 0;

        // Detalhamento por secretaria
        $porSecretaria = collect($contratos)
            ->groupBy('secretaria')
            ->map(function ($grupo, $nomeSecretaria) {
                $total = $grupo->count();
                $regularizados = $grupo->where('status_efetividade', 'regularizado_a_tempo')->count();
                $vencidos = $grupo->where('status_efetividade', 'vencido_sem_acao')->count();
                $retroativos = $grupo->where('status_efetividade', 'regularizado_retroativo')->count();
                $taxa = $total > 0 ? round(($regularizados / $total) * 100, 1) : 0;

                return [
                    'secretaria' => $nomeSecretaria,
                    'total' => $total,
                    'regularizados' => $regularizados,
                    'vencidos' => $vencidos,
                    'retroativos' => $retroativos,
                    'taxa' => $taxa,
                ];
            })
            ->sortByDesc('taxa')
            ->values()
            ->toArray();

        return [
            'municipio' => $tenant?->nome ?? 'Municipio',
            'data_geracao' => now()->format('d/m/Y H:i'),
            'filtros' => [
                'periodo' => $mesInicio->translatedFormat('F/Y'),
                'secretaria' => $secretariaId
                    ? (Secretaria::find($secretariaId)?->nome ?? 'Todas')
                    : 'Todas',
            ],
            'resumo' => [
                'total_elegiveis' => $totalElegiveis,
                'regularizados_a_tempo' => $regularizadosATempo,
                'vencidos_sem_acao' => $vencidosSemAcao,
                'regularizados_retroativos' => $regularizadosRetroativos,
                'taxa_efetividade' => $taxaEfetividade,
                'tempo_medio_antecipacao' => $tempoMedioAntecipacao,
            ],
            'por_secretaria' => $porSecretaria,
            'contratos' => $contratos,
        ];
    }

    /**
     * Verificar integridade de um documento individual (RN-221).
     * Recalcula SHA-256 e compara com hash armazenado.
     */
    public static function verificarIntegridade(Documento $documento): string
    {
        if (empty($documento->hash_integridade)) {
            return 'sem_hash';
        }

        if (empty($documento->caminho) || ! Storage::disk('local')->exists($documento->caminho)) {
            return 'arquivo_ausente';
        }

        $hashRecalculado = hash('sha256', Storage::disk('local')->get($documento->caminho));

        return $hashRecalculado === $documento->hash_integridade ? 'integro' : 'corrompido';
    }
}
