<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\ClassificacaoRespostaLai;
use App\Enums\StatusSolicitacaoLai;
use App\Http\Controllers\Controller;
use App\Models\SolicitacaoLai;
use App\Services\SolicitacaoLaiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitacoesLaiController extends Controller
{
    /**
     * Listagem de solicitacoes LAI com filtros.
     */
    public function index(Request $request): View
    {
        $query = SolicitacaoLai::query()->latest();

        if ($request->filled('status')) {
            $status = StatusSolicitacaoLai::tryFrom($request->status);
            if ($status) {
                $query->porStatus($status);
            }
        }

        if ($request->filled('vencidas') && $request->vencidas === '1') {
            $query->vencidas();
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('protocolo', 'like', "%{$busca}%")
                    ->orWhere('nome_solicitante', 'like', "%{$busca}%")
                    ->orWhere('assunto', 'like', "%{$busca}%");
            });
        }

        $solicitacoes = $query->paginate(20)->withQueryString();
        $resumo = SolicitacaoLaiService::resumo();
        $statusOptions = StatusSolicitacaoLai::cases();

        return view('tenant.solicitacoes-lai.index', compact('solicitacoes', 'resumo', 'statusOptions'));
    }

    /**
     * Detalhe da solicitacao com historico.
     */
    public function show(SolicitacaoLai $solicitacao): View
    {
        $solicitacao->load(['historicos' => fn ($q) => $q->latest('created_at'), 'respondente']);

        return view('tenant.solicitacoes-lai.show', compact('solicitacao'));
    }

    /**
     * Marcar solicitacao como em analise.
     */
    public function analisar(Request $request, SolicitacaoLai $solicitacao): RedirectResponse
    {
        try {
            SolicitacaoLaiService::analisar($solicitacao, $request->user(), $request->ip());

            return redirect()
                ->route('tenant.solicitacoes-lai.show', $solicitacao)
                ->with('success', 'Solicitacao marcada como em analise.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Registrar resposta a solicitacao.
     */
    public function responder(Request $request, SolicitacaoLai $solicitacao): RedirectResponse
    {
        $request->validate([
            'resposta' => 'required|string|min:20|max:10000',
            'classificacao_resposta' => 'required|in:' . implode(',', array_column(ClassificacaoRespostaLai::cases(), 'value')),
        ]);

        try {
            $classificacao = ClassificacaoRespostaLai::from($request->classificacao_resposta);

            SolicitacaoLaiService::responder(
                $solicitacao,
                $request->resposta,
                $classificacao,
                $request->user(),
                $request->ip()
            );

            return redirect()
                ->route('tenant.solicitacoes-lai.show', $solicitacao)
                ->with('success', 'Resposta registrada com sucesso.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Prorrogar prazo da solicitacao (max 1 vez, +10 dias).
     */
    public function prorrogar(Request $request, SolicitacaoLai $solicitacao): RedirectResponse
    {
        $request->validate([
            'justificativa_prorrogacao' => 'required|string|min:20|max:2000',
        ]);

        try {
            SolicitacaoLaiService::prorrogar(
                $solicitacao,
                $request->justificativa_prorrogacao,
                $request->user(),
                $request->ip()
            );

            return redirect()
                ->route('tenant.solicitacoes-lai.show', $solicitacao)
                ->with('success', 'Prazo prorrogado por 10 dias.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Indeferir solicitacao com justificativa.
     */
    public function indeferir(Request $request, SolicitacaoLai $solicitacao): RedirectResponse
    {
        $request->validate([
            'resposta' => 'required|string|min:20|max:10000',
            'classificacao_resposta' => 'required|in:' . implode(',', array_column(ClassificacaoRespostaLai::cases(), 'value')),
        ]);

        try {
            $classificacao = ClassificacaoRespostaLai::from($request->classificacao_resposta);

            SolicitacaoLaiService::indeferir(
                $solicitacao,
                $request->resposta,
                $classificacao,
                $request->user(),
                $request->ip()
            );

            return redirect()
                ->route('tenant.solicitacoes-lai.show', $solicitacao)
                ->with('success', 'Solicitacao indeferida.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
