<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreRoleRequest;
use App\Http\Requests\Tenant\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RolesController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users')
            ->orderByDesc('is_padrao')
            ->orderBy('descricao')
            ->paginate(25);

        return view('tenant.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('tenant.roles.create');
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        Role::create($request->validated());

        return redirect()->route('tenant.roles.index')
            ->with('success', 'Perfil criado com sucesso.');
    }

    public function edit(Role $role): View
    {
        return view('tenant.roles.edit', compact('role'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        // Perfis padrao nao podem ter nome alterado
        if ($role->is_padrao) {
            unset($data['nome']);
        }

        $role->update($data);

        return redirect()->route('tenant.roles.index')
            ->with('success', 'Perfil atualizado com sucesso.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_padrao) {
            return redirect()->route('tenant.roles.index')
                ->with('error', 'Perfis padrao do sistema nao podem ser removidos.');
        }

        if ($role->users()->exists()) {
            return redirect()->route('tenant.roles.index')
                ->with('error', 'Nao e possivel remover um perfil com usuarios vinculados.');
        }

        $role->delete();

        return redirect()->route('tenant.roles.index')
            ->with('success', 'Perfil removido com sucesso.');
    }
}
