@extends('layout.layout')

@php
    $title = 'Fornecedores';
    $subTitle = 'Cadastros';
@endphp

@section('title', 'Fornecedores')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Fornecedores</h6>
    <div class="d-flex gap-8">
        <a href="{{ route('tenant.exportar.fornecedores') }}" class="btn btn-outline-success-600 text-sm btn-sm px-12 py-8 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="solar:file-download-bold" class="icon text-xl"></iconify-icon> Exportar Excel
        </a>
        <a href="{{ route('tenant.fornecedores.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-8 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl"></iconify-icon> Novo Fornecedor
        </a>
    </div>
</div>

<div class="card radius-8 border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="px-24 py-16">Razão Social</th>
                    <th class="px-24 py-16">Nome Fantasia</th>
                    <th class="px-24 py-16">CNPJ</th>
                    <th class="px-24 py-16">E-mail</th>
                    <th class="px-24 py-16">Telefone</th>
                    <th class="px-24 py-16 text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($fornecedores as $fornecedor)
                    <tr>
                        <td class="px-24 py-16 fw-medium">{{ $fornecedor->razao_social }}</td>
                        <td class="px-24 py-16">{{ $fornecedor->nome_fantasia ?? '-' }}</td>
                        <td class="px-24 py-16"><code>{{ $fornecedor->cnpj }}</code></td>
                        <td class="px-24 py-16">{{ $fornecedor->email ?? '-' }}</td>
                        <td class="px-24 py-16">{{ $fornecedor->telefone ?? '-' }}</td>
                        <td class="px-24 py-16 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="{{ route('tenant.fornecedores.edit', $fornecedor) }}"
                                   class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-flex justify-content-center align-items-center"
                                   title="Editar">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>
                                <form action="{{ route('tenant.fornecedores.destroy', $fornecedor) }}" method="POST"
                                      data-confirm="Tem certeza que deseja remover este fornecedor?"
                                      data-confirm-title="Remover fornecedor">
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
                        <td colspan="6" class="text-center text-secondary-light py-24">Nenhum fornecedor cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@if ($fornecedores->hasPages())
    <div class="mt-16">
        {{ $fornecedores->links() }}
    </div>
@endif
@endsection
