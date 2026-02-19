<?php

namespace App\Http\Controllers\AdminSaaS;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSaaS\StoreTenantRequest;
use App\Models\Tenant;
use App\Services\TenantService;

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
}
