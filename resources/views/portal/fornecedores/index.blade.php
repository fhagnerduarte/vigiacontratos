@extends('portal.layout')

@section('title', 'Fornecedores')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('portal.index', $tenant->slug) }}">Inicio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Fornecedores</li>
@endsection

@section('content')
<h2 class="portal-section-title">Fornecedores</h2>

{{-- Busca --}}
<div class="portal-filter-panel">
    <form method="GET" action="{{ route('portal.fornecedores', $tenant->slug) }}">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Buscar</label>
                <div class="portal-search-wrapper">
                    <iconify-icon icon="solar:magnifer-bold" width="16" class="search-icon"></iconify-icon>
                    <input type="text" name="busca" class="form-control" placeholder="Razao social ou CNPJ..." value="{{ request('busca') }}">
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <iconify-icon icon="solar:magnifer-bold" width="16"></iconify-icon> Filtrar
                </button>
                <a href="{{ route('portal.fornecedores', $tenant->slug) }}" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </div>
    </form>
</div>

{{-- Contador --}}
<div class="portal-result-counter">
    Exibindo {{ $fornecedores->firstItem() ?? 0 }} a {{ $fornecedores->lastItem() ?? 0 }} de {{ $fornecedores->total() }} fornecedores
</div>

{{-- Tabela --}}
<div class="table-responsive">
    <table class="table portal-table">
        <thead>
            <tr>
                <th>Razao Social</th>
                <th>CNPJ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fornecedores as $fornecedor)
            <tr>
                <td>{{ $fornecedor->razao_social }}</td>
                <td>{{ $fornecedor->cnpj }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2">
                    <div class="portal-empty-state">
                        <iconify-icon icon="solar:users-group-rounded-bold" width="48"></iconify-icon>
                        <p>Nenhum fornecedor encontrado.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginacao --}}
<div class="d-flex justify-content-center mt-3">
    {{ $fornecedores->withQueryString()->links() }}
</div>
@endsection
