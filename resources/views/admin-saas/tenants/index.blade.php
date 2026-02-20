@extends('layout.admin-saas')

@section('title', 'Tenants')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Tenants (Prefeituras)</h6>
    <a href="{{ route('admin-saas.tenants.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-8 radius-8 d-flex align-items-center gap-2">
        <iconify-icon icon="ic:baseline-plus" class="icon text-xl"></iconify-icon> Novo Tenant
    </a>
</div>

<div class="card radius-8 border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="px-24 py-16">Nome</th>
                    <th class="px-24 py-16">Slug</th>
                    <th class="px-24 py-16">Banco</th>
                    <th class="px-24 py-16">Plano</th>
                    <th class="px-24 py-16">Status</th>
                    <th class="px-24 py-16">Criado em</th>
                    <th class="px-24 py-16">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tenants as $tenant)
                    <tr>
                        <td class="px-24 py-16">
                            <a href="{{ route('admin-saas.tenants.show', $tenant) }}" class="text-primary-600 fw-medium">{{ $tenant->nome }}</a>
                        </td>
                        <td class="px-24 py-16"><code>{{ $tenant->slug }}</code></td>
                        <td class="px-24 py-16"><code>{{ $tenant->database_name }}</code></td>
                        <td class="px-24 py-16">{{ ucfirst($tenant->plano) }}</td>
                        <td class="px-24 py-16">
                            @if ($tenant->is_ativo)
                                <span class="bg-success-focus text-success-main px-16 py-6 radius-4 fw-medium text-sm">Ativo</span>
                            @else
                                <span class="bg-danger-focus text-danger-main px-16 py-6 radius-4 fw-medium text-sm">Inativo</span>
                            @endif
                        </td>
                        <td class="px-24 py-16">{{ $tenant->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-24 py-16">
                            @if ($tenant->is_ativo)
                                <form action="{{ route('admin-saas.tenants.deactivate', $tenant) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger text-sm btn-sm px-12 py-6 radius-8">Desativar</button>
                                </form>
                            @else
                                <form action="{{ route('admin-saas.tenants.activate', $tenant) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success text-sm btn-sm px-12 py-6 radius-8">Ativar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary-light py-24">Nenhum tenant cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($tenants->hasPages())
    <div class="mt-16">
        {{ $tenants->links() }}
    </div>
@endif
@endsection
