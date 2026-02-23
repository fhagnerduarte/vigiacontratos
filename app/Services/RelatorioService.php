<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Documento;
use App\Models\HistoricoAlteracao;
use App\Models\LoginLog;
use App\Models\LogAcessoDocumento;
use App\Models\Tenant;
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
