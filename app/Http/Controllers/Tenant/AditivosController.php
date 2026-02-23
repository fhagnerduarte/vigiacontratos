<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\StatusContrato;
use App\Enums\TipoAditivo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreAditivoRequest;
use App\Models\Aditivo;
use App\Models\ConfiguracaoLimiteAditivo;
use App\Models\Contrato;
use App\Services\AditivoService;
use App\Services\WorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AditivosController extends Controller
{
    /**
     * Dashboard de aditivos — indicadores anuais (RN-109 a RN-114).
     */
    public function index(): View
    {
        $anoAtual = date('Y');
        $conn = \Illuminate\Support\Facades\DB::connection('tenant');

        // Scope por secretaria para raw queries (RN-326)
        $secretariaIds = null;
        if (auth()->check() && ! auth()->user()->isPerfilEstrategico()) {
            $secretariaIds = auth()->user()->secretarias()->pluck('secretarias.id')->toArray();
        }

        // Indicadores anuais (RN-110, RN-111, RN-112)
        $baseIndicadores = $conn->table('aditivos')
            ->join('contratos', 'aditivos.contrato_id', '=', 'contratos.id')
            ->whereNull('aditivos.deleted_at')
            ->whereNull('contratos.deleted_at')
            ->whereYear('aditivos.data_assinatura', $anoAtual);

        if ($secretariaIds !== null) {
            $baseIndicadores->whereIn('contratos.secretaria_id', $secretariaIds);
        }

        $totalAditivosAno = (clone $baseIndicadores)->count();

        $valorTotalAcrescido = (float) (clone $baseIndicadores)->sum('aditivos.valor_acrescimo');

        $percentualMedioAcrescimo = round((float) (clone $baseIndicadores)
            ->where('aditivos.percentual_acumulado', '>', 0)
            ->avg('aditivos.percentual_acumulado'), 2);

        // Ranking contratos mais alterados — Top 10 (RN-113)
        $queryRanking = $conn->table('aditivos')
            ->join('contratos', 'aditivos.contrato_id', '=', 'contratos.id')
            ->whereNull('aditivos.deleted_at')
            ->whereNull('contratos.deleted_at');

        if ($secretariaIds !== null) {
            $queryRanking->whereIn('contratos.secretaria_id', $secretariaIds);
        }

        $rankingContratosMaisAlterados = $queryRanking
            ->select(
                'contratos.id as contrato_id',
                'contratos.numero',
                $conn->raw('COUNT(aditivos.id) as total_aditivos'),
                $conn->raw('MAX(aditivos.percentual_acumulado) as percentual_acumulado')
            )
            ->groupBy('contratos.id', 'contratos.numero')
            ->orderByDesc('total_aditivos')
            ->limit(10)
            ->get();

        // Ranking secretarias com mais aditivos — Top 5 (RN-114)
        $querySecretarias = $conn->table('aditivos')
            ->join('contratos', 'aditivos.contrato_id', '=', 'contratos.id')
            ->join('secretarias', 'contratos.secretaria_id', '=', 'secretarias.id')
            ->whereNull('aditivos.deleted_at')
            ->whereNull('contratos.deleted_at');

        if ($secretariaIds !== null) {
            $querySecretarias->whereIn('contratos.secretaria_id', $secretariaIds);
        }

        $rankingSecretarias = $querySecretarias
            ->select(
                'secretarias.nome',
                $conn->raw('COUNT(aditivos.id) as total_aditivos')
            )
            ->groupBy('secretarias.id', 'secretarias.nome')
            ->orderByDesc('total_aditivos')
            ->limit(5)
            ->get();

        return view('tenant.aditivos.index', compact(
            'totalAditivosAno',
            'valorTotalAcrescido',
            'percentualMedioAcrescimo',
            'rankingContratosMaisAlterados',
            'rankingSecretarias',
        ));
    }

    /**
     * Formulario de criacao de aditivo aninhado ao contrato.
     */
    public function create(Contrato $contrato): View|RedirectResponse
    {
        // RN-009/RN-052: Contrato precisa estar vigente ou vencido (retroativo com justificativa)
        if (! in_array($contrato->status, [StatusContrato::Vigente, StatusContrato::Vencido])) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Aditivo so pode ser adicionado a contrato vigente ou vencido (RN-009/RN-052).');
        }

        $contrato->load('fornecedor', 'secretaria');

        // Flag para view: contrato vencido exige justificativa retroativa (RN-052)
        $exigeJustificativaRetroativa = $contrato->status === StatusContrato::Vencido;

        // Dados para o formulario
        $proximoSequencial = AditivoService::gerarNumeroSequencial($contrato);
        $percentualAcumuladoAtual = AditivoService::calcularPercentualAcumulado($contrato);
        $valorOriginal = AditivoService::obterValorOriginal($contrato);
        $limiteLegal = AditivoService::verificarLimiteLegal($contrato, $percentualAcumuladoAtual);

        return view('tenant.aditivos.create', compact(
            'contrato',
            'proximoSequencial',
            'percentualAcumuladoAtual',
            'valorOriginal',
            'limiteLegal',
            'exigeJustificativaRetroativa',
        ));
    }

    /**
     * Salva o aditivo com validacao de limites legais.
     */
    public function store(StoreAditivoRequest $request, Contrato $contrato): RedirectResponse
    {
        // RN-009/RN-052: Contrato precisa estar vigente ou vencido
        if (! in_array($contrato->status, [StatusContrato::Vigente, StatusContrato::Vencido])) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Aditivo so pode ser adicionado a contrato vigente ou vencido (RN-009/RN-052).');
        }

        $dados = $request->validated();

        // Verifica limite legal antes de salvar (RN-098 a RN-102)
        $novoAcrescimo = (float) ($dados['valor_acrescimo'] ?? 0);
        $percentualProjetado = AditivoService::calcularPercentualAcumulado($contrato, $novoAcrescimo);
        $limiteLegal = AditivoService::verificarLimiteLegal($contrato, $percentualProjetado);

        if (! $limiteLegal['dentro_limite']) {
            // Bloqueante: impede salvamento (RN-101)
            if ($limiteLegal['is_bloqueante']) {
                return redirect()->back()->withInput()
                    ->with('error', 'O percentual acumulado de acrescimos (' . $percentualProjetado . '%) ultrapassa o limite legal de ' . $limiteLegal['limite'] . '%. Salvamento bloqueado (RN-101).');
            }

            // Nao-bloqueante: exige justificativa (RN-102)
            if (empty($dados['justificativa_excesso_limite'])) {
                return redirect()->back()->withInput()
                    ->withErrors(['justificativa_excesso_limite' => 'O percentual acumulado ultrapassa o limite legal. Justificativa obrigatoria para prosseguir (RN-102).']);
            }
        }

        try {
            $aditivo = AditivoService::criar($dados, $contrato, $request->user(), $request->ip());

            return redirect()->route('tenant.aditivos.show', $aditivo)
                ->with('success', $aditivo->numero_sequencial . 'o Termo Aditivo registrado com sucesso. Workflow de aprovacao iniciado.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Detalhes do aditivo com timeline e workflow (padroes-aditivos.md).
     */
    public function show(Aditivo $aditivo): View
    {
        $aditivo->load([
            'contrato.fornecedor',
            'contrato.secretaria',
            'documentos' => fn ($q) => $q->versaoAtual(),
            'workflowAprovacoes' => fn ($q) => $q->with('roleResponsavel', 'user')->orderBy('etapa_ordem'),
        ]);

        // Todos os aditivos do contrato para timeline
        $todosAditivos = $aditivo->contrato->aditivos()
            ->orderBy('numero_sequencial')
            ->get();

        // Limite legal configurado
        $limiteLegal = AditivoService::verificarLimiteLegal(
            $aditivo->contrato,
            (float) $aditivo->percentual_acumulado
        );

        // Etapa atual do workflow
        $etapaAtual = WorkflowService::obterEtapaAtual($aditivo);
        $workflowAprovado = WorkflowService::isAprovado($aditivo);

        return view('tenant.aditivos.show', compact(
            'aditivo',
            'todosAditivos',
            'limiteLegal',
            'etapaAtual',
            'workflowAprovado',
        ));
    }

    /**
     * Aprova a etapa atual do workflow (RN-337).
     */
    public function aprovar(Request $request, Aditivo $aditivo): RedirectResponse
    {
        $etapaAtual = WorkflowService::obterEtapaAtual($aditivo);

        if (! $etapaAtual) {
            return redirect()->route('tenant.aditivos.show', $aditivo)
                ->with('error', 'Nao ha etapa pendente para aprovacao.');
        }

        try {
            WorkflowService::aprovar(
                $etapaAtual,
                $request->user(),
                $request->input('parecer'),
                $request->ip()
            );

            return redirect()->route('tenant.aditivos.show', $aditivo)
                ->with('success', 'Etapa "' . $etapaAtual->etapa->label() . '" aprovada com sucesso.');
        } catch (\RuntimeException $e) {
            return redirect()->route('tenant.aditivos.show', $aditivo)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancela um aditivo vigente (RN-116). Apenas admin.
     */
    public function cancelar(Request $request, Aditivo $aditivo): RedirectResponse
    {
        try {
            AditivoService::cancelar($aditivo, $request->user(), $request->ip());

            return redirect()->route('tenant.aditivos.show', $aditivo)
                ->with('success', $aditivo->numero_sequencial . 'o Termo Aditivo cancelado com sucesso. Valores do contrato recalculados.');
        } catch (\RuntimeException $e) {
            return redirect()->route('tenant.aditivos.show', $aditivo)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reprova a etapa atual do workflow (RN-338). Parecer obrigatorio.
     */
    public function reprovar(Request $request, Aditivo $aditivo): RedirectResponse
    {
        $request->validate([
            'parecer' => ['required', 'string'],
        ], [
            'parecer.required' => 'O parecer/motivo e obrigatorio na reprovacao (RN-338).',
        ]);

        $etapaAtual = WorkflowService::obterEtapaAtual($aditivo);

        if (! $etapaAtual) {
            return redirect()->route('tenant.aditivos.show', $aditivo)
                ->with('error', 'Nao ha etapa pendente para reprovacao.');
        }

        try {
            WorkflowService::reprovar(
                $etapaAtual,
                $request->user(),
                $request->input('parecer'),
                $request->ip()
            );

            return redirect()->route('tenant.aditivos.show', $aditivo)
                ->with('success', 'Etapa "' . $etapaAtual->etapa->label() . '" reprovada. O solicitante sera notificado.');
        } catch (\RuntimeException $e) {
            return redirect()->route('tenant.aditivos.show', $aditivo)
                ->with('error', $e->getMessage());
        }
    }
}
