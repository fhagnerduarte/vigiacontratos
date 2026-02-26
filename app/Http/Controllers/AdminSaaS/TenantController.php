<?php

namespace App\Http\Controllers\AdminSaaS;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSaaS\StoreTenantRequest;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function updateBranding(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'logo' => ['nullable', 'image', 'max:2048'],
            'cor_primaria' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cor_secundaria' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cep' => ['nullable', 'string', 'max:9'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'size:2'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'email_contato' => ['nullable', 'email', 'max:255'],
            'horario_atendimento' => ['nullable', 'string', 'max:100'],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'gestor_nome' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('logo')) {
            if ($tenant->logo_path) {
                Storage::disk('s3')->delete($tenant->logo_path);
            }
            $path = $request->file('logo')->store("tenants/{$tenant->slug}/branding", 's3');
            $validated['logo_path'] = $path;
        } elseif ($request->input('remover_logo') === '1' && $tenant->logo_path) {
            Storage::disk('s3')->delete($tenant->logo_path);
            $validated['logo_path'] = null;
        }

        unset($validated['logo'], $validated['remover_logo']);
        $tenant->update($validated);

        return back()->with('success', 'Configuracoes do portal atualizadas com sucesso.');
    }

    public function showLogo(Tenant $tenant)
    {
        if (! $tenant->logo_path || ! Storage::disk('s3')->exists($tenant->logo_path)) {
            abort(404);
        }

        return Storage::disk('s3')->response($tenant->logo_path);
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
