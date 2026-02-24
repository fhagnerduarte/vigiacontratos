<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\TipoSolicitacaoLGPD;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreLgpdSolicitacaoRequest;
use App\Models\Fiscal;
use App\Models\Fornecedor;
use App\Models\LogLgpdSolicitacao;
use App\Models\Servidor;
use App\Models\User;
use App\Services\LGPDService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LgpdController extends Controller
{
    public function index(): View
    {
        $solicitacoes = LogLgpdSolicitacao::orderBy('created_at', 'desc')
            ->paginate(25);

        return view('tenant.lgpd.index', compact('solicitacoes'));
    }

    public function create(): View
    {
        $tipos = TipoSolicitacaoLGPD::cases();
        $fornecedores = Fornecedor::orderBy('razao_social')->get(['id', 'razao_social', 'cnpj']);
        $fiscais = Fiscal::orderBy('nome')->get(['id', 'nome', 'matricula']);
        $servidores = Servidor::orderBy('nome')->get(['id', 'nome', 'cpf']);
        $usuarios = User::where('is_ativo', false)->orderBy('nome')->get(['id', 'nome', 'email']);

        return view('tenant.lgpd.create', compact(
            'tipos',
            'fornecedores',
            'fiscais',
            'servidores',
            'usuarios',
        ));
    }

    public function store(StoreLgpdSolicitacaoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $modelClass = match ($data['entidade_tipo']) {
            'fornecedor' => Fornecedor::class,
            'fiscal' => Fiscal::class,
            'servidor' => Servidor::class,
            'usuario' => User::class,
        };

        $entidade = $modelClass::findOrFail($data['entidade_id']);
        $tipoSolicitacao = TipoSolicitacaoLGPD::from($data['tipo_solicitacao']);

        // Anonimizacao e processada automaticamente via LGPDService
        if ($tipoSolicitacao === TipoSolicitacaoLGPD::Anonimizacao) {
            try {
                $method = match ($data['entidade_tipo']) {
                    'fornecedor' => 'anonimizarFornecedor',
                    'fiscal' => 'anonimizarFiscal',
                    'servidor' => 'anonimizarServidor',
                    'usuario' => 'anonimizarUsuario',
                };

                LGPDService::$method(
                    $entidade,
                    auth()->user()->nome,
                    $data['justificativa'],
                    auth()->user(),
                );

                return redirect()->route('tenant.lgpd.index')
                    ->with('success', 'Solicitacao LGPD processada com sucesso. Dados anonimizados.');
            } catch (\RuntimeException $e) {
                return redirect()->back()->withInput()
                    ->with('error', $e->getMessage());
            }
        }

        // Demais tipos: registrar como pendente para processamento manual
        LogLgpdSolicitacao::create([
            'tipo_solicitacao' => $tipoSolicitacao->value,
            'entidade_tipo' => $modelClass,
            'entidade_id' => $data['entidade_id'],
            'solicitante' => auth()->user()->nome,
            'justificativa' => $data['justificativa'],
            'status' => 'pendente',
            'executado_por' => auth()->id(),
            'data_solicitacao' => now(),
        ]);

        return redirect()->route('tenant.lgpd.index')
            ->with('success', 'Solicitacao LGPD registrada. Aguardando processamento.');
    }

    public function show(LogLgpdSolicitacao $solicitacao): View
    {
        return view('tenant.lgpd.show', compact('solicitacao'));
    }
}
