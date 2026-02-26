@extends('layout.layout')

@php
    $title = 'Secretarias';
    $subTitle = 'Cadastros';
@endphp

@section('title', 'Secretarias')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Secretarias</h6>
    <a href="{{ route('tenant.secretarias.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-8 radius-8 d-flex align-items-center gap-2">
        <iconify-icon icon="ic:baseline-plus" class="icon text-xl"></iconify-icon> Nova Secretaria
    </a>
</div>

<div class="card radius-8 border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="px-24 py-16">Nome</th>
                    <th class="px-24 py-16">Sigla</th>
                    <th class="px-24 py-16">Responsável</th>
                    <th class="px-24 py-16">E-mail</th>
                    <th class="px-24 py-16">Telefone</th>
                    <th class="px-24 py-16 text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($secretarias as $secretaria)
                    <tr>
                        <td class="px-24 py-16 fw-medium">{{ $secretaria->nome }}</td>
                        <td class="px-24 py-16">{{ $secretaria->sigla ?? '-' }}</td>
                        <td class="px-24 py-16">{{ $secretaria->responsavel ?? '-' }}</td>
                        <td class="px-24 py-16">{{ $secretaria->email ?? '-' }}</td>
                        <td class="px-24 py-16">{{ $secretaria->telefone ?? '-' }}</td>
                        <td class="px-24 py-16 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="{{ route('tenant.secretarias.edit', $secretaria) }}"
                                   class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-flex justify-content-center align-items-center"
                                   title="Editar">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>
                                <form action="{{ route('tenant.secretarias.destroy', $secretaria) }}" method="POST"
                                      data-confirm="Tem certeza que deseja remover esta secretaria?"
                                      data-confirm-title="Remover secretaria">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-flex justify-content-center align-items-center border-0"
                                            title="Remover">
                                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary-light py-24">Nenhuma secretaria cadastrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($secretarias->hasPages())
    <div class="mt-16">
        {{ $secretarias->links() }}
    </div>
@endif
@endsection
