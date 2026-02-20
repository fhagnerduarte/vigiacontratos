<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissaoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissoesController extends Controller
{
    public function index(Role $role): View
    {
        $permissions = Permission::orderBy('grupo')->orderBy('nome')->get()->groupBy('grupo');
        $rolePermissionIds = $role->permissions()->pluck('permissions.id')->toArray();

        return view('tenant.permissoes.index', compact('role', 'permissions', 'rolePermissionIds'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:tenant.permissions,id'],
        ]);

        PermissaoService::sincronizarPermissoesRole($role, $validated['permissions'] ?? []);

        return redirect()->route('tenant.permissoes.index', $role)
            ->with('success', 'Permissoes do perfil atualizadas com sucesso.');
    }
}
