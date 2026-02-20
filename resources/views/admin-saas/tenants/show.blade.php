@extends('layout.admin-saas')

@section('title', $tenant->nome)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">{{ $tenant->nome }}</h6>
    <a href="{{ route('admin-saas.tenants.index') }}" class="btn btn-outline-secondary text-sm btn-sm px-12 py-8 radius-8">Voltar</a>
</div>

<div class="row gy-4">
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
</div>
@endsection
