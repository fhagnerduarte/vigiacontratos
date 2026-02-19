@extends('admin-saas.layouts.app')

@section('title', 'Tenants')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Tenants (Prefeituras)</h3>
    <a href="{{ route('admin-saas.tenants.create') }}" class="btn btn-primary">Novo Tenant</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Slug</th>
                    <th>Banco</th>
                    <th>Plano</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tenants as $tenant)
                    <tr>
                        <td>
                            <a href="{{ route('admin-saas.tenants.show', $tenant) }}">{{ $tenant->nome }}</a>
                        </td>
                        <td><code>{{ $tenant->slug }}</code></td>
                        <td><code>{{ $tenant->database_name }}</code></td>
                        <td>{{ $tenant->plano }}</td>
                        <td>
                            @if ($tenant->is_ativo)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-danger">Inativo</span>
                            @endif
                        </td>
                        <td>{{ $tenant->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if ($tenant->is_ativo)
                                <form action="{{ route('admin-saas.tenants.deactivate', $tenant) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Desativar</button>
                                </form>
                            @else
                                <form action="{{ route('admin-saas.tenants.activate', $tenant) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm">Ativar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Nenhum tenant cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $tenants->links() }}
</div>
@endsection
