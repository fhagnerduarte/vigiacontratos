@extends('layout.admin-saas')

@section('title', 'Novo Tenant')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Novo Tenant</h6>
    <a href="{{ route('admin-saas.tenants.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-12 py-8 radius-8">Voltar</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card radius-8 border-0">
            <div class="card-body p-24">
                <form method="POST" action="{{ route('admin-saas.tenants.store') }}">
                    @csrf

                    <div class="mb-20">
                        <label for="nome" class="form-label fw-semibold text-primary-light text-sm mb-8">Nome da Prefeitura <span class="text-danger-main">*</span></label>
                        <input type="text" class="form-control radius-8 @error('nome') is-invalid @enderror"
                               id="nome" name="nome" value="{{ old('nome') }}" required
                               placeholder="Ex: Prefeitura Municipal de São Paulo">
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-20">
                        <label for="slug" class="form-label fw-semibold text-primary-light text-sm mb-8">Slug (subdomínio) <span class="text-danger-main">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control radius-8 @error('slug') is-invalid @enderror"
                                   id="slug" name="slug" value="{{ old('slug') }}" required
                                   placeholder="Ex: prefeitura-sao-paulo">
                            <span class="input-group-text radius-8">.{{ config('app.domain', 'vigiacontratos.com.br') }}</span>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <span class="text-secondary-light text-sm mt-4 d-block">Apenas letras minúsculas, números e hífens.</span>
                    </div>

                    <div class="mb-20">
                        <label for="plano" class="form-label fw-semibold text-primary-light text-sm mb-8">Plano</label>
                        <select class="form-select radius-8 select2" id="plano" name="plano">
                            <option value="basico" {{ old('plano') === 'basico' ? 'selected' : '' }}>Básico</option>
                            <option value="profissional" {{ old('plano') === 'profissional' ? 'selected' : '' }}>Profissional</option>
                            <option value="enterprise" {{ old('plano') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary text-sm btn-sm px-24 py-12 radius-8">
                        Criar Tenant
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
