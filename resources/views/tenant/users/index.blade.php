@extends('layout.layout')

@php
    $title = 'Usuários';
    $subTitle = 'Administração';
@endphp

@section('title', 'Usuários')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Usuários</h6>
    @if (auth()->user()->hasPermission('usuario.criar'))
        <a href="{{ route('tenant.users.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-8 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl"></iconify-icon> Novo Usuário
        </a>
    @endif
</div>

<div class="card radius-8 border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="px-24 py-16">Nome</th>
                    <th class="px-24 py-16">E-mail</th>
                    <th class="px-24 py-16">Perfil</th>
                    <th class="px-24 py-16">Secretarias</th>
                    <th class="px-24 py-16 text-center">Status</th>
                    <th class="px-24 py-16 text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="px-24 py-16 fw-medium">{{ $user->nome }}</td>
                        <td class="px-24 py-16">{{ $user->email }}</td>
                        <td class="px-24 py-16">
                            @if ($user->role)
                                <span class="badge bg-primary-focus text-primary-main px-12 py-6 radius-4">
                                    {{ $user->role->descricao }}
                                </span>
                            @else
                                <span class="text-secondary-light">-</span>
                            @endif
                        </td>
                        <td class="px-24 py-16">
                            @if ($user->secretarias->isNotEmpty())
                                @foreach ($user->secretarias as $secretaria)
                                    <span class="badge bg-neutral-200 text-neutral-600 px-8 py-4 radius-4 me-4 mb-4">
                                        {{ $secretaria->sigla ?? $secretaria->nome }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-secondary-light">{{ $user->isPerfilEstrategico() ? 'Todas' : '-' }}</span>
                            @endif
                        </td>
                        <td class="px-24 py-16 text-center">
                            @if ($user->is_ativo)
                                <span class="badge bg-success-focus text-success-main px-16 py-6 radius-4">Ativo</span>
                            @else
                                <span class="badge bg-danger-focus text-danger-main px-16 py-6 radius-4">Inativo</span>
                            @endif
                        </td>
                        <td class="px-24 py-16 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                @if (auth()->user()->hasPermission('usuario.editar'))
                                    <a href="{{ route('tenant.users.edit', $user) }}"
                                       class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-flex justify-content-center align-items-center"
                                       title="Editar">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                @endif
                                @if (auth()->user()->hasPermission('usuario.desativar') && $user->is_ativo && $user->id !== auth()->id())
                                    <form action="{{ route('tenant.users.destroy', $user) }}" method="POST"
                                          data-confirm="Tem certeza que deseja desativar este usuário?"
                                          data-confirm-title="Desativar usuário">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-flex justify-content-center align-items-center border-0"
                                                title="Desativar">
                                            <iconify-icon icon="lucide:user-x"></iconify-icon>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary-light py-24">Nenhum usuário cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@if ($users->hasPages())
    <div class="mt-16">
        {{ $users->links() }}
    </div>
@endif
@endsection
