@extends('layout.layout')

@php
    $title = 'Checklist de Documentos Obrigatorios';
    $subTitle = 'Configurar quais documentos sao obrigatorios por tipo de contrato e fase';
@endphp

@section('title', 'Checklist de Documentos')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-4">Checklist de Documentos Obrigatorios</h6>
        <p class="text-secondary-light text-sm mb-0">Configure quais documentos sao obrigatorios por fase contratual e tipo de contrato (RN-129, Lei 14.133/2021).</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-24" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('tenant.configuracoes-checklist.update') }}">
    @csrf
    @method('PUT')

    <div class="accordion" id="accordionFases">
        @foreach ($fases as $index => $fase)
            @php
                $documentosDaFase = $mapeamentoPadrao[$fase->value] ?? [];
            @endphp
            @if (count($documentosDaFase) > 0)
            <div class="card radius-8 border-0 mb-16">
                <div class="card-header p-0" id="heading{{ $index }}">
                    <button class="btn w-100 text-start d-flex align-items-center gap-12 p-16 {{ $index === 0 ? '' : 'collapsed' }}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse{{ $index }}"
                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                        <iconify-icon icon="{{ $fase->icone() }}" class="text-primary-600 text-xl"></iconify-icon>
                        <span class="fw-semibold">{{ $fase->ordem() }}. {{ $fase->label() }}</span>
                        <span class="badge bg-neutral-200 text-neutral-600 ms-2">{{ count($documentosDaFase) }} doc(s)</span>
                    </button>
                </div>
                <div id="collapse{{ $index }}" class="collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#accordionFases">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table bordered-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="px-16">Tipo de Documento</th>
                                        @foreach ($tiposContrato as $tipoContrato)
                                            <th class="text-center px-8">{{ $tipoContrato->label() }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($documentosDaFase as $tipoDocumento)
                                    <tr>
                                        <td class="px-16">
                                            <span class="fw-medium text-sm">{{ $tipoDocumento->label() }}</span>
                                        </td>
                                        @foreach ($tiposContrato as $tipoContrato)
                                            @php
                                                $isAtivo = $configuracoes
                                                    ->get($fase->value, collect())
                                                    ->get($tipoContrato->value, collect())
                                                    ->get($tipoDocumento->value)?->is_ativo ?? true;
                                            @endphp
                                            <td class="text-center px-8">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           name="checklist[{{ $fase->value }}][{{ $tipoContrato->value }}][{{ $tipoDocumento->value }}]"
                                                           value="1"
                                                           {{ $isAtivo ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    <div class="mt-24 d-flex justify-content-between align-items-center">
        <p class="text-sm text-secondary-light mb-0">
            <iconify-icon icon="solar:info-circle-bold" class="me-1"></iconify-icon>
            Alteracoes afetam imediatamente a validacao de conformidade por fase de todos os contratos.
        </p>
        <button type="submit" class="btn btn-primary-600">
            <iconify-icon icon="solar:disk-bold" class="me-1"></iconify-icon> Salvar Configuracoes
        </button>
    </div>
</form>

@endsection
