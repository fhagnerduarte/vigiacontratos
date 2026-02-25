<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\EtapaEncerramento;
use App\Enums\StatusContrato;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Services\EncerramentoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EncerramentosController extends Controller
{
    /**
     * Exibe o wizard de encerramento (ou inicia o processo).
     */
    public function show(Contrato $contrato): View|RedirectResponse
    {
        if (!in_array($contrato->status, [StatusContrato::Vigente, StatusContrato::Vencido, StatusContrato::Encerrado])) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Este contrato nao pode ser encerrado no status atual.');
        }

        $contrato->load([
            'encerramento',
            'encerramento.verificadorFinanceiro',
            'encerramento.registradorTermoProvisorio',
            'encerramento.avaliadorFiscal',
            'encerramento.registradorTermoDefinitivo',
            'encerramento.registradorQuitacao',
            'fornecedor',
            'fiscalAtual',
        ]);

        $etapas = EtapaEncerramento::cases();

        return view('tenant.encerramentos.show', compact('contrato', 'etapas'));
    }

    /**
     * Inicia o processo de encerramento.
     */
    public function iniciar(Request $request, Contrato $contrato): RedirectResponse
    {
        try {
            EncerramentoService::iniciar($contrato, $request->user(), $request->ip());

            return redirect()->route('tenant.contratos.encerramento.show', $contrato)
                ->with('success', 'Processo de encerramento iniciado com sucesso.');
        } catch (\RuntimeException $e) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Etapa 1: Verificacao Financeira.
     */
    public function verificarFinanceiro(Request $request, Contrato $contrato): RedirectResponse
    {
        $request->validate([
            'verificacao_financeira_ok' => 'required|boolean',
            'verificacao_financeira_obs' => 'nullable|string|max:2000',
        ]);

        $encerramento = $contrato->encerramento;

        if (!$encerramento) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Processo de encerramento nao iniciado.');
        }

        try {
            EncerramentoService::verificarFinanceiro(
                $encerramento,
                (bool) $request->verificacao_financeira_ok,
                $request->verificacao_financeira_obs,
                $request->user(),
                $request->ip()
            );

            return redirect()->route('tenant.contratos.encerramento.show', $contrato)
                ->with('success', 'Verificacao financeira registrada.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Etapa 2: Termo de Recebimento Provisorio.
     */
    public function termoProvisorio(Request $request, Contrato $contrato): RedirectResponse
    {
        $request->validate([
            'termo_provisorio_prazo_dias' => 'required|integer|min:1|max:365',
        ]);

        $encerramento = $contrato->encerramento;

        if (!$encerramento) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Processo de encerramento nao iniciado.');
        }

        try {
            EncerramentoService::registrarTermoProvisorio(
                $encerramento,
                (int) $request->termo_provisorio_prazo_dias,
                $request->user(),
                $request->ip()
            );

            return redirect()->route('tenant.contratos.encerramento.show', $contrato)
                ->with('success', 'Termo de recebimento provisorio registrado.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Etapa 3: Avaliacao do Fiscal.
     */
    public function avaliacaoFiscal(Request $request, Contrato $contrato): RedirectResponse
    {
        $request->validate([
            'avaliacao_fiscal_nota' => 'required|numeric|min:1|max:10',
            'avaliacao_fiscal_obs' => 'nullable|string|max:2000',
        ]);

        $encerramento = $contrato->encerramento;

        if (!$encerramento) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Processo de encerramento nao iniciado.');
        }

        try {
            EncerramentoService::registrarAvaliacaoFiscal(
                $encerramento,
                (float) $request->avaliacao_fiscal_nota,
                $request->avaliacao_fiscal_obs,
                $request->user(),
                $request->ip()
            );

            return redirect()->route('tenant.contratos.encerramento.show', $contrato)
                ->with('success', 'Avaliacao fiscal registrada.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Etapa 4: Termo de Recebimento Definitivo.
     */
    public function termoDefinitivo(Request $request, Contrato $contrato): RedirectResponse
    {
        $encerramento = $contrato->encerramento;

        if (!$encerramento) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Processo de encerramento nao iniciado.');
        }

        try {
            EncerramentoService::registrarTermoDefinitivo(
                $encerramento,
                $request->user(),
                $request->ip()
            );

            return redirect()->route('tenant.contratos.encerramento.show', $contrato)
                ->with('success', 'Termo de recebimento definitivo registrado.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Etapa 5: Quitacao (finaliza o encerramento).
     */
    public function quitacao(Request $request, Contrato $contrato): RedirectResponse
    {
        $request->validate([
            'quitacao_obs' => 'nullable|string|max:2000',
        ]);

        $encerramento = $contrato->encerramento;

        if (!$encerramento) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Processo de encerramento nao iniciado.');
        }

        try {
            EncerramentoService::registrarQuitacao(
                $encerramento,
                $request->quitacao_obs,
                $request->user(),
                $request->ip()
            );

            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('success', 'Contrato encerrado formalmente com sucesso. Todos os alertas foram resolvidos.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
