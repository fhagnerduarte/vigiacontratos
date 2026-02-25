<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SolicitacaoLai;
use App\Services\SolicitacaoLaiService;
use Illuminate\Http\Request;

class SolicitacaoLaiPublicController extends Controller
{
    /**
     * Formulario publico para nova solicitacao LAI.
     */
    public function create(string $slug)
    {
        $tenant = app('tenant');

        return view('portal.lai.create', compact('tenant'));
    }

    /**
     * Registra nova solicitacao LAI (sem autenticacao).
     */
    public function store(Request $request, string $slug)
    {
        $validated = $request->validate([
            'nome_solicitante' => 'required|string|max:255',
            'email_solicitante' => 'required|email|max:255',
            'cpf_solicitante' => 'required|string|max:14',
            'telefone_solicitante' => 'nullable|string|max:20',
            'assunto' => 'required|string|max:255',
            'descricao' => 'required|string|min:20|max:5000',
        ]);

        $solicitacao = SolicitacaoLaiService::criar($validated);

        return redirect()
            ->route('portal.lai.show', [$slug, $solicitacao->protocolo])
            ->with('success', "Solicitacao registrada com sucesso! Protocolo: {$solicitacao->protocolo}");
    }

    /**
     * Formulario de consulta por protocolo.
     */
    public function consultar(string $slug)
    {
        $tenant = app('tenant');

        return view('portal.lai.consultar', compact('tenant'));
    }

    /**
     * Exibe status publico da solicitacao por protocolo.
     */
    public function show(Request $request, string $slug, string $protocolo)
    {
        $tenant = app('tenant');

        $query = SolicitacaoLai::where('protocolo', $protocolo);

        // Verificacao de email para privacidade
        if ($request->has('email')) {
            $query->where('email_solicitante', $request->email);
        }

        $solicitacao = $query->first();

        if (!$solicitacao) {
            return redirect()
                ->route('portal.lai.consultar', $slug)
                ->with('error', 'Solicitacao nao encontrada. Verifique o protocolo e o email informados.');
        }

        $solicitacao->load('historicos');

        return view('portal.lai.show', compact('tenant', 'solicitacao'));
    }
}
