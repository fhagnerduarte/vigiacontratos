<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\TipoContrato;
use App\Enums\TipoEventoAlerta;
use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\ConfiguracaoAlerta;
use App\Models\Secretaria;
use App\Services\AlertaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertasController extends Controller
{
    /**
     * Dashboard de alertas com indicadores e tabela filtrada (RN-055, RN-056).
     */
    public function index(Request $request): View
    {
        // Indicadores (RN-055)
        $indicadores = AlertaService::gerarIndicadoresDashboard();

        // Query de alertas com filtros (RN-056)
        $query = Alerta::with(['contrato.fornecedor', 'contrato.secretaria'])
            ->orderByRaw("FIELD(prioridade, 'urgente', 'atencao', 'informativo')")
            ->orderBy('data_vencimento');

        // Scope por secretaria: usuarios nao-estrategicos veem apenas alertas
        // de contratos vinculados as suas secretarias (RN-326).
        // O SecretariaScope do Contrato propaga-se automaticamente via whereHas.
        if (auth()->check() && ! auth()->user()->isPerfilEstrategico()) {
            $query->whereHas('contrato');
        }

        // Filtro: status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            // Default: mostrar nao-resolvidos
            $query->naoResolvidos();
        }

        // Filtro: prioridade (criticidade)
        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->input('prioridade'));
        }

        // Filtro: tipo_evento
        if ($request->filled('tipo_evento')) {
            $query->where('tipo_evento', $request->input('tipo_evento'));
        }

        // Filtro: secretaria
        if ($request->filled('secretaria_id')) {
            $query->whereHas('contrato', fn ($q) => $q->where('secretaria_id', $request->input('secretaria_id')));
        }

        // Filtro: tipo contrato
        if ($request->filled('tipo_contrato')) {
            $query->whereHas('contrato', fn ($q) => $q->where('tipo', $request->input('tipo_contrato')));
        }

        // Filtro: faixa de valor
        if ($request->filled('valor_min')) {
            $query->whereHas('contrato', fn ($q) => $q->where('valor_global', '>=', $request->input('valor_min')));
        }
        if ($request->filled('valor_max')) {
            $query->whereHas('contrato', fn ($q) => $q->where('valor_global', '<=', $request->input('valor_max')));
        }

        $alertas = $query->paginate(20)->withQueryString();

        // Dados para filtros
        $secretarias = Secretaria::orderBy('nome')->get();

        return view('tenant.alertas.index', compact(
            'indicadores',
            'alertas',
            'secretarias',
        ));
    }

    /**
     * Detalhes de um alerta.
     */
    public function show(Alerta $alerta): View
    {
        $alerta->load([
            'contrato.fornecedor',
            'contrato.secretaria',
            'contrato.fiscalAtual.servidor',
            'logNotificacoes',
            'visualizadoPor',
            'resolvidoPor',
        ]);

        // Marcar como visualizado ao acessar
        if (auth()->check()) {
            AlertaService::marcarVisualizado($alerta, auth()->user());
        }

        return view('tenant.alertas.show', compact('alerta'));
    }

    /**
     * Resolver manualmente um alerta.
     */
    public function resolver(Request $request, Alerta $alerta): RedirectResponse
    {
        try {
            AlertaService::resolverManualmente($alerta, $request->user());

            return redirect()->route('tenant.alertas.show', $alerta)
                ->with('success', 'Alerta resolvido com sucesso.');
        } catch (\RuntimeException $e) {
            return redirect()->route('tenant.alertas.show', $alerta)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Pagina de configuracoes de alertas.
     */
    public function configuracoes(): View
    {
        $configuracoes = ConfiguracaoAlerta::orderByDesc('dias_antecedencia')->get();

        return view('tenant.alertas.configuracoes', compact('configuracoes'));
    }

    /**
     * Salvar configuracoes de alertas.
     */
    public function salvarConfiguracoes(Request $request): RedirectResponse
    {
        $request->validate([
            'configuracoes' => ['required', 'array'],
            'configuracoes.*.id' => ['required', 'integer'],
            'configuracoes.*.is_ativo' => ['required'],
        ]);

        foreach ($request->input('configuracoes') as $config) {
            ConfiguracaoAlerta::where('id', $config['id'])->update([
                'is_ativo' => (bool) $config['is_ativo'],
            ]);
        }

        return redirect()->route('tenant.alertas.configuracoes')
            ->with('success', 'Configuracoes de alerta atualizadas com sucesso.');
    }
}
