@extends('layout.layout')

@php
    $title = 'Detalhes do Alerta';
    $subTitle = 'Contrato ' . ($alerta->contrato->numero ?? '');
@endphp

@section('title', 'Alerta - Contrato ' . ($alerta->contrato->numero ?? ''))

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <a href="{{ route('tenant.alertas.index') }}" class="btn btn-sm btn-outline-secondary">
        <iconify-icon icon="solar:arrow-left-bold" class="me-1"></iconify-icon> Voltar
    </a>

    @if ($alerta->status !== \App\Enums\StatusAlerta::Resolvido && auth()->user()->hasPermission('alerta.resolver'))
        <form method="POST" action="{{ route('tenant.alertas.resolver', $alerta) }}" class="d-inline"
              onsubmit="return confirm('Tem certeza que deseja resolver este alerta manualmente?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-success-600">
                <iconify-icon icon="solar:check-circle-bold" class="me-1"></iconify-icon> Resolver Alerta
            </button>
        </form>
    @endif
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-24" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-24" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row gy-4">
    {{-- Header do Alerta --}}
    <div class="col-12">
        <div class="card radius-8 border-0">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="w-60-px h-60-px bg-{{ $alerta->prioridade->cor() }}-50 radius-8 d-flex justify-content-center align-items-center">
                            <iconify-icon icon="{{ $alerta->prioridade->icone() }}" class="text-{{ $alerta->prioridade->cor() }}-600 text-3xl"></iconify-icon>
                        </div>
                        <div>
                            <h5 class="fw-semibold mb-1">
                                Alerta de {{ $alerta->tipo_evento->label() }}
                            </h5>
                            <div class="d-flex gap-2">
                                <span class="badge bg-{{ $alerta->prioridade->cor() }}-focus text-{{ $alerta->prioridade->cor() }}-main px-12 py-6 radius-4 fw-semibold text-sm">
                                    {{ $alerta->prioridade->label() }}
                                </span>
                                <span class="badge bg-{{ $alerta->status->cor() }}-focus text-{{ $alerta->status->cor() }}-main px-12 py-6 radius-4 fw-semibold text-sm">
                                    {{ $alerta->status->label() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <p class="text-sm text-secondary-light mb-1">Dias para vencimento</p>
                        <h4 class="fw-bold {{ $alerta->dias_para_vencimento <= 7 ? 'text-danger-main' : ($alerta->dias_para_vencimento <= 30 ? 'text-warning-main' : 'text-info-main') }}">
                            {{ $alerta->dias_para_vencimento }}d
                        </h4>
                    </div>
                </div>

                <div class="mt-16 p-16 bg-neutral-50 radius-8">
                    <p class="mb-0">{{ $alerta->mensagem }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Dados do Contrato --}}
    <div class="col-lg-6">
        <div class="card radius-8 border-0 h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">Dados do Contrato</h6>
            </div>
            <div class="card-body">
                @php $contrato = $alerta->contrato; @endphp
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-secondary-light fw-medium w-40">Numero</th>
                        <td>
                            <a href="{{ route('tenant.contratos.show', $contrato) }}" class="text-primary-600 fw-semibold">
                                {{ $contrato->numero }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Objeto</th>
                        <td>{{ $contrato->objeto }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Fornecedor</th>
                        <td>{{ $contrato->fornecedor->razao_social ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Secretaria</th>
                        <td>{{ $contrato->secretaria->nome ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Valor Global</th>
                        <td>R$ {{ number_format((float) $contrato->valor_global, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Vigencia</th>
                        <td>{{ $contrato->data_inicio->format('d/m/Y') }} a {{ $contrato->data_fim->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Fiscal Atual</th>
                        <td>{{ $contrato->fiscalAtual->nome ?? 'Nao designado' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Detalhes do Alerta --}}
    <div class="col-lg-6">
        <div class="card radius-8 border-0 h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">Detalhes do Alerta</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-secondary-light fw-medium w-40">Tipo Evento</th>
                        <td>{{ $alerta->tipo_evento->label() }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Data Vencimento</th>
                        <td>{{ $alerta->data_vencimento->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Dias Restantes</th>
                        <td>
                            <span class="fw-bold {{ $alerta->dias_para_vencimento <= 7 ? 'text-danger-main' : ($alerta->dias_para_vencimento <= 30 ? 'text-warning-main' : '') }}">
                                {{ $alerta->dias_para_vencimento }} dia(s)
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Prazo Config.</th>
                        <td>{{ $alerta->dias_antecedencia_config }} dias</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Data Disparo</th>
                        <td>{{ $alerta->data_disparo->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th class="text-secondary-light fw-medium">Tentativas Envio</th>
                        <td>{{ $alerta->tentativas_envio }}</td>
                    </tr>
                    @if ($alerta->visualizado_por)
                    <tr>
                        <th class="text-secondary-light fw-medium">Visualizado por</th>
                        <td>{{ $alerta->visualizadoPor->nome ?? '-' }} em {{ $alerta->visualizado_em?->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                    @if ($alerta->resolvido_por)
                    <tr>
                        <th class="text-secondary-light fw-medium">Resolvido por</th>
                        <td>{{ $alerta->resolvidoPor->nome ?? '-' }} em {{ $alerta->resolvido_em?->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Log de Notificacoes --}}
    <div class="col-12">
        <div class="card radius-8 border-0">
            <div class="card-header">
                <h6 class="card-title mb-0">Historico de Notificacoes</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table bordered-table mb-0">
                        <thead>
                            <tr>
                                <th>Canal</th>
                                <th>Destinatario</th>
                                <th>Data Envio</th>
                                <th>Tentativa</th>
                                <th>Status</th>
                                <th>Resposta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($alerta->logNotificacoes->sortByDesc('data_envio') as $log)
                            <tr>
                                <td>{{ $log->canal->label() }}</td>
                                <td>{{ $log->destinatario }}</td>
                                <td>{{ $log->data_envio->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $log->tentativa_numero }}a</td>
                                <td>
                                    @if ($log->sucesso)
                                        <span class="badge bg-success-focus text-success-main">Sucesso</span>
                                    @else
                                        <span class="badge bg-danger-focus text-danger-main">Falha</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-secondary-light">{{ \Illuminate\Support\Str::limit($log->resposta_gateway, 60) }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-16 text-secondary-light">
                                    Nenhuma notificacao enviada ainda.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
