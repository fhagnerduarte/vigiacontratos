@extends('layout.layout')

@php
    $title = 'Perfis';
    $subTitle = 'Administracao';
@endphp

@section('title', 'Perfis')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Perfis de Usuario</h6>
    @if (auth()->user()->hasPermission('configuracao.editar'))
        <a href="{{ route('tenant.roles.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-8 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl"></iconify-icon> Novo Perfil
        </a>
    @endif
</div>

<div class="card radius-8 border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="px-24 py-16">Identificador</th>
                    <th class="px-24 py-16">Descricao</th>
                    <th class="px-24 py-16 text-center">Usuarios</th>
                    <th class="px-24 py-16 text-center">Tipo</th>
                    <th class="px-24 py-16 text-center">Status</th>
                    <th class="px-24 py-16 text-center">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td class="px-24 py-16 fw-medium"><code>{{ $role->nome }}</code></td>
                        <td class="px-24 py-16">{{ $role->descricao }}</td>
                        <td class="px-24 py-16 text-center">
                            <span class="badge bg-neutral-200 text-neutral-600 px-12 py-6 radius-4">
                                {{ $role->users_count }}
                            </span>
                        </td>
                        <td class="px-24 py-16 text-center">
                            @if ($role->is_padrao)
                                <span class="badge bg-info-focus text-info-main px-12 py-6 radius-4">Padrao</span>
                            @else
                                <span class="badge bg-warning-focus text-warning-main px-12 py-6 radius-4">Customizado</span>
                            @endif
                        </td>
                        <td class="px-24 py-16 text-center">
                            @if ($role->is_ativo)
                                <span class="badge bg-success-focus text-success-main px-16 py-6 radius-4">Ativo</span>
                            @else
                                <span class="badge bg-danger-focus text-danger-main px-16 py-6 radius-4">Inativo</span>
                            @endif
                        </td>
                        <td class="px-24 py-16 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                @if (auth()->user()->hasPermission('configuracao.editar'))
                                    <a href="{{ route('tenant.permissoes.index', $role) }}"
                                       class="w-32-px h-32-px bg-info-focus text-info-main rounded-circle d-flex justify-content-center align-items-center"
                                       title="Gerenciar Permissoes">
                                        <iconify-icon icon="lucide:shield-check"></iconify-icon>
                                    </a>
                                    <a href="{{ route('tenant.roles.edit', $role) }}"
                                       class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-flex justify-content-center align-items-center"
                                       title="Editar">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermission('configuracao.editar') && ! $role->is_padrao && $role->users_count === 0)
                                    <form action="{{ route('tenant.roles.destroy', $role) }}" method="POST"
                                          data-confirm="Tem certeza que deseja remover este perfil?"
                                          data-confirm-title="Remover perfil">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-flex justify-content-center align-items-center border-0"
                                                title="Remover">
                                            <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary-light py-24">Nenhum perfil cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@if ($roles->hasPages())
    <div class="mt-16">
        {{ $roles->links() }}
    </div>
@endif
@endsection
