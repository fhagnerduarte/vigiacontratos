<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreServidorRequest;
use App\Http\Requests\Tenant\UpdateServidorRequest;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Rules\CpfValido;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServidoresController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Servidor::class);

        $servidores = Servidor::with('secretaria')
            ->orderBy('nome')
            ->paginate(25);

        return view('tenant.servidores.index', compact('servidores'));
    }

    public function create(): View
    {
        $this->authorize('create', Servidor::class);

        $secretarias = Secretaria::orderBy('nome')->get();

        return view('tenant.servidores.create', compact('secretarias'));
    }

    public function store(StoreServidorRequest $request): RedirectResponse
    {
        $this->authorize('create', Servidor::class);

        $data = $request->validated();

        if (! empty($data['cpf'])) {
            $data['cpf'] = CpfValido::formatarCpf($data['cpf']);
        }

        $data['is_ativo'] = $request->boolean('is_ativo');

        Servidor::create($data);

        return redirect()->route('tenant.servidores.index')
            ->with('success', 'Servidor cadastrado com sucesso.');
    }

    public function edit(Servidor $servidor): View
    {
        $this->authorize('update', $servidor);

        $secretarias = Secretaria::orderBy('nome')->get();

        return view('tenant.servidores.edit', compact('servidor', 'secretarias'));
    }

    public function update(UpdateServidorRequest $request, Servidor $servidor): RedirectResponse
    {
        $this->authorize('update', $servidor);

        $data = $request->validated();

        if (! empty($data['cpf'])) {
            $data['cpf'] = CpfValido::formatarCpf($data['cpf']);
        }

        $data['is_ativo'] = $request->boolean('is_ativo');

        $servidor->update($data);

        return redirect()->route('tenant.servidores.index')
            ->with('success', 'Servidor atualizado com sucesso.');
    }

    public function destroy(Servidor $servidor): RedirectResponse
    {
        $this->authorize('delete', $servidor);

        $servidor->delete();

        return redirect()->route('tenant.servidores.index')
            ->with('success', 'Servidor removido com sucesso.');
    }
}
