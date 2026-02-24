@extends('layout.layout')

@php
    $title = 'LGPD';
    $subTitle = 'Detalhes da solicitacao #' . $solicitacao->id;
@endphp

@section('title', 'LGPD — Solicitacao #' . $solicitacao->id)

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Solicitacao LGPD #{{ $solicitacao->id }}</h6>
    <a href="{{ route('tenant.lgpd.index') }}" class="btn btn-sm btn-outline-secondary-600">
        <iconify-icon icon="solar:arrow-left-bold" class="me-1"></iconify-icon> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body p-24">
        <div class="row gy-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-neutral-500">Tipo de Solicitacao</label>
                <p class="mb-0">
                    <span class="badge bg-info-focus text-info-main px-12 py-6 radius-4">
                        {{ $solicitacao->tipo_solicitacao?->label() ?? '-' }}
                    </span>
                </p>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-neutral-500">Status</label>
                <p class="mb-0">
                    @if ($solicitacao->status === 'processado')
                        <span class="badge bg-success-focus text-success-main px-12 py-6 radius-4">Processado</span>
                    @else
                        <span class="badge bg-warning-focus text-warning-main px-12 py-6 radius-4">Pendente</span>
                    @endif
                </p>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-neutral-500">Entidade</label>
                <p class="mb-0">{{ class_basename($solicitacao->entidade_tipo) }} #{{ $solicitacao->entidade_id }}</p>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-neutral-500">Solicitante</label>
                <p class="mb-0">{{ $solicitacao->solicitante }}</p>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-neutral-500">Data da Solicitacao</label>
                <p class="mb-0">{{ $solicitacao->data_solicitacao?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-neutral-500">Data de Execucao</label>
                <p class="mb-0">{{ $solicitacao->data_execucao?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold text-neutral-500">Justificativa</label>
                <p class="mb-0">{{ $solicitacao->justificativa ?? '-' }}</p>
            </div>

            @if ($solicitacao->campos_anonimizados)
                <div class="col-md-12">
                    <label class="form-label fw-semibold text-neutral-500">Campos Anonimizados</label>
                    <div class="d-flex flex-wrap gap-8">
                        @foreach ($solicitacao->campos_anonimizados as $campo)
                            <span class="badge bg-neutral-200 text-neutral-600 px-12 py-6 radius-4">{{ $campo }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($solicitacao->executado_por)
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-neutral-500">Executado por</label>
                    <p class="mb-0">{{ $solicitacao->executor?->nome ?? 'ID: ' . $solicitacao->executado_por }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
