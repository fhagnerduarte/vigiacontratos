@extends('layout.layout')

@php
    $title = 'Relatório de Auditoria';
    $subTitle = 'Relatórios';
@endphp

@section('title', 'Relatório de Auditoria')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-4">Relatório de Auditoria</h6>
        <p class="text-neutral-500 text-sm mb-0">Configure os filtros e gere o relatório em PDF ou CSV</p>
    </div>
    <a href="{{ route('tenant.relatorios.index') }}" class="btn btn-outline-neutral-600 btn-sm d-flex align-items-center gap-4">
        <iconify-icon icon="solar:arrow-left-bold" class="text-lg"></iconify-icon> Voltar
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show radius-8 mb-24" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

<div class="card radius-8 border-0">
    <div class="card-body p-24">
        <form id="form-auditoria" method="POST">
            @csrf
            <div class="row g-16 mb-24">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-4">Data Início *</label>
                    <input type="date" name="data_inicio" value="{{ old('data_inicio', now()->subMonth()->format('Y-m-d')) }}" class="form-control radius-8" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-4">Data Fim *</label>
                    <input type="date" name="data_fim" value="{{ old('data_fim', now()->format('Y-m-d')) }}" class="form-control radius-8" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-4">Tipo de Ação</label>
                    <select name="tipo_acao" class="form-select select2" data-placeholder="Todos">
                        <option value="">Todos</option>
                        <option value="alteracao" {{ old('tipo_acao') === 'alteracao' ? 'selected' : '' }}>Alterações</option>
                        <option value="login" {{ old('tipo_acao') === 'login' ? 'selected' : '' }}>Logins</option>
                        <option value="acesso_documento" {{ old('tipo_acao') === 'acesso_documento' ? 'selected' : '' }}>Acessos a Documentos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-4">Usuário</label>
                    <select name="user_id" class="form-select select2" data-placeholder="Todos">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ old('user_id') == $usuario->id ? 'selected' : '' }}>{{ $usuario->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-16 mb-24">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-4">Entidade</label>
                    <select name="entidade" class="form-select select2" data-placeholder="Todas">
                        <option value="">Todas</option>
                        <option value="contrato" {{ old('entidade') === 'contrato' ? 'selected' : '' }}>Contrato</option>
                        <option value="aditivo" {{ old('entidade') === 'aditivo' ? 'selected' : '' }}>Aditivo</option>
                        <option value="fornecedor" {{ old('entidade') === 'fornecedor' ? 'selected' : '' }}>Fornecedor</option>
                        <option value="secretaria" {{ old('entidade') === 'secretaria' ? 'selected' : '' }}>Secretaria</option>
                        <option value="servidor" {{ old('entidade') === 'servidor' ? 'selected' : '' }}>Servidor</option>
                        <option value="user" {{ old('entidade') === 'user' ? 'selected' : '' }}>Usuário</option>
                        <option value="role" {{ old('entidade') === 'role' ? 'selected' : '' }}>Perfil</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-12">
                <button type="submit" formaction="{{ route('tenant.relatorios.auditoria.pdf') }}" class="btn btn-primary-600 btn-sm d-flex align-items-center gap-4">
                    <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> Gerar PDF
                </button>
                <button type="submit" formaction="{{ route('tenant.relatorios.auditoria.csv') }}" class="btn btn-outline-success-600 btn-sm d-flex align-items-center gap-4">
                    <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> Gerar CSV
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
