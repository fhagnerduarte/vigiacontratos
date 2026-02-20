@extends('layout.layout')

@php
    $title = 'Contratos';
    $subTitle = 'Gestao Contratual';
@endphp

@section('title', 'Contratos')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Contratos</h6>
    @if (auth()->user()->hasPermission('contrato.criar'))
        <a href="{{ route('tenant.contratos.create') }}" class="btn btn-primary text-sm btn-sm px-12 py-8 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl"></iconify-icon> Novo Contrato
        </a>
    @endif
</div>

{{-- Filtros --}}
<div class="card radius-8 border-0 mb-24">
    <div class="card-body p-16">
        <form method="GET" action="{{ route('tenant.contratos.index') }}" class="row g-12 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold text-primary-light text-sm mb-4">Status</label>
                <select name="status" class="form-control radius-8 form-select">
                    <option value="">Todos</option>
                    @foreach (\App\Enums\StatusContrato::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-primary-light text-sm mb-4">Secretaria</label>
                <select name="secretaria_id" class="form-control radius-8 form-select">
                    <option value="">Todas</option>
                    @foreach ($secretarias as $sec)
                        <option value="{{ $sec->id }}" {{ request('secretaria_id') == $sec->id ? 'selected' : '' }}>{{ $sec->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-primary-light text-sm mb-4">Nivel de Risco</label>
                <select name="nivel_risco" class="form-control radius-8 form-select">
                    <option value="">Todos</option>
                    @foreach (\App\Enums\NivelRisco::cases() as $risco)
                        <option value="{{ $risco->value }}" {{ request('nivel_risco') === $risco->value ? 'selected' : '' }}>{{ $risco->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary text-sm btn-sm px-16 py-10 radius-8 w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

<div class="card radius-8 border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-24 py-16">Numero</th>
                        <th class="px-24 py-16">Objeto</th>
                        <th class="px-24 py-16">Fornecedor</th>
                        <th class="px-24 py-16">Valor Global</th>
                        <th class="px-24 py-16">Vigencia</th>
                        <th class="px-24 py-16 text-center">Status</th>
                        <th class="px-24 py-16 text-center">Risco</th>
                        <th class="px-24 py-16 text-center">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contratos as $contrato)
                        <tr>
                            <td class="px-24 py-16 fw-medium">
                                <a href="{{ route('tenant.contratos.show', $contrato) }}" class="text-primary-600 fw-semibold">
                                    {{ $contrato->numero }}
                                </a>
                            </td>
                            <td class="px-24 py-16" title="{{ $contrato->objeto }}">{{ Str::limit($contrato->objeto, 50) }}</td>
                            <td class="px-24 py-16">{{ $contrato->fornecedor->razao_social ?? '-' }}</td>
                            <td class="px-24 py-16">R$ {{ number_format($contrato->valor_global, 2, ',', '.') }}</td>
                            <td class="px-24 py-16">
                                {{ $contrato->data_inicio->format('d/m/Y') }} a {{ $contrato->data_fim->format('d/m/Y') }}
                            </td>
                            <td class="px-24 py-16 text-center">
                                <span class="badge bg-{{ $contrato->status->cor() }}-focus text-{{ $contrato->status->cor() }}-main px-16 py-6 radius-4">
                                    {{ $contrato->status->label() }}
                                </span>
                            </td>
                            <td class="px-24 py-16 text-center">
                                <span class="badge bg-{{ $contrato->nivel_risco->cor() }}-focus text-{{ $contrato->nivel_risco->cor() }}-main px-12 py-6 radius-4">
                                    {{ $contrato->nivel_risco->label() }}
                                </span>
                            </td>
                            <td class="px-24 py-16 text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <a href="{{ route('tenant.contratos.show', $contrato) }}"
                                       class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-flex justify-content-center align-items-center"
                                       title="Visualizar">
                                        <iconify-icon icon="lucide:eye"></iconify-icon>
                                    </a>
                                    @if (auth()->user()->hasPermission('contrato.editar') && $contrato->status !== \App\Enums\StatusContrato::Vencido)
                                        <a href="{{ route('tenant.contratos.edit', $contrato) }}"
                                           class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-flex justify-content-center align-items-center"
                                           title="Editar">
                                            <iconify-icon icon="lucide:edit"></iconify-icon>
                                        </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('contrato.excluir'))
                                        <form action="{{ route('tenant.contratos.destroy', $contrato) }}" method="POST"
                                              onsubmit="return confirm('Tem certeza que deseja remover este contrato?')">
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
                            <td colspan="8" class="text-center text-secondary-light py-24">Nenhum contrato cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if ($contratos->hasPages())
    <div class="mt-16">
        {{ $contratos->links() }}
    </div>
@endif
@endsection
