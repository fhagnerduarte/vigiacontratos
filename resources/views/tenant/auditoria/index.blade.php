@extends('layout.layout')

@php
    $title = 'Auditoria';
    $subTitle = 'Trilha de auditoria do sistema';
@endphp

@section('title', 'Auditoria')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Trilha de Auditoria</h6>
    @if (auth()->user()->hasPermission('auditoria.exportar'))
        <div class="d-flex gap-8">
            <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-4" onclick="document.getElementById('form-exportar').action='{{ route('tenant.auditoria.exportar.pdf') }}'; document.getElementById('form-exportar').submit();">
                <iconify-icon icon="solar:file-text-bold" class="text-lg"></iconify-icon> PDF
            </button>
            <button type="button" class="btn btn-sm btn-outline-success-600 d-flex align-items-center gap-4" onclick="document.getElementById('form-exportar').action='{{ route('tenant.auditoria.exportar.csv') }}'; document.getElementById('form-exportar').submit();">
                <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> CSV
            </button>
        </div>
    @endif
</div>

{{-- 4 Cards de Resumo --}}
<div class="row row-cols-xxxl-4 row-cols-lg-4 row-cols-sm-2 row-cols-1 gy-4 mb-24">
    {{-- Total Alteracoes --}}
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Alteracoes</p>
                        <h6 class="mb-0">{{ number_format($totalAlteracoes) }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-info-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:refresh-circle-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Logins --}}
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Logins</p>
                        <h6 class="mb-0">{{ number_format($totalLogins) }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:login-2-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Acessos a Documentos --}}
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Acessos a Documentos</p>
                        <h6 class="mb-0">{{ number_format($totalAcessosDocs) }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:folder-open-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Geral --}}
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-4 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Geral</p>
                        <h6 class="mb-0">{{ number_format($totalGeral) }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:clipboard-list-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card radius-8 border-0 mb-24">
    <div class="card-header">
        <h6 class="card-title mb-0">Filtros</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('tenant.auditoria.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Data Inicio</label>
                <input type="date" name="data_inicio" class="form-control" value="{{ $filtros['data_inicio'] }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Data Fim</label>
                <input type="date" name="data_fim" class="form-control" value="{{ $filtros['data_fim'] }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select name="tipo_acao" class="form-select select2" data-placeholder="Todos...">
                    <option value=""></option>
                    <option value="alteracao" {{ ($filtros['tipo_acao'] ?? '') === 'alteracao' ? 'selected' : '' }}>Alteracoes</option>
                    <option value="login" {{ ($filtros['tipo_acao'] ?? '') === 'login' ? 'selected' : '' }}>Logins</option>
                    <option value="acesso_documento" {{ ($filtros['tipo_acao'] ?? '') === 'acesso_documento' ? 'selected' : '' }}>Acessos a Documentos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Usuario</label>
                <select name="user_id" class="form-select select2" data-placeholder="Todos...">
                    <option value=""></option>
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" {{ ($filtros['user_id'] ?? '') == $usuario->id ? 'selected' : '' }}>
                            {{ $usuario->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Entidade</label>
                <select name="entidade" class="form-select select2" data-placeholder="Todas...">
                    <option value=""></option>
                    <option value="contrato" {{ ($filtros['entidade'] ?? '') === 'contrato' ? 'selected' : '' }}>Contrato</option>
                    <option value="aditivo" {{ ($filtros['entidade'] ?? '') === 'aditivo' ? 'selected' : '' }}>Aditivo</option>
                    <option value="fornecedor" {{ ($filtros['entidade'] ?? '') === 'fornecedor' ? 'selected' : '' }}>Fornecedor</option>
                    <option value="secretaria" {{ ($filtros['entidade'] ?? '') === 'secretaria' ? 'selected' : '' }}>Secretaria</option>
                    <option value="servidor" {{ ($filtros['entidade'] ?? '') === 'servidor' ? 'selected' : '' }}>Servidor</option>
                    <option value="user" {{ ($filtros['entidade'] ?? '') === 'user' ? 'selected' : '' }}>Usuario</option>
                    <option value="role" {{ ($filtros['entidade'] ?? '') === 'role' ? 'selected' : '' }}>Perfil</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary-600 w-100">
                    <iconify-icon icon="ion:search-outline"></iconify-icon>
                </button>
                <a href="{{ route('tenant.auditoria.index') }}" class="btn btn-outline-secondary w-100" title="Limpar filtros">
                    <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Formulario de exportacao (hidden, usa mesmos filtros) --}}
@if (auth()->user()->hasPermission('auditoria.exportar'))
    <form id="form-exportar" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="data_inicio" value="{{ $filtros['data_inicio'] }}">
        <input type="hidden" name="data_fim" value="{{ $filtros['data_fim'] }}">
        @if (! empty($filtros['tipo_acao']))
            <input type="hidden" name="tipo_acao" value="{{ $filtros['tipo_acao'] }}">
        @endif
        @if (! empty($filtros['user_id']))
            <input type="hidden" name="user_id" value="{{ $filtros['user_id'] }}">
        @endif
        @if (! empty($filtros['entidade']))
            <input type="hidden" name="entidade" value="{{ $filtros['entidade'] }}">
        @endif
    </form>
@endif

{{-- Tabela de Auditoria --}}
<div class="card radius-8 border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table bordered-table mb-0">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Tipo</th>
                        <th>Usuario</th>
                        <th>Perfil</th>
                        <th>Descricao</th>
                        <th>Detalhes</th>
                        <th>IP</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paginator as $registro)
                    <tr>
                        <td class="text-nowrap">
                            <small>{{ $registro['data'] }}</small>
                        </td>
                        <td>
                            @if ($registro['tipo'] === 'Alteracao')
                                <span class="badge bg-info-focus text-info-main px-12 py-6 radius-4 fw-semibold text-sm">
                                    <iconify-icon icon="solar:refresh-circle-bold" class="me-1"></iconify-icon>
                                    Alteracao
                                </span>
                            @elseif ($registro['tipo'] === 'Login')
                                @if (! empty($registro['success']) || ! isset($registro['success']))
                                    <span class="badge bg-success-focus text-success-main px-12 py-6 radius-4 fw-semibold text-sm">
                                        <iconify-icon icon="solar:login-2-bold" class="me-1"></iconify-icon>
                                        Login
                                    </span>
                                @else
                                    <span class="badge bg-danger-focus text-danger-main px-12 py-6 radius-4 fw-semibold text-sm">
                                        <iconify-icon icon="solar:login-2-bold" class="me-1"></iconify-icon>
                                        Login Falho
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-warning-focus text-warning-main px-12 py-6 radius-4 fw-semibold text-sm">
                                    <iconify-icon icon="solar:folder-open-bold" class="me-1"></iconify-icon>
                                    Documento
                                </span>
                            @endif
                        </td>
                        <td>{{ $registro['usuario'] }}</td>
                        <td>{{ $registro['perfil'] }}</td>
                        <td>
                            <small>{{ \Illuminate\Support\Str::limit($registro['descricao'], 50) }}</small>
                        </td>
                        <td>
                            <small class="text-secondary-light">{{ \Illuminate\Support\Str::limit($registro['detalhes'], 50) }}</small>
                        </td>
                        <td>
                            <code class="text-xs">{{ $registro['ip'] }}</code>
                        </td>
                        <td>
                            <a href="{{ route('tenant.auditoria.show', ['tipo' => $registro['tipo_key'], 'id' => $registro['id']]) }}"
                               class="btn btn-sm btn-outline-primary" title="Ver detalhes">
                                <iconify-icon icon="solar:eye-bold"></iconify-icon>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-16">
                            <iconify-icon icon="solar:clipboard-list-bold" class="text-secondary-light text-3xl mb-8"></iconify-icon>
                            <p class="text-secondary-light mb-0">Nenhum registro de auditoria encontrado para o periodo selecionado.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($paginator->hasPages())
        <div class="px-24 py-16">
            {{ $paginator->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
