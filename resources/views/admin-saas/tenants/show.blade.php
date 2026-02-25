@extends('layout.admin-saas')

@section('title', $tenant->nome)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">{{ $tenant->nome }}</h6>
    <a href="{{ route('admin-saas.tenants.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-12 py-8 radius-8">Voltar</a>
</div>

<div class="row gy-4">
    {{-- Dados do Tenant --}}
    <div class="col-lg-8">
        <div class="card radius-8 border-0">
            <div class="card-body p-24">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-secondary-light py-8" style="width: 200px;">ID</th>
                        <td class="py-8">{{ $tenant->id }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light py-8">Nome</th>
                        <td class="py-8">{{ $tenant->nome }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light py-8">Slug</th>
                        <td class="py-8"><code>{{ $tenant->slug }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light py-8">URL</th>
                        <td class="py-8"><code>{{ $tenant->slug }}.{{ config('app.domain', 'vigiacontratos.com.br') }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light py-8">Banco de Dados</th>
                        <td class="py-8"><code>{{ $tenant->database_name }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light py-8">Host do Banco</th>
                        <td class="py-8">{{ $tenant->database_host ?? 'Padrão (mesmo do master)' }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light py-8">Plano</th>
                        <td class="py-8">{{ ucfirst($tenant->plano) }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light py-8">Status</th>
                        <td class="py-8">
                            @if ($tenant->is_ativo)
                                <span class="bg-success-focus text-success-main px-16 py-6 radius-4 fw-medium text-sm">Ativo</span>
                            @else
                                <span class="bg-danger-focus text-danger-main px-16 py-6 radius-4 fw-medium text-sm">Inativo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light py-8">Criado em</th>
                        <td class="py-8">{{ $tenant->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light py-8">Atualizado em</th>
                        <td class="py-8">{{ $tenant->updated_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Ações --}}
    <div class="col-lg-4">
        <div class="card radius-8 border-0">
            <div class="card-header bg-base border-bottom py-16 px-24">
                <h6 class="fw-semibold mb-0 text-lg">Ações</h6>
            </div>
            <div class="card-body p-24">
                @if ($tenant->is_ativo)
                    <form action="{{ route('admin-saas.tenants.deactivate', $tenant) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger text-sm btn-sm px-24 py-12 radius-8 w-100">Desativar Tenant</button>
                    </form>
                @else
                    <form action="{{ route('admin-saas.tenants.activate', $tenant) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-success text-sm btn-sm px-24 py-12 radius-8 w-100">Ativar Tenant</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Configuração MFA --}}
    <div class="col-12">
        <div class="card radius-8 border-0">
            <div class="card-header bg-base border-bottom py-16 px-24">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="mdi:shield-lock-outline" class="text-primary-main text-xl"></iconify-icon>
                    <h6 class="fw-semibold mb-0 text-lg">Autenticação em Dois Fatores (MFA/2FA)</h6>
                </div>
            </div>
            <div class="card-body p-24">
                <form action="{{ route('admin-saas.tenants.mfa.update', $tenant) }}" method="POST" id="mfaConfigForm">
                    @csrf
                    @method('PUT')

                    <div class="row gy-4">
                        {{-- Modo MFA --}}
                        <div class="col-lg-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Modo do MFA</label>
                            <div class="d-flex flex-column gap-12">
                                <label class="d-flex align-items-start gap-10 p-16 border radius-8 cursor-pointer mfa-mode-option {{ !$tenant->mfa_habilitado || $tenant->mfa_modo === 'desativado' ? 'border-danger-main bg-danger-50' : '' }}"
                                       for="mfa_desativado">
                                    <input type="radio" name="mfa_modo" id="mfa_desativado" value="desativado"
                                           class="form-check-input mt-2"
                                           {{ !$tenant->mfa_habilitado || $tenant->mfa_modo === 'desativado' ? 'checked' : '' }}>
                                    <div>
                                        <span class="fw-semibold text-sm d-block">Desativado</span>
                                        <span class="text-secondary-light text-xs">Nenhum usuário do tenant poderá usar MFA. A autenticação será feita apenas com senha.</span>
                                    </div>
                                </label>

                                <label class="d-flex align-items-start gap-10 p-16 border radius-8 cursor-pointer mfa-mode-option {{ $tenant->mfa_habilitado && $tenant->mfa_modo === 'opcional' ? 'border-primary-main bg-primary-50' : '' }}"
                                       for="mfa_opcional">
                                    <input type="radio" name="mfa_modo" id="mfa_opcional" value="opcional"
                                           class="form-check-input mt-2"
                                           {{ $tenant->mfa_habilitado && $tenant->mfa_modo === 'opcional' ? 'checked' : '' }}>
                                    <div>
                                        <span class="fw-semibold text-sm d-block">Opcional (com perfis obrigatórios)</span>
                                        <span class="text-secondary-light text-xs">MFA habilitado para todos os usuários. Você pode definir quais perfis são <strong>obrigados</strong> a configurar. Os demais podem optar por usar.</span>
                                    </div>
                                </label>

                                <label class="d-flex align-items-start gap-10 p-16 border radius-8 cursor-pointer mfa-mode-option {{ $tenant->mfa_habilitado && $tenant->mfa_modo === 'obrigatorio' ? 'border-warning-main bg-warning-50' : '' }}"
                                       for="mfa_obrigatorio">
                                    <input type="radio" name="mfa_modo" id="mfa_obrigatorio" value="obrigatorio"
                                           class="form-check-input mt-2"
                                           {{ $tenant->mfa_habilitado && $tenant->mfa_modo === 'obrigatorio' ? 'checked' : '' }}>
                                    <div>
                                        <span class="fw-semibold text-sm d-block">Obrigatório para todos</span>
                                        <span class="text-secondary-light text-xs">Todos os usuários do tenant serão obrigados a configurar MFA antes de acessar o sistema.</span>
                                    </div>
                                </label>
                            </div>

                            <input type="hidden" name="mfa_habilitado" id="mfa_habilitado" value="{{ $tenant->mfa_habilitado ? '1' : '0' }}">
                        </div>

                        {{-- Perfis obrigatórios (visível apenas no modo opcional) --}}
                        <div class="col-lg-6" id="perfisObrigatoriosSection" style="{{ $tenant->mfa_habilitado && $tenant->mfa_modo === 'opcional' ? '' : 'display: none;' }}">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                                Perfis com MFA obrigatório
                                <span class="text-secondary-light fw-normal">(no modo opcional)</span>
                            </label>
                            <p class="text-secondary-light text-xs mb-12">Selecione quais perfis serão <strong>obrigados</strong> a configurar MFA. Os demais perfis poderão optar por ativar.</p>

                            @php
                                $perfis = [
                                    'administrador_geral' => 'Administrador Geral',
                                    'controladoria' => 'Controladoria Interna',
                                    'secretario' => 'Secretário Municipal',
                                    'gestor_contrato' => 'Gestor de Contrato',
                                    'fiscal_contrato' => 'Fiscal de Contrato',
                                    'financeiro' => 'Financeiro / Contabilidade',
                                    'procuradoria' => 'Procuradoria Jurídica',
                                    'gabinete' => 'Gabinete / Prefeito',
                                ];
                                $perfisObrigatorios = $tenant->mfa_perfis_obrigatorios ?? [];
                            @endphp

                            <div class="d-flex flex-column gap-8">
                                @foreach ($perfis as $valor => $label)
                                    <label class="d-flex align-items-center gap-8 p-10 border radius-8 cursor-pointer perfil-check-option">
                                        <input type="checkbox" name="mfa_perfis_obrigatorios[]" value="{{ $valor }}"
                                               class="form-check-input"
                                               {{ in_array($valor, $perfisObrigatorios) ? 'checked' : '' }}>
                                        <span class="text-sm">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Status atual --}}
                    <div class="mt-24 p-16 bg-neutral-50 radius-8">
                        <h6 class="fw-semibold text-sm mb-8">Status atual</h6>
                        <div class="d-flex flex-wrap gap-12">
                            <div class="d-flex align-items-center gap-6">
                                <iconify-icon icon="{{ $tenant->mfa_habilitado ? 'mdi:check-circle' : 'mdi:close-circle' }}"
                                              class="{{ $tenant->mfa_habilitado ? 'text-success-main' : 'text-danger-main' }} text-lg"></iconify-icon>
                                <span class="text-sm">MFA {{ $tenant->mfa_habilitado ? 'Habilitado' : 'Desabilitado' }}</span>
                            </div>
                            <span class="text-neutral-300">|</span>
                            <div class="d-flex align-items-center gap-6">
                                <iconify-icon icon="mdi:cog-outline" class="text-secondary-light text-lg"></iconify-icon>
                                <span class="text-sm">
                                    Modo:
                                    @if (!$tenant->mfa_habilitado || $tenant->mfa_modo === 'desativado')
                                        <span class="text-danger-main fw-medium">Desativado</span>
                                    @elseif ($tenant->mfa_modo === 'opcional')
                                        <span class="text-primary-main fw-medium">Opcional</span>
                                        @if (!empty($perfisObrigatorios))
                                            ({{ count($perfisObrigatorios) }} perfil(is) obrigatório(s))
                                        @endif
                                    @elseif ($tenant->mfa_modo === 'obrigatorio')
                                        <span class="text-warning-main fw-medium">Obrigatório para todos</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-24 d-flex gap-12">
                        <button type="submit" class="btn btn-primary text-sm btn-sm px-24 py-12 radius-8">
                            <iconify-icon icon="mdi:content-save-outline" class="text-lg me-4"></iconify-icon>
                            Salvar Configurações MFA
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="mfa_modo"]');
    const perfisSection = document.getElementById('perfisObrigatoriosSection');
    const mfaHabilitado = document.getElementById('mfa_habilitado');
    const options = document.querySelectorAll('.mfa-mode-option');

    function updateUI() {
        const selected = document.querySelector('input[name="mfa_modo"]:checked');
        if (!selected) return;

        options.forEach(function(opt) {
            opt.classList.remove('border-danger-main', 'bg-danger-50',
                                'border-primary-main', 'bg-primary-50',
                                'border-warning-main', 'bg-warning-50');
        });

        var parent = selected.closest('.mfa-mode-option');
        if (selected.value === 'desativado') {
            parent.classList.add('border-danger-main', 'bg-danger-50');
            mfaHabilitado.value = '0';
            perfisSection.style.display = 'none';
        } else if (selected.value === 'opcional') {
            parent.classList.add('border-primary-main', 'bg-primary-50');
            mfaHabilitado.value = '1';
            perfisSection.style.display = '';
        } else if (selected.value === 'obrigatorio') {
            parent.classList.add('border-warning-main', 'bg-warning-50');
            mfaHabilitado.value = '1';
            perfisSection.style.display = 'none';
        }
    }

    radios.forEach(function(radio) {
        radio.addEventListener('change', updateUI);
    });
});
</script>
@endsection
