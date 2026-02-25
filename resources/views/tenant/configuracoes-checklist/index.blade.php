@extends('layout.layout')

@php
    $title = 'Checklist de Documentos Obrigatorios';
    $subTitle = 'Configurar quais documentos sao obrigatorios por tipo de contrato';
@endphp

@section('title', 'Checklist de Documentos')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-4">Checklist de Documentos Obrigatorios</h6>
        <p class="text-secondary-light text-sm mb-0">Configure quais documentos sao obrigatorios por tipo de contrato (RN-129).</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-24" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card radius-8 border-0">
    <div class="card-header">
        <h6 class="card-title mb-0">Matriz de Obrigatoriedade</h6>
        <p class="text-sm text-secondary-light mb-0 mt-1">
            Marque os documentos que devem estar presentes para cada tipo de contrato.
            O sistema validara a completude documental com base nesta configuracao.
        </p>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('tenant.configuracoes-checklist.update') }}">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            <th>Tipo de Documento</th>
                            @foreach ($tiposContrato as $tipoContrato)
                                <th class="text-center">{{ $tipoContrato->label() }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tiposDocumento as $tipoDocumento)
                        <tr>
                            <td>
                                <span class="fw-medium text-sm">{{ $tipoDocumento->label() }}</span>
                            </td>
                            @foreach ($tiposContrato as $tipoContrato)
                                @php
                                    $isAtivo = $configuracoes->get($tipoContrato->value)?->get($tipoDocumento->value)?->is_ativo ?? false;
                                @endphp
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="checklist[{{ $tipoContrato->value }}][{{ $tipoDocumento->value }}]"
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

            <div class="mt-24 d-flex justify-content-between align-items-center">
                <p class="text-sm text-secondary-light mb-0">
                    <iconify-icon icon="solar:info-circle-bold" class="me-1"></iconify-icon>
                    Alteracoes afetam imediatamente a validacao de completude documental de todos os contratos.
                </p>
                <button type="submit" class="btn btn-primary-600">
                    <iconify-icon icon="solar:disk-bold" class="me-1"></iconify-icon> Salvar Configuracoes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
