@extends('portal.layout')

@section('title', 'Solicitacao ' . $solicitacao->protocolo)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('portal.index', $tenant->slug) }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('portal.lai.create', $tenant->slug) }}">e-SIC</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $solicitacao->protocolo }}</li>
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Header --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="portal-section-title mb-1">{{ $solicitacao->protocolo }}</h2>
        <p class="text-muted mb-0">
            <iconify-icon icon="solar:calendar-bold" width="14"></iconify-icon>
            Registrada em {{ $solicitacao->created_at->format('d/m/Y \a\s H:i') }}
        </p>
    </div>
    <span class="badge bg-{{ $solicitacao->status->cor() }} fs-6 px-3 py-2">
        <iconify-icon icon="{{ $solicitacao->status->icone() }}" width="16"></iconify-icon>
        {{ $solicitacao->status->label() }}
    </span>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Dados da Solicitacao --}}
        <div class="card portal-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><iconify-icon icon="solar:document-text-bold" width="18"></iconify-icon> Dados da Solicitacao</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">Protocolo</strong>
                        <p class="mb-0 fw-bold">{{ $solicitacao->protocolo }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">Solicitante</strong>
                        <p class="mb-0">{{ $solicitacao->nome_solicitante }}</p>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">Prazo Legal</strong>
                        <p class="mb-0">
                            <iconify-icon icon="solar:calendar-minimalistic-bold" width="14"></iconify-icon>
                            {{ $solicitacao->prazo_legal->format('d/m/Y') }}
                            @if ($solicitacao->prazo_estendido)
                                <br>
                                <small class="text-primary">
                                    <iconify-icon icon="solar:clock-circle-bold" width="14"></iconify-icon>
                                    Estendido ate {{ $solicitacao->prazo_estendido->format('d/m/Y') }}
                                </small>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        @if (!$solicitacao->status->isFinalizado())
                            <strong class="text-muted d-block mb-1">Dias Restantes</strong>
                            @if ($solicitacao->is_vencida)
                                <p class="mb-0 text-danger fw-bold">
                                    <iconify-icon icon="solar:danger-triangle-bold" width="16"></iconify-icon>
                                    Vencida ({{ abs($solicitacao->dias_restantes) }} dias)
                                </p>
                            @else
                                <p class="mb-0 {{ $solicitacao->dias_restantes <= 5 ? 'text-warning fw-bold' : 'text-success' }}">
                                    <iconify-icon icon="solar:clock-circle-bold" width="16"></iconify-icon>
                                    {{ $solicitacao->dias_restantes }} dias
                                </p>
                            @endif
                        @else
                            <strong class="text-muted d-block mb-1">Respondida em</strong>
                            <p class="mb-0">{{ $solicitacao->data_resposta?->format('d/m/Y H:i') ?? '-' }}</p>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <strong class="text-muted d-block mb-1">Assunto</strong>
                    <p class="mb-0 fw-bold">{{ $solicitacao->assunto }}</p>
                </div>
                <div>
                    <strong class="text-muted d-block mb-1">Descricao</strong>
                    <p class="mb-0" style="white-space: pre-line;">{{ $solicitacao->descricao }}</p>
                </div>
            </div>
        </div>

        {{-- Resposta --}}
        @if ($solicitacao->resposta)
            <div class="card portal-card mb-4" style="border-top-color: {{ $solicitacao->status === \App\Enums\StatusSolicitacaoLai::Indeferida ? '#dc3545' : '#28a745' }};">
                <div class="card-header" style="background: {{ $solicitacao->status === \App\Enums\StatusSolicitacaoLai::Indeferida ? '#dc3545' : '#28a745' }}; color: #fff;">
                    <h5 class="mb-0">
                        <iconify-icon icon="{{ $solicitacao->status === \App\Enums\StatusSolicitacaoLai::Indeferida ? 'solar:close-circle-bold' : 'solar:check-circle-bold' }}" width="18"></iconify-icon>
                        {{ $solicitacao->status === \App\Enums\StatusSolicitacaoLai::Indeferida ? 'Indeferimento' : 'Resposta' }}
                        @if ($solicitacao->classificacao_resposta)
                            — {{ $solicitacao->classificacao_resposta->label() }}
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-line;">{{ $solicitacao->resposta }}</p>
                </div>
            </div>
        @endif

        {{-- Prorrogacao --}}
        @if ($solicitacao->justificativa_prorrogacao)
            <div class="card portal-card mb-4" style="border-top-color: #ffc107;">
                <div class="card-header" style="background: #ffc107; color: #333;">
                    <h5 class="mb-0">
                        <iconify-icon icon="solar:clock-circle-bold" width="18"></iconify-icon> Prorrogacao de Prazo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong class="text-muted d-block mb-1">Data da Prorrogacao</strong>
                            <p class="mb-0">{{ $solicitacao->data_prorrogacao?->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-muted d-block mb-1">Novo Prazo</strong>
                            <p class="mb-0 fw-bold">{{ $solicitacao->prazo_estendido?->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-muted d-block mb-1">Justificativa</strong>
                            <p class="mb-0">{{ $solicitacao->justificativa_prorrogacao }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Status Card --}}
        <div class="card portal-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><iconify-icon icon="solar:info-circle-bold" width="18"></iconify-icon> Status</h5>
            </div>
            <div class="card-body text-center py-4">
                <div class="mb-3">
                    <iconify-icon icon="{{ $solicitacao->status->icone() }}" width="48" style="color: var(--portal-primary); opacity: 0.7;"></iconify-icon>
                </div>
                <span class="badge bg-{{ $solicitacao->status->cor() }} fs-6 px-3 py-2 mb-3">
                    {{ $solicitacao->status->label() }}
                </span>
                @if (!$solicitacao->status->isFinalizado())
                    <p class="text-muted small mt-3 mb-0">
                        Sua solicitacao esta sendo processada dentro do prazo legal de 20 dias uteis previsto na Lei 12.527/2011.
                    </p>
                @else
                    <p class="text-muted small mt-3 mb-0">
                        Esta solicitacao foi finalizada. Caso discorde da resposta, voce pode registrar um recurso.
                    </p>
                @endif
            </div>
        </div>

        {{-- Timeline do Historico --}}
        @if ($solicitacao->historicos->isNotEmpty())
            <div class="card portal-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><iconify-icon icon="solar:history-bold" width="18"></iconify-icon> Historico</h5>
                </div>
                <div class="card-body">
                    <div class="portal-timeline">
                        @foreach ($solicitacao->historicos->sortBy('created_at') as $historico)
                            @php
                                $statusEnum = \App\Enums\StatusSolicitacaoLai::tryFrom($historico->status_novo);
                            @endphp
                            <div class="portal-timeline-item">
                                <div class="mb-1">
                                    <strong>{{ $statusEnum?->label() ?? $historico->status_novo }}</strong>
                                </div>
                                <small class="text-muted d-block mb-1">
                                    <iconify-icon icon="solar:calendar-minimalistic-bold" width="12"></iconify-icon>
                                    {{ $historico->created_at->format('d/m/Y H:i') }}
                                </small>
                                @if ($historico->observacao)
                                    <p class="mb-0 small text-muted">{{ $historico->observacao }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Acoes --}}
        <div class="card portal-card">
            <div class="card-header">
                <h5 class="mb-0"><iconify-icon icon="solar:widget-bold" width="18"></iconify-icon> Acoes</h5>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('portal.lai.create', $tenant->slug) }}" class="btn btn-primary">
                    <iconify-icon icon="solar:pen-new-round-bold" width="16"></iconify-icon> Nova Solicitacao
                </a>
                <a href="{{ route('portal.lai.consultar', $tenant->slug) }}" class="btn btn-outline-secondary">
                    <iconify-icon icon="solar:magnifer-bold" width="16"></iconify-icon> Consultar Outra
                </a>
                <a href="{{ route('portal.index', $tenant->slug) }}" class="btn btn-outline-secondary">
                    <iconify-icon icon="solar:arrow-left-bold" width="16"></iconify-icon> Voltar ao Portal
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
