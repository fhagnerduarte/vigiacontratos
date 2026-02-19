@extends('admin-saas.layouts.app')

@section('title', $tenant->nome)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>{{ $tenant->nome }}</h3>
    <a href="{{ route('admin-saas.tenants.index') }}" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th class="text-muted" style="width: 200px;">ID</th>
                        <td>{{ $tenant->id }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Nome</th>
                        <td>{{ $tenant->nome }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Slug</th>
                        <td><code>{{ $tenant->slug }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">URL</th>
                        <td><code>{{ $tenant->slug }}.{{ config('app.domain') }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Banco de Dados</th>
                        <td><code>{{ $tenant->database_name }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Host do Banco</th>
                        <td>{{ $tenant->database_host ?? 'Padrão (mesmo do master)' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Plano</th>
                        <td>{{ ucfirst($tenant->plano) }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status</th>
                        <td>
                            @if ($tenant->is_ativo)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-danger">Inativo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Criado em</th>
                        <td>{{ $tenant->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Atualizado em</th>
                        <td>{{ $tenant->updated_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header">Ações</div>
            <div class="card-body">
                @if ($tenant->is_ativo)
                    <form action="{{ route('admin-saas.tenants.deactivate', $tenant) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">Desativar Tenant</button>
                    </form>
                @else
                    <form action="{{ route('admin-saas.tenants.activate', $tenant) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-success w-100">Ativar Tenant</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
