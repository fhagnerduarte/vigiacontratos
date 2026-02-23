<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ExportarAuditoriaRequest;
use App\Models\HistoricoAlteracao;
use App\Models\LogAcessoDocumento;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\RelatorioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditoriaController extends Controller
{
    /**
     * Trilha de auditoria unificada com filtros e paginacao.
     */
    public function index(Request $request): View
    {
        $perPage = 25;
        $page = (int) $request->input('page', 1);

        $filtros = $request->only(['data_inicio', 'data_fim', 'tipo_acao', 'user_id', 'entidade']);
        $filtros['data_inicio'] = $filtros['data_inicio'] ?? now()->subMonth()->format('Y-m-d');
        $filtros['data_fim'] = $filtros['data_fim'] ?? now()->format('Y-m-d');

        $dataFim = $filtros['data_fim'] . ' 23:59:59';

        $registros = collect();

        // Fonte 1: historico_alteracoes
        if (! isset($filtros['tipo_acao']) || $filtros['tipo_acao'] === 'alteracao') {
            $query = HistoricoAlteracao::with('user:id,nome')
                ->whereBetween('created_at', [$filtros['data_inicio'], $dataFim]);

            if (! empty($filtros['user_id'])) {
                $query->where('user_id', $filtros['user_id']);
            }
            if (! empty($filtros['entidade'])) {
                $query->where('auditable_type', 'like', '%' . ucfirst($filtros['entidade']));
            }

            foreach ($query->orderByDesc('created_at')->get() as $h) {
                $entidade = class_basename($h->auditable_type);
                $registros->push([
                    'id' => $h->id,
                    'tipo_key' => 'alteracao',
                    'data' => $h->created_at->format('d/m/Y H:i:s'),
                    'data_sort' => $h->created_at->timestamp,
                    'tipo' => 'Alteracao',
                    'usuario' => $h->user?->nome ?? '-',
                    'perfil' => $h->role_nome ?? '-',
                    'descricao' => "{$entidade} #{$h->auditable_id}: {$h->campo_alterado}",
                    'detalhes' => 'De: ' . Str::limit($h->valor_anterior ?? '—', 40) . ' → Para: ' . Str::limit($h->valor_novo ?? '—', 40),
                    'ip' => $h->ip_address ?? '-',
                ]);
            }
        }

        // Fonte 2: login_logs
        if (! isset($filtros['tipo_acao']) || $filtros['tipo_acao'] === 'login') {
            if (empty($filtros['entidade'])) {
                $query = LoginLog::with('user:id,nome')
                    ->whereBetween('created_at', [$filtros['data_inicio'], $dataFim]);

                if (! empty($filtros['user_id'])) {
                    $query->where('user_id', $filtros['user_id']);
                }

                foreach ($query->orderByDesc('created_at')->get() as $l) {
                    $registros->push([
                        'id' => $l->id,
                        'tipo_key' => 'login',
                        'data' => $l->created_at->format('d/m/Y H:i:s'),
                        'data_sort' => $l->created_at->timestamp,
                        'tipo' => 'Login',
                        'usuario' => $l->user?->nome ?? '-',
                        'perfil' => '-',
                        'descricao' => $l->success ? 'Login bem-sucedido' : 'Tentativa de login falhada',
                        'detalhes' => $l->user_agent ? Str::limit($l->user_agent, 60) : '-',
                        'ip' => $l->ip_address ?? '-',
                        'success' => $l->success,
                    ]);
                }
            }
        }

        // Fonte 3: log_acesso_documentos
        if (! isset($filtros['tipo_acao']) || $filtros['tipo_acao'] === 'acesso_documento') {
            if (empty($filtros['entidade'])) {
                $query = LogAcessoDocumento::with(['user:id,nome', 'documento:id,nome_arquivo,tipo_documento'])
                    ->whereBetween('created_at', [$filtros['data_inicio'], $dataFim]);

                if (! empty($filtros['user_id'])) {
                    $query->where('user_id', $filtros['user_id']);
                }

                foreach ($query->orderByDesc('created_at')->get() as $a) {
                    $registros->push([
                        'id' => $a->id,
                        'tipo_key' => 'acesso_documento',
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

        // Contadores para cards
        $totalAlteracoes = $registros->where('tipo', 'Alteracao')->count();
        $totalLogins = $registros->where('tipo', 'Login')->count();
        $totalAcessosDocs = $registros->where('tipo', 'Acesso Documento')->count();
        $totalGeral = $registros->count();

        // Paginacao manual
        $paginados = $registros->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $paginados,
            $totalGeral,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $usuarios = User::select('id', 'nome')->orderBy('nome')->get();

        return view('tenant.auditoria.index', compact(
            'paginator',
            'filtros',
            'usuarios',
            'totalAlteracoes',
            'totalLogins',
            'totalAcessosDocs',
            'totalGeral',
        ));
    }

    /**
     * Detalhe de uma entrada de auditoria.
     */
    public function show(Request $request, string $tipo, int $id): View
    {
        $registro = null;
        $contexto = collect();

        switch ($tipo) {
            case 'alteracao':
                $registro = HistoricoAlteracao::with(['user:id,nome', 'auditable'])->findOrFail($id);

                // Contexto: mesma entidade, mesmo periodo (+/- 5 minutos)
                $contexto = HistoricoAlteracao::with('user:id,nome')
                    ->where('auditable_type', $registro->auditable_type)
                    ->where('auditable_id', $registro->auditable_id)
                    ->where('id', '!=', $registro->id)
                    ->whereBetween('created_at', [
                        $registro->created_at->subMinutes(5),
                        $registro->created_at->addMinutes(5),
                    ])
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get();
                break;

            case 'login':
                $registro = LoginLog::with('user:id,nome')->findOrFail($id);

                // Contexto: logins do mesmo usuario, mesmo periodo
                $contexto = LoginLog::with('user:id,nome')
                    ->where('user_id', $registro->user_id)
                    ->where('id', '!=', $registro->id)
                    ->whereBetween('created_at', [
                        $registro->created_at->subMinutes(30),
                        $registro->created_at->addMinutes(30),
                    ])
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get();
                break;

            case 'acesso_documento':
                $registro = LogAcessoDocumento::with(['user:id,nome', 'documento:id,nome_arquivo,tipo_documento'])
                    ->findOrFail($id);

                // Contexto: acessos do mesmo usuario ao mesmo documento
                $contexto = LogAcessoDocumento::with('user:id,nome')
                    ->where('documento_id', $registro->documento_id)
                    ->where('id', '!=', $registro->id)
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get();
                break;

            default:
                abort(404);
        }

        return view('tenant.auditoria.show', compact('tipo', 'registro', 'contexto'));
    }

    /**
     * Exportar trilha de auditoria em PDF.
     */
    public function exportarPdf(ExportarAuditoriaRequest $request)
    {
        $dados = RelatorioService::dadosAuditoria($request->validated());

        $pdf = Pdf::loadView('tenant.relatorios.pdf.auditoria', compact('dados'))
            ->setPaper('a4', 'landscape');

        $nomeArquivo = 'relatorio-auditoria-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * Exportar trilha de auditoria em CSV.
     */
    public function exportarCsv(ExportarAuditoriaRequest $request): StreamedResponse
    {
        $registros = RelatorioService::dadosAuditoriaCSV($request->validated());

        $nomeArquivo = 'relatorio-auditoria-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($registros) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            if ($registros->isNotEmpty()) {
                fputcsv($handle, array_keys($registros->first()), ';');
            }

            foreach ($registros as $registro) {
                fputcsv($handle, $registro, ';');
            }

            fclose($handle);
        }, $nomeArquivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
