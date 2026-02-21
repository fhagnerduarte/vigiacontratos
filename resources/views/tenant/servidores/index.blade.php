@extends('layout.layout')

@php
    $title = 'Servidores';
    $subTitle = 'Cadastros';
@endphp

@section('title', 'Servidores')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Servidores</h6>
    @if (auth()->user()->hasPermission('servidor.criar'))
        <a href="{{ route('tenant.servidores.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-8 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl"></iconify-icon> Novo Servidor
        </a>
    @endif
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

<div class="card radius-8 border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-24 py-16">Nome</th>
                        <th class="px-24 py-16">Matricula</th>
                        <th class="px-24 py-16">Cargo</th>
                        <th class="px-24 py-16">Secretaria</th>
                        <th class="px-24 py-16">E-mail</th>
                        <th class="px-24 py-16 text-center">Status</th>
                        <th class="px-24 py-16 text-center">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($servidores as $servidor)
                        <tr>
                            <td class="px-24 py-16 fw-medium">{{ $servidor->nome }}</td>
                            <td class="px-24 py-16"><code>{{ $servidor->matricula }}</code></td>
                            <td class="px-24 py-16">{{ $servidor->cargo }}</td>
                            <td class="px-24 py-16">{{ $servidor->secretaria->nome ?? '-' }}</td>
                            <td class="px-24 py-16">{{ $servidor->email ?? '-' }}</td>
                            <td class="px-24 py-16 text-center">
                                @if ($servidor->is_ativo)
                                    <span class="badge bg-success-focus text-success-main px-16 py-6 radius-4">Ativo</span>
                                @else
                                    <span class="badge bg-danger-focus text-danger-main px-16 py-6 radius-4">Inativo</span>
                                @endif
                            </td>
                            <td class="px-24 py-16 text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    @if (auth()->user()->hasPermission('servidor.editar'))
                                        <a href="{{ route('tenant.servidores.edit', $servidor) }}"
                                           class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-flex justify-content-center align-items-center"
                                           title="Editar">
                                            <iconify-icon icon="lucide:edit"></iconify-icon>
                                        </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('servidor.excluir'))
                                        <form action="{{ route('tenant.servidores.destroy', $servidor) }}" method="POST"
                                              onsubmit="return confirm('Tem certeza que deseja remover este servidor?')">
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
                            <td colspan="7" class="text-center text-secondary-light py-24">Nenhum servidor cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if ($servidores->hasPages())
    <div class="mt-16">
        {{ $servidores->links() }}
    </div>
@endif
@endsection
