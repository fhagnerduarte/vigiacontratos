<?php

namespace App\Http\Controllers\AdminSaaS;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSaaS\StoreTenantRequest;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantService $tenantService
    ) {}

    public function index()
    {
        $tenants = Tenant::orderBy('nome')->paginate(25);

        return view('admin-saas.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('admin-saas.tenants.create');
    }

    public function store(StoreTenantRequest $request)
    {
        $tenant = $this->tenantService->createTenant($request->validated());

        return redirect()->route('admin-saas.tenants.show', $tenant)
            ->with('success', "Tenant '{$tenant->nome}' criado com sucesso.");
    }

    public function show(Tenant $tenant)
    {
        return view('admin-saas.tenants.show', compact('tenant'));
    }

    public function activate(Tenant $tenant)
    {
        $this->tenantService->activateTenant($tenant);

        return back()->with('success', "Tenant '{$tenant->nome}' ativado.");
    }

    public function deactivate(Tenant $tenant)
    {
        $this->tenantService->deactivateTenant($tenant);

        return back()->with('success', "Tenant '{$tenant->nome}' desativado.");
    }

    public function updateMfaConfig(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'mfa_habilitado' => ['required', 'boolean'],
            'mfa_modo' => ['required', 'in:desativado,opcional,obrigatorio'],
            'mfa_perfis_obrigatorios' => ['nullable', 'array'],
            'mfa_perfis_obrigatorios.*' => ['string', 'in:administrador_geral,controladoria,secretario,gestor_contrato,fiscal_contrato,financeiro,procuradoria,gabinete'],
        ], [
            'mfa_habilitado.required' => 'Informe se o MFA está habilitado.',
            'mfa_modo.required' => 'Selecione o modo do MFA.',
            'mfa_modo.in' => 'Modo inválido.',
        ]);

        // Se modo é desativado, forçar habilitado = false
        if ($validated['mfa_modo'] === 'desativado') {
            $validated['mfa_habilitado'] = false;
            $validated['mfa_perfis_obrigatorios'] = null;
        }

        // Se modo é obrigatório, limpar perfis específicos (todos são obrigatórios)
        if ($validated['mfa_modo'] === 'obrigatorio') {
            $validated['mfa_habilitado'] = true;
            $validated['mfa_perfis_obrigatorios'] = null;
        }

        // Se modo é opcional, mfa está habilitado
        if ($validated['mfa_modo'] === 'opcional') {
            $validated['mfa_habilitado'] = true;
        }

        $tenant->update($validated);

        return back()->with('success', 'Configurações de MFA atualizadas com sucesso.');
    }
}
