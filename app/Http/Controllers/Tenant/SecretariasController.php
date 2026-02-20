<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSecretariaRequest;
use App\Http\Requests\Tenant\UpdateSecretariaRequest;
use App\Models\Secretaria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SecretariasController extends Controller
{
    public function index(): View
    {
        $secretarias = Secretaria::orderBy('nome')->paginate(25);

        return view('tenant.secretarias.index', compact('secretarias'));
    }

    public function create(): View
    {
        return view('tenant.secretarias.create');
    }

    public function store(StoreSecretariaRequest $request): RedirectResponse
    {
        Secretaria::create($request->validated());

        return redirect()->route('tenant.secretarias.index')
            ->with('success', 'Secretaria cadastrada com sucesso.');
    }

    public function edit(Secretaria $secretaria): View
    {
        return view('tenant.secretarias.edit', compact('secretaria'));
    }

    public function update(UpdateSecretariaRequest $request, Secretaria $secretaria): RedirectResponse
    {
        $secretaria->update($request->validated());

        return redirect()->route('tenant.secretarias.index')
            ->with('success', 'Secretaria atualizada com sucesso.');
    }

    public function destroy(Secretaria $secretaria): RedirectResponse
    {
        $secretaria->delete();

        return redirect()->route('tenant.secretarias.index')
            ->with('success', 'Secretaria removida com sucesso.');
    }
}
