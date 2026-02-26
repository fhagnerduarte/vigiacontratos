<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\ModalidadeContratacao;
use App\Enums\RegimeExecucao;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
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
use App\Models\Servidor;
use App\Services\ContratoService;
use App\Services\DocumentoService;
use App\Services\ChecklistService;
use App\Services\FiscalService;
use App\Services\OcorrenciaService;
use App\Services\RelatorioFiscalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContratosController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Contrato::class);

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
        $this->authorize('create', Contrato::class);

        $fornecedores = Fornecedor::orderBy('razao_social')->get();
        $secretarias = Secretaria::orderBy('nome')->get();
        $servidores = Servidor::where('is_ativo', true)->orderBy('nome')->get();

        return view('tenant.contratos.create', compact(
            'fornecedores',
            'secretarias',
            'servidores',
        ));
    }

    public function store(StoreContratoRequest $request): RedirectResponse
    {
        $this->authorize('create', Contrato::class);

        $dados = $request->validated();

        // Separa dados do fiscal titular
        $dadosFiscal = [
            'servidor_id' => $dados['fiscal_servidor_id'] ?? null,
            'portaria_designacao' => $dados['portaria_designacao'] ?? null,
        ];
        unset($dados['fiscal_servidor_id'], $dados['portaria_designacao']);

        // Separa dados do fiscal substituto (opcional)
        $dadosFiscalSubstituto = null;
        if (! empty($dados['fiscal_substituto_servidor_id'])) {
            $dadosFiscalSubstituto = [
                'servidor_id' => $dados['fiscal_substituto_servidor_id'],
                'portaria_designacao' => $dadosFiscal['portaria_designacao'] ?? null,
            ];
        }
        unset($dados['fiscal_substituto_servidor_id']);

        // Checkbox prorrogação automática
        $dados['prorrogacao_automatica'] = $request->boolean('prorrogacao_automatica');

        // Checkbox publicado_portal
        $dados['publicado_portal'] = $request->boolean('publicado_portal');

        $contrato = ContratoService::criar($dados, $dadosFiscal, $request->user(), $request->ip());

        // Designar fiscal substituto se informado (Lei 14.133 art. 117)
        if ($dadosFiscalSubstituto) {
            FiscalService::designarSubstituto($contrato, $dadosFiscalSubstituto);
        }

        return redirect()->route('tenant.contratos.index')
            ->with('success', 'Contrato cadastrado com sucesso.');
    }

    public function show(Contrato $contrato): View
    {
        $this->authorize('view', $contrato);

        $contrato->load([
            'fornecedor',
            'secretaria',
            'gestor',
            'fiscalAtual.servidor',
            'fiscalSubstituto.servidor',
            'fiscais' => fn ($q) => $q->orderBy('data_inicio', 'desc'),
            'execucoesFinanceiras' => fn ($q) => $q->orderBy('data_execucao', 'desc'),
            'execucoesFinanceiras.registrador',
            'documentos' => fn ($q) => $q->orderBy('tipo_documento')->orderBy('versao', 'desc'),
            'documentos.uploader',
            'historicoAlteracoes' => fn ($q) => $q->orderBy('created_at', 'desc'),
            'historicoAlteracoes.user',
            'aditivos' => fn ($q) => $q->orderBy('numero_sequencial'),
            'ocorrencias' => fn ($q) => $q->orderBy('data_ocorrencia', 'desc'),
            'ocorrencias.fiscal',
            'ocorrencias.registradoPor',
            'ocorrencias.resolvidaPor',
            'relatoriosFiscais' => fn ($q) => $q->orderBy('periodo_fim', 'desc'),
            'relatoriosFiscais.fiscal',
            'relatoriosFiscais.registradoPor',
        ]);

        // Dados para aba Documentos (Módulo 5)
        $checklistObrigatorio = DocumentoService::verificarChecklist($contrato);
        $tiposDocumento = TipoDocumentoContratual::cases();

        // Agrupar documentos por tipo (label) para exibição
        $documentosPorTipo = $contrato->documentos->groupBy(
            fn ($doc) => $doc->tipo_documento->label()
        );

        // Conformidade por fase contratual (IMP-050)
        $conformidadeFases = ChecklistService::calcularConformidadeGeral($contrato);
        $percentualGlobal = ChecklistService::calcularPercentualGlobal($contrato);

        // Resumo ocorrências e relatórios fiscais (IMP-054)
        $resumoOcorrencias = OcorrenciaService::resumo($contrato);
        $resumoRelatoriosFiscais = RelatorioFiscalService::resumo($contrato);

        // Servidores ativos para form de designar/trocar fiscal
        $servidores = Servidor::where('is_ativo', true)->orderBy('nome')->get();

        return view('tenant.contratos.show', compact(
            'contrato',
            'checklistObrigatorio',
            'tiposDocumento',
            'documentosPorTipo',
            'conformidadeFases',
            'percentualGlobal',
            'resumoOcorrencias',
            'resumoRelatoriosFiscais',
            'servidores',
        ));
    }

    public function edit(Contrato $contrato): View|RedirectResponse
    {
        $this->authorize('update', $contrato);

        // RN-006: Contrato vencido não pode ser editado
        if ($contrato->status === StatusContrato::Vencido) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Contrato vencido não pode ser editado (RN-006). Para alterar, crie um aditivo ou novo contrato.');
        }

        $contrato->load('fornecedor', 'secretaria', 'gestor');
        $fornecedores = Fornecedor::orderBy('razao_social')->get();
        $secretarias = Secretaria::orderBy('nome')->get();
        $servidores = Servidor::where('is_ativo', true)->orderBy('nome')->get();

        return view('tenant.contratos.edit', compact('contrato', 'fornecedores', 'secretarias', 'servidores'));
    }

    public function update(UpdateContratoRequest $request, Contrato $contrato): RedirectResponse
    {
        $this->authorize('update', $contrato);

        // RN-006: Defesa em profundidade
        if ($contrato->status === StatusContrato::Vencido) {
            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('error', 'Contrato vencido não pode ser editado (RN-006). Para alterar, crie um aditivo retroativo ou encerre formalmente.');
        }

        $dados = $request->validated();
        $dados['prorrogacao_automatica'] = $request->boolean('prorrogacao_automatica');
        $dados['publicado_portal'] = $request->boolean('publicado_portal');

        ContratoService::atualizar($contrato, $dados, $request->user(), $request->ip());

        return redirect()->route('tenant.contratos.show', $contrato)
            ->with('success', 'Contrato atualizado com sucesso.');
    }

    public function destroy(Contrato $contrato): RedirectResponse
    {
        $this->authorize('delete', $contrato);

        $contrato->delete();

        return redirect()->route('tenant.contratos.index')
            ->with('success', 'Contrato removido com sucesso.');
    }
}
