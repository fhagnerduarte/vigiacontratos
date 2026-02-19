@extends('admin-saas.layouts.app')

@section('title', 'Novo Tenant')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Novo Tenant</h3>
            <a href="{{ route('admin-saas.tenants.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin-saas.tenants.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Prefeitura</label>
                        <input type="text" class="form-control @error('nome') is-invalid @enderror"
                               id="nome" name="nome" value="{{ old('nome') }}" required
                               placeholder="Ex: Prefeitura Municipal de São Paulo">
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug (subdomínio)</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                   id="slug" name="slug" value="{{ old('slug') }}" required
                                   placeholder="Ex: prefeitura-sao-paulo">
                            <span class="input-group-text">.{{ config('app.domain') }}</span>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">Apenas letras minúsculas, números e hífens.</div>
                    </div>

                    <div class="mb-3">
                        <label for="plano" class="form-label">Plano</label>
                        <select class="form-select" id="plano" name="plano">
                            <option value="basico" {{ old('plano') === 'basico' ? 'selected' : '' }}>Básico</option>
                            <option value="profissional" {{ old('plano') === 'profissional' ? 'selected' : '' }}>Profissional</option>
                            <option value="enterprise" {{ old('plano') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Criar Tenant</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
