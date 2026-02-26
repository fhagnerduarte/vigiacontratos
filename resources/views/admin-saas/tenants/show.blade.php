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

    {{-- Configurações do Portal de Transparência --}}
    <div class="col-12">
        <div class="card radius-8 border-0">
            <div class="card-header bg-base border-bottom py-16 px-24">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:globe-bold" class="text-primary-main text-xl"></iconify-icon>
                    <h6 class="fw-semibold mb-0 text-lg">Portal de Transparência (Branding)</h6>
                </div>
            </div>
            <div class="card-body p-24">
                <form action="{{ route('admin-saas.tenants.branding.update', $tenant) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row gy-4">
                        {{-- Logo --}}
                        <div class="col-lg-6">
                            <label class="form-label fw-semibold text-primary-light text-sm mb-8">Logo / Brasão</label>
                            @if($tenant->logo_path)
                                <div class="mb-12">
                                    <img src="{{ $tenant->logo_url }}" alt="Logo atual" style="max-height: 60px; border-radius: 6px; border: 1px solid #dee2e6; padding: 4px;">
                                </div>
                            @endif
                            <input type="file" class="form-control radius-8 @error('logo') is-invalid @enderror" name="logo" accept="image/*">
                            <span class="text-secondary-light text-xs mt-4 d-block">PNG, JPG ou SVG. Máximo 2MB.</span>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cores --}}
                        <div class="col-lg-3">
                            <label for="cor_primaria" class="form-label fw-semibold text-primary-light text-sm mb-8">Cor Primária</label>
                            <div class="d-flex align-items-center gap-8">
                                <input type="color" class="form-control form-control-color radius-8" id="cor_primaria" name="cor_primaria"
                                       value="{{ old('cor_primaria', $tenant->cor_primaria ?? '#1b55e2') }}" style="width: 50px; height: 38px;">
                                <input type="text" class="form-control radius-8 text-sm" id="cor_primaria_text"
                                       value="{{ old('cor_primaria', $tenant->cor_primaria ?? '#1b55e2') }}" readonly style="max-width: 100px;">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <label for="cor_secundaria" class="form-label fw-semibold text-primary-light text-sm mb-8">Cor Secundária</label>
                            <div class="d-flex align-items-center gap-8">
                                <input type="color" class="form-control form-control-color radius-8" id="cor_secundaria" name="cor_secundaria"
                                       value="{{ old('cor_secundaria', $tenant->cor_secundaria ?? '#0b3a9e') }}" style="width: 50px; height: 38px;">
                                <input type="text" class="form-control radius-8 text-sm" id="cor_secundaria_text"
                                       value="{{ old('cor_secundaria', $tenant->cor_secundaria ?? '#0b3a9e') }}" readonly style="max-width: 100px;">
                            </div>
                        </div>

                        {{-- Dados Institucionais --}}
                        <div class="col-lg-6">
                            <label for="branding_cnpj" class="form-label fw-semibold text-primary-light text-sm mb-8">CNPJ</label>
                            <input type="text" class="form-control radius-8 @error('cnpj') is-invalid @enderror"
                                   id="branding_cnpj" name="cnpj" value="{{ old('cnpj', $tenant->cnpj) }}" placeholder="00.000.000/0000-00">
                            @error('cnpj') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-6">
                            <label for="gestor_nome" class="form-label fw-semibold text-primary-light text-sm mb-8">Gestor / Prefeito(a)</label>
                            <input type="text" class="form-control radius-8 @error('gestor_nome') is-invalid @enderror"
                                   id="gestor_nome" name="gestor_nome" value="{{ old('gestor_nome', $tenant->gestor_nome) }}" placeholder="Nome do(a) Prefeito(a)">
                            @error('gestor_nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Contato --}}
                        <div class="col-lg-12">
                            <label for="endereco" class="form-label fw-semibold text-primary-light text-sm mb-8">Endereço</label>
                            <input type="text" class="form-control radius-8 @error('endereco') is-invalid @enderror"
                                   id="endereco" name="endereco" value="{{ old('endereco', $tenant->endereco) }}" placeholder="Rua, número, bairro — CEP">
                            @error('endereco') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-4">
                            <label for="branding_telefone" class="form-label fw-semibold text-primary-light text-sm mb-8">Telefone</label>
                            <input type="text" class="form-control radius-8 @error('telefone') is-invalid @enderror"
                                   id="branding_telefone" name="telefone" value="{{ old('telefone', $tenant->telefone) }}" placeholder="(00) 0000-0000">
                            @error('telefone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-4">
                            <label for="email_contato" class="form-label fw-semibold text-primary-light text-sm mb-8">E-mail de Contato</label>
                            <input type="email" class="form-control radius-8 @error('email_contato') is-invalid @enderror"
                                   id="email_contato" name="email_contato" value="{{ old('email_contato', $tenant->email_contato) }}" placeholder="contato@prefeitura.gov.br">
                            @error('email_contato') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-4">
                            <label for="horario_atendimento" class="form-label fw-semibold text-primary-light text-sm mb-8">Horário de Atendimento</label>
                            <input type="text" class="form-control radius-8 @error('horario_atendimento') is-invalid @enderror"
                                   id="horario_atendimento" name="horario_atendimento" value="{{ old('horario_atendimento', $tenant->horario_atendimento) }}" placeholder="Seg-Sex, 8h às 14h">
                            @error('horario_atendimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Link Portal --}}
                    <div class="mt-24 p-16 bg-neutral-50 radius-8">
                        <h6 class="fw-semibold text-sm mb-8">Portal de Transparência</h6>
                        <p class="text-secondary-light text-xs mb-0">
                            URL: <a href="{{ url($tenant->slug . '/portal') }}" target="_blank" class="text-primary-main">{{ url($tenant->slug . '/portal') }}</a>
                        </p>
                    </div>

                    <div class="mt-24">
                        <button type="submit" class="btn btn-primary text-sm btn-sm px-24 py-12 radius-8">
                            <iconify-icon icon="mdi:content-save-outline" class="text-lg me-4"></iconify-icon>
                            Salvar Configurações do Portal
                        </button>
                    </div>
                </form>
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

    // Sincronizar color pickers com campos de texto
    var corPrimaria = document.getElementById('cor_primaria');
    var corPrimariaText = document.getElementById('cor_primaria_text');
    var corSecundaria = document.getElementById('cor_secundaria');
    var corSecundariaText = document.getElementById('cor_secundaria_text');

    if (corPrimaria && corPrimariaText) {
        corPrimaria.addEventListener('input', function() { corPrimariaText.value = this.value; });
    }
    if (corSecundaria && corSecundariaText) {
        corSecundaria.addEventListener('input', function() { corSecundariaText.value = this.value; });
    }
});
</script>
@endsection
