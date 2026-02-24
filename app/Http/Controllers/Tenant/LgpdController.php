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
use Illuminate\Http\Request;
use Illuminate\View\View;

class LgpdController extends Controller
{
    public function index(): View
    {
        // Registros derivados de processamento manual (nao-anonimizacao com status processado)
        // Estes sao duplicatas append-only — ocultar da listagem para mostrar apenas o original
        $idsDerivados = LogLgpdSolicitacao::where('status', 'processado')
            ->whereNot('tipo_solicitacao', TipoSolicitacaoLGPD::Anonimizacao->value)
            ->pluck('id');

        $solicitacoes = LogLgpdSolicitacao::whereNotIn('id', $idsDerivados)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        // Buscar registros de processamento para marcar quais pendentes ja foram processados
        $processados = LogLgpdSolicitacao::where('status', 'processado')
            ->whereNot('tipo_solicitacao', TipoSolicitacaoLGPD::Anonimizacao->value)
            ->get(['entidade_tipo', 'entidade_id', 'tipo_solicitacao', 'data_solicitacao']);

        $solicitacoes->getCollection()->transform(function ($solicitacao) use ($processados) {
            $solicitacao->jaProcessado = $solicitacao->status === 'processado' || $processados->contains(function ($p) use ($solicitacao) {
                return $p->entidade_tipo === $solicitacao->entidade_tipo
                    && $p->entidade_id === $solicitacao->entidade_id
                    && $p->tipo_solicitacao === $solicitacao->tipo_solicitacao
                    && $p->data_solicitacao == $solicitacao->data_solicitacao;
            });

            return $solicitacao;
        });

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
        // Verificar se ja existe registro de processamento para esta solicitacao
        $jaProcessado = $solicitacao->status === 'processado' || LogLgpdSolicitacao::where('entidade_tipo', $solicitacao->entidade_tipo)
            ->where('entidade_id', $solicitacao->entidade_id)
            ->where('tipo_solicitacao', $solicitacao->tipo_solicitacao->value)
            ->where('status', 'processado')
            ->where('data_solicitacao', $solicitacao->data_solicitacao)
            ->exists();

        return view('tenant.lgpd.show', compact('solicitacao', 'jaProcessado'));
    }

    public function processar(Request $request, LogLgpdSolicitacao $solicitacao): RedirectResponse
    {
        // Verificar se ja foi processado (registro original ou registro vinculado)
        $jaProcessado = $solicitacao->status === 'processado' || LogLgpdSolicitacao::where('entidade_tipo', $solicitacao->entidade_tipo)
            ->where('entidade_id', $solicitacao->entidade_id)
            ->where('tipo_solicitacao', $solicitacao->tipo_solicitacao->value)
            ->where('status', 'processado')
            ->where('data_solicitacao', $solicitacao->data_solicitacao)
            ->exists();

        if ($jaProcessado) {
            return redirect()->route('tenant.lgpd.show', $solicitacao)
                ->with('error', 'Esta solicitacao ja foi processada.');
        }

        $request->validate([
            'observacao' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'observacao.required' => 'A observacao do processamento e obrigatoria.',
            'observacao.min' => 'A observacao deve ter pelo menos 10 caracteres.',
        ]);

        // Tabela append-only: criar novo registro com status processado
        LogLgpdSolicitacao::create([
            'tipo_solicitacao' => $solicitacao->tipo_solicitacao->value,
            'entidade_tipo' => $solicitacao->entidade_tipo,
            'entidade_id' => $solicitacao->entidade_id,
            'solicitante' => $solicitacao->solicitante,
            'justificativa' => $request->input('observacao'),
            'status' => 'processado',
            'executado_por' => auth()->id(),
            'data_solicitacao' => $solicitacao->data_solicitacao,
            'data_execucao' => now(),
        ]);

        return redirect()->route('tenant.lgpd.show', $solicitacao)
            ->with('success', 'Solicitacao LGPD processada com sucesso.');
    }
}
