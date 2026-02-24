<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreFornecedorRequest;
use App\Http\Requests\Tenant\UpdateFornecedorRequest;
use App\Models\Fornecedor;
use App\Services\FornecedorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FornecedoresController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Fornecedor::class);

        $fornecedores = Fornecedor::orderBy('razao_social')->paginate(25);

        return view('tenant.fornecedores.index', compact('fornecedores'));
    }

    public function create(): View
    {
        $this->authorize('create', Fornecedor::class);

        return view('tenant.fornecedores.create');
    }

    public function store(StoreFornecedorRequest $request): RedirectResponse
    {
        $this->authorize('create', Fornecedor::class);

        $data = $request->validated();
        $data['cnpj'] = FornecedorService::formatarCnpj($data['cnpj']);

        Fornecedor::create($data);

        return redirect()->route('tenant.fornecedores.index')
            ->with('success', 'Fornecedor cadastrado com sucesso.');
    }

    public function edit(Fornecedor $fornecedor): View
    {
        $this->authorize('update', $fornecedor);

        return view('tenant.fornecedores.edit', compact('fornecedor'));
    }

    public function update(UpdateFornecedorRequest $request, Fornecedor $fornecedor): RedirectResponse
    {
        $this->authorize('update', $fornecedor);

        $data = $request->validated();
        $data['cnpj'] = FornecedorService::formatarCnpj($data['cnpj']);

        $fornecedor->update($data);

        return redirect()->route('tenant.fornecedores.index')
            ->with('success', 'Fornecedor atualizado com sucesso.');
    }

    public function destroy(Fornecedor $fornecedor): RedirectResponse
    {
        $this->authorize('delete', $fornecedor);

        $fornecedor->delete();

        return redirect()->route('tenant.fornecedores.index')
            ->with('success', 'Fornecedor removido com sucesso.');
    }
}
