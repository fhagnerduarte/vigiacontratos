<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\ModalidadeContratacao;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Enums\TipoPagamento;
use App\Enums\CategoriaContrato;
use App\Enums\CategoriaServico;
use App\Enums\NivelRisco;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreContratoRequest;
use App\Http\Requests\Tenant\UpdateContratoRequest;
use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Services\ContratoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContratosController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contrato::with('fornecedor', 'secretaria', 'fiscalAtual')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('secretaria_id')) {
            $query->where('secretaria_id', $request->secretaria_id);
        }

        if ($request->filled('modalidade')) {
            $query->where('modalidade_contratacao', $request->modalidade);
        }

        if ($request->filled('nivel_risco')) {
            $query->where('nivel_risco', $request->nivel_risco);
        }

        $contratos = $query->paginate(25)->withQueryString();
        $secretarias = Secretaria::orderBy('nome')->get();

        return view('tenant.contratos.index', compact('contratos', 'secretarias'));
    }

    public function create(): View
    {
        $fornecedores = Fornecedor::orderBy('razao_social')->get();
        $secretarias = Secretaria::orderBy('nome')->get();

        return view('tenant.contratos.create', compact(
            'fornecedores',
            'secretarias',
        ));
    }

    public function store(StoreContratoRequest $request): RedirectResponse
    {
        $dados = $request->validated();

        // Separa dados do fiscal
        $dadosFiscal = [
            'fiscal_nome' => $dados['fiscal_nome'] ?? null,
            'fiscal_matricula' => $dados['fiscal_matricula'] ?? null,
            'fiscal_cargo' => $dados['fiscal_cargo'] ?? null,
            'fiscal_email' => $dados['fiscal_email'] ?? null,
        ];
        unset($dados['fiscal_nome'], $dados['fiscal_matricula'], $dados['fiscal_cargo'], $dados['fiscal_email']);

        // Checkbox prorrogacao_automatica
        $dados['prorrogacao_automatica'] = $request->boolean('prorrogacao_automatica');

        ContratoService::criar($dados, $dadosFiscal, $request->user(), $request->ip());

        return redirect()->route('tenant.contratos.index')
            ->with('success', 'Contrato cadastrado com sucesso.');
    }

    public function show(Contrato $contrato): View
    {
        $contrato->load([
            'fornecedor',
            'secretaria',
            'fiscalAtual',
            'fiscais' => fn ($q) => $q->orderBy('data_inicio', 'desc'),
            'execucoesFinanceiras' => fn ($q) => $q->orderBy('data_execucao', 'desc'),
            'execucoesFinanceiras.registrador',
            'documentos',
            'historicoAlteracoes' => fn ($q) => $q->orderBy('created_at', 'desc'),
            'historicoAlteracoes.user',
        ]);

        return view('tenant.contratos.show', compact('contrato'));
    }

    public function edit(Contrato $contrato): View|RedirectResponse
    {
        // RN-006: Contrato vencido nao pode ser editado
        if ($contrato->status === StatusContrato::Vencido) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Contrato vencido nao pode ser editado (RN-006). Para alterar, crie um aditivo ou novo contrato.');
        }

        $contrato->load('fornecedor', 'secretaria');
        $fornecedores = Fornecedor::orderBy('razao_social')->get();
        $secretarias = Secretaria::orderBy('nome')->get();

        return view('tenant.contratos.edit', compact('contrato', 'fornecedores', 'secretarias'));
    }

    public function update(UpdateContratoRequest $request, Contrato $contrato): RedirectResponse
    {
        $dados = $request->validated();
        $dados['prorrogacao_automatica'] = $request->boolean('prorrogacao_automatica');

        ContratoService::atualizar($contrato, $dados, $request->user(), $request->ip());

        return redirect()->route('tenant.contratos.show', $contrato)
            ->with('success', 'Contrato atualizado com sucesso.');
    }

    public function destroy(Contrato $contrato): RedirectResponse
    {
        $contrato->delete();

        return redirect()->route('tenant.contratos.index')
            ->with('success', 'Contrato removido com sucesso.');
    }
}
