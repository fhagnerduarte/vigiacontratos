@extends('portal.layout')

@section('title', 'Contrato ' . $contrato->numero)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('portal.index', $tenant->slug) }}">Início</a></li>
    <li class="breadcrumb-item"><a href="{{ route('portal.contratos', $tenant->slug) }}">Contratos</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $contrato->numero }}</li>
@endsection

@section('content')
{{-- Header --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="portal-section-title mb-1">Contrato {{ $contrato->numero }}</h2>
    </div>
    @php
        $badgeClass = match($contrato->status?->value) {
            'vigente' => 'portal-badge-vigente',
            'vencido' => 'portal-badge-vencido',
            'cancelado' => 'portal-badge-cancelado',
            'suspenso' => 'portal-badge-suspenso',
            'encerrado' => 'portal-badge-encerrado',
            'rescindido' => 'portal-badge-rescindido',
            default => 'bg-secondary text-white',
        };
    @endphp
    <span class="badge {{ $badgeClass }} fs-6 px-3 py-2">{{ ucfirst($contrato->status?->value) }}</span>
</div>

{{-- Dados Gerais --}}
<div class="card portal-card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><iconify-icon icon="solar:document-text-bold" width="18"></iconify-icon> Dados Gerais</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <strong class="text-muted d-block mb-1">Objeto</strong>
                <p class="mb-0">{{ $contrato->objeto }}</p>
            </div>
            <div class="col-md-4">
                <strong class="text-muted d-block mb-1">Tipo</strong>
                <p class="mb-0">{{ $contrato->tipo?->label() ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <strong class="text-muted d-block mb-1">Modalidade</strong>
                <p class="mb-0">{{ $contrato->modalidade_contratacao?->label() ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <strong class="text-muted d-block mb-1">Processo Licitatório</strong>
                <p class="mb-0">{{ $contrato->numero_processo ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Contratado --}}
<div class="card portal-card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><iconify-icon icon="solar:users-group-rounded-bold" width="18"></iconify-icon> Contratado</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <strong class="text-muted d-block mb-1">Razão Social</strong>
                <p class="mb-0">{{ $contrato->fornecedor?->razao_social ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <strong class="text-muted d-block mb-1">CNPJ</strong>
                <p class="mb-0">{{ $contrato->fornecedor?->cnpj ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <strong class="text-muted d-block mb-1">Secretaria</strong>
                <p class="mb-0">{{ $contrato->secretaria?->nome ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Valores --}}
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="portal-stat-card">
            <div class="stat-icon bg-success-light">
                <iconify-icon icon="solar:wallet-money-bold" width="24"></iconify-icon>
            </div>
            <div class="stat-value" style="color: #28a745;">R$ {{ number_format($contrato->valor_global, 2, ',', '.') }}</div>
            <p class="stat-label">Valor Global</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="portal-stat-card">
            <div class="stat-icon bg-info-light">
                <iconify-icon icon="solar:calendar-minimalistic-bold" width="24"></iconify-icon>
            </div>
            <div class="stat-value" style="color: #17a2b8;">R$ {{ number_format($contrato->valor_mensal, 2, ',', '.') }}</div>
            <p class="stat-label">Valor Mensal</p>
        </div>
    </div>
</div>

{{-- Vigência --}}
<div class="card portal-card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><iconify-icon icon="solar:calendar-bold" width="18"></iconify-icon> Vigência</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <strong class="text-muted d-block mb-1">Data Início</strong>
                <p class="mb-0">{{ $contrato->data_inicio?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <strong class="text-muted d-block mb-1">Data Fim</strong>
                <p class="mb-0">{{ $contrato->data_fim?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <strong class="text-muted d-block mb-1">Data Publicação</strong>
                <p class="mb-0">{{ $contrato->data_publicacao?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <strong class="text-muted d-block mb-1">Fonte de Recurso</strong>
                <p class="mb-0">{{ $contrato->fonte_recurso ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Aditivos --}}
@if($contrato->aditivos->count() > 0)
<div class="card portal-card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><iconify-icon icon="solar:add-circle-bold" width="18"></iconify-icon> Aditivos ({{ $contrato->aditivos->count() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table portal-table mb-0">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Tipo</th>
                        <th class="text-end">Valor Acréscimo</th>
                        <th>Data Início</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contrato->aditivos as $aditivo)
                    <tr>
                        <td class="fw-bold">{{ $aditivo->numero_sequencial }}o Termo Aditivo</td>
                        <td>{{ $aditivo->tipo?->label() ?? $aditivo->tipo }}</td>
                        <td class="text-end">R$ {{ number_format($aditivo->valor_acrescimo ?? 0, 2, ',', '.') }}</td>
                        <td>{{ $aditivo->data_inicio_vigencia?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ ucfirst($aditivo->status?->value ?? $aditivo->status) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Voltar --}}
<div class="mt-3">
    <a href="{{ route('portal.contratos', $tenant->slug) }}" class="btn btn-outline-secondary">
        <iconify-icon icon="solar:arrow-left-bold" width="16"></iconify-icon> Voltar para listagem
    </a>
</div>
@endsection
