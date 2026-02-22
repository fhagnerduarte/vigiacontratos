@extends('layout.layout')

@php
    $title = $aditivo->numero_sequencial . 'o Termo Aditivo';
    $subTitle = 'Contrato ' . $aditivo->contrato->numero;
@endphp

@section('title', $aditivo->numero_sequencial . 'o Termo Aditivo')

@section('content')

{{-- Flash Messages --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show radius-8 mb-24" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

{{-- Header Card --}}
<div class="card radius-8 border-0 mb-24">
    <div class="card-body p-24">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="fw-semibold mb-4">{{ $aditivo->numero_sequencial }}o Termo Aditivo</h5>
                <p class="text-neutral-600 mb-8">
                    Contrato:
                    <a href="{{ route('tenant.contratos.show', $aditivo->contrato) }}" class="text-primary-600 fw-medium">
                        {{ $aditivo->contrato->numero }}
                    </a>
                    — {{ $aditivo->contrato->fornecedor->razao_social ?? '' }}
                    @if ($aditivo->contrato->secretaria)
                        — {{ $aditivo->contrato->secretaria->nome }}
                    @endif
                </p>
                <p class="text-neutral-600 mb-0">
                    Assinado em: {{ $aditivo->data_assinatura->format('d/m/Y') }}
                </p>
            </div>
            <div class="d-flex gap-12 align-items-center flex-wrap">
                {{-- Badge Tipo --}}
                <span class="badge bg-{{ $aditivo->tipo->cor() }}-focus text-{{ $aditivo->tipo->cor() }}-main px-20 py-9 radius-4 text-sm">
                    {{ $aditivo->tipo->label() }}
                </span>
                {{-- Badge Status --}}
                <span class="badge bg-{{ $aditivo->status->cor() }}-focus text-{{ $aditivo->status->cor() }}-main px-20 py-9 radius-4 text-sm">
                    {{ $aditivo->status->label() }}
                </span>
                {{-- Badge Percentual Acumulado (se >20%) --}}
                @if ($aditivo->percentual_acumulado > 20)
                    <span class="badge bg-warning-focus text-warning-main px-20 py-9 radius-4 text-sm">
                        Acumulado: {{ number_format($aditivo->percentual_acumulado, 2, ',', '.') }}%
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Botao Cancelar (RN-116 — apenas admin com permissao aditivo.aprovar) --}}
@if ($aditivo->status->value === 'vigente' && auth()->user()->hasPermission('aditivo.aprovar'))
    <div class="d-flex justify-content-end mb-24">
        <form action="{{ route('tenant.aditivos.cancelar', $aditivo) }}" method="POST"
              onsubmit="return confirm('Tem certeza que deseja cancelar este aditivo? Os valores do contrato serao recalculados. Esta acao nao pode ser desfeita.');">
            @csrf
            <button type="submit" class="btn btn-outline-danger-600 radius-8">
                <iconify-icon icon="solar:close-circle-bold" class="icon"></iconify-icon> Cancelar Aditivo
            </button>
        </form>
    </div>
@endif

{{-- Impact Cards --}}
<div class="row gy-4 mb-24">
    @if ($aditivo->valor_acrescimo)
        <div class="col-md-4">
            <div class="card shadow-none border bg-gradient-start-2 h-100">
                <div class="card-body p-20">
                    <p class="fw-medium text-primary-light mb-1">Valor Acrescido</p>
                    <h6 class="mb-0 text-success-main">+ R$ {{ number_format($aditivo->valor_acrescimo, 2, ',', '.') }}</h6>
                </div>
            </div>
        </div>
    @endif
    @if ($aditivo->valor_supressao)
        <div class="col-md-4">
            <div class="card shadow-none border bg-gradient-start-4 h-100">
                <div class="card-body p-20">
                    <p class="fw-medium text-primary-light mb-1">Valor Suprimido</p>
                    <h6 class="mb-0 text-danger-main">- R$ {{ number_format($aditivo->valor_supressao, 2, ',', '.') }}</h6>
                </div>
            </div>
        </div>
    @endif
    @if ($aditivo->nova_data_fim)
        <div class="col-md-4">
            <div class="card shadow-none border bg-gradient-start-1 h-100">
                <div class="card-body p-20">
                    <p class="fw-medium text-primary-light mb-1">Nova Data Fim</p>
                    <h6 class="mb-0">{{ $aditivo->nova_data_fim->format('d/m/Y') }}</h6>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Timeline + Detalhes (lado a lado) --}}
<div class="row gy-4 mb-24">

    {{-- Coluna Esquerda: Timeline de Todos os Aditivos --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Historico de Aditivos</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-unstyled mb-0">
                    @foreach ($todosAditivos as $item)
                        <li class="d-flex gap-16 p-16 border-bottom {{ $item->id === $aditivo->id ? 'bg-primary-50' : '' }}">
                            {{-- Numero sequencial circular --}}
                            <div class="w-40-px h-40-px {{ $item->id === $aditivo->id ? 'bg-primary-600' : 'bg-neutral-200' }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                <span class="{{ $item->id === $aditivo->id ? 'text-white' : 'text-neutral-600' }} fw-bold text-sm">
                                    {{ $item->numero_sequencial }}
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between mb-4">
                                    <span class="fw-medium text-neutral-900">{{ $item->tipo->label() }}</span>
                                    <span class="badge bg-{{ $item->status->cor() }}-focus text-{{ $item->status->cor() }}-main px-12 py-4 radius-4 text-xs">
                                        {{ $item->status->label() }}
                                    </span>
                                </div>
                                <p class="text-neutral-600 text-sm mb-4">{{ $item->data_assinatura->format('d/m/Y') }}</p>
                                @if ($item->valor_acrescimo)
                                    <p class="text-success-main text-sm mb-0 fw-medium">+ R$ {{ number_format($item->valor_acrescimo, 2, ',', '.') }}</p>
                                @endif
                                @if ($item->valor_supressao)
                                    <p class="text-danger-main text-sm mb-0 fw-medium">- R$ {{ number_format($item->valor_supressao, 2, ',', '.') }}</p>
                                @endif
                                @if ($item->nova_data_fim)
                                    <p class="text-info-main text-sm mb-0">Ate: {{ $item->nova_data_fim->format('d/m/Y') }}</p>
                                @endif
                            </div>
                            @if ($item->id !== $aditivo->id)
                                <a href="{{ route('tenant.aditivos.show', $item) }}"
                                   class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                   title="Visualizar">
                                    <iconify-icon icon="solar:eye-bold"></iconify-icon>
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Coluna Direita: Detalhes do Aditivo Atual --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Detalhes do {{ $aditivo->numero_sequencial }}o Aditivo</h5>
            </div>
            <div class="card-body">

                {{-- Fundamentacao Legal --}}
                <div class="mb-20">
                    <label class="form-label fw-semibold text-neutral-600 text-sm">Fundamentacao Legal</label>
                    <p class="mb-0">{{ $aditivo->fundamentacao_legal }}</p>
                </div>

                {{-- Justificativa Tecnica --}}
                <div class="mb-20">
                    <label class="form-label fw-semibold text-neutral-600 text-sm">Justificativa Tecnica</label>
                    <p class="mb-0">{{ $aditivo->justificativa_tecnica }}</p>
                </div>

                {{-- Reequilibrio (condicional) --}}
                @if ($aditivo->tipo->value === 'reequilibrio')
                    <div class="border rounded p-16 mb-20 bg-neutral-50">
                        <h6 class="mb-12 text-neutral-700">Dados do Reequilibrio</h6>
                        <div class="row">
                            <div class="col-md-6 mb-8">
                                <p class="text-neutral-600 text-sm mb-2">Indice Utilizado</p>
                                <p class="fw-medium mb-0">{{ $aditivo->indice_utilizado }}</p>
                            </div>
                            <div class="col-md-6 mb-8">
                                <p class="text-neutral-600 text-sm mb-2">Motivo</p>
                                <p class="fw-medium mb-0">{{ $aditivo->motivo_reequilibrio }}</p>
                            </div>
                            <div class="col-md-6 mb-8">
                                <p class="text-neutral-600 text-sm mb-2">Valor Anterior</p>
                                <p class="fw-medium mb-0">R$ {{ number_format($aditivo->valor_anterior_reequilibrio, 2, ',', '.') }}</p>
                            </div>
                            <div class="col-md-6 mb-8">
                                <p class="text-neutral-600 text-sm mb-2">Valor Reajustado</p>
                                <p class="fw-medium mb-0">R$ {{ number_format($aditivo->valor_reajustado, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Barra de Limite Legal --}}
                @php
                    $limiteConfig = $limiteLegal['limite'] ?? 25;
                    $percentual = (float) $aditivo->percentual_acumulado;
                    $ratio = $limiteConfig > 0 ? ($percentual / $limiteConfig) * 100 : 0;
                    $barColor = $percentual > $limiteConfig ? 'danger' : ($percentual > ($limiteConfig * 0.8) ? 'warning' : 'success');
                @endphp
                <div class="mb-20">
                    <div class="d-flex justify-content-between mb-8">
                        <label class="form-label fw-semibold text-neutral-600 text-sm mb-0">Percentual Acumulado</label>
                        <span class="text-{{ $percentual > $limiteConfig ? 'danger' : 'success' }}-main fw-bold text-sm">
                            {{ number_format($percentual, 2, ',', '.') }}% / {{ $limiteConfig }}%
                        </span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-{{ $barColor }}"
                             role="progressbar"
                             style="width: {{ min(100, $ratio) }}%"
                             aria-valuenow="{{ $percentual }}"
                             aria-valuemin="0"
                             aria-valuemax="{{ $limiteConfig }}">
                        </div>
                    </div>
                    <small class="text-neutral-400">Limite legal configurado: {{ $limiteConfig }}%</small>
                </div>

                {{-- Documentos Anexados --}}
                @if ($aditivo->documentos->count() > 0)
                    <div>
                        <label class="form-label fw-semibold text-neutral-600 text-sm">Documentos Anexados</label>
                        @foreach ($aditivo->documentos as $doc)
                            <div class="d-flex align-items-center gap-12 p-12 border rounded mb-8">
                                <iconify-icon icon="solar:file-bold" class="text-primary-600 text-xl flex-shrink-0"></iconify-icon>
                                <div class="flex-grow-1">
                                    <p class="fw-medium mb-0 text-sm">{{ $doc->nome_original ?? $doc->nome }}</p>
                                    <p class="text-neutral-400 text-xs mb-0">{{ $doc->tipo_documento->label() }}</p>
                                </div>
                                @if (auth()->user()->hasPermission('documento.download'))
                                    <a href="{{ route('tenant.documentos.download', $doc) }}"
                                       class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                       title="Download">
                                        <iconify-icon icon="solar:download-bold"></iconify-icon>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- Workflow de Aprovacao --}}
<div class="card radius-8 border-0">
    <div class="card-header">
        <h5 class="card-title mb-0">Workflow de Aprovacao</h5>
    </div>
    <div class="card-body p-24">

        @if ($aditivo->workflowAprovacoes->count() > 0)

            {{-- Status geral do workflow --}}
            @if ($workflowAprovado)
                <div class="alert alert-success radius-8 mb-24 d-flex align-items-center gap-12">
                    <iconify-icon icon="solar:check-circle-bold" class="text-success-main text-2xl"></iconify-icon>
                    <span class="fw-medium">Aditivo totalmente aprovado em todas as etapas do workflow.</span>
                </div>
            @elseif ($aditivo->workflowAprovacoes->where('status.value', 'reprovado')->count() > 0)
                @php
                    $etapaReprovada = $aditivo->workflowAprovacoes->firstWhere('status.value', 'reprovado');
                @endphp
                <div class="alert alert-danger radius-8 mb-24 d-flex align-items-center gap-12">
                    <iconify-icon icon="solar:close-circle-bold" class="text-danger-main text-2xl"></iconify-icon>
                    <span class="fw-medium">Aditivo reprovado na etapa: {{ $etapaReprovada->etapa->label() }}</span>
                </div>
            @endif

            {{-- Stepper horizontal --}}
            <div class="d-flex justify-content-between align-items-start mb-24">
                @foreach ($aditivo->workflowAprovacoes as $etapa)
                    <div class="text-center" style="flex: 1;">
                        <div class="w-40-px h-40-px rounded-circle mx-auto d-flex align-items-center justify-content-center
                            {{ $etapa->status->value === 'aprovado' ? 'bg-success-main' : ($etapa->status->value === 'reprovado' ? 'bg-danger-main' : 'bg-neutral-200') }}">
                            <iconify-icon icon="{{ $etapa->status->icone() }}" class="{{ $etapa->status->value === 'pendente' ? 'text-neutral-600' : 'text-white' }} text-lg"></iconify-icon>
                        </div>
                        <p class="text-xs mt-8 mb-4 fw-medium">{{ $etapa->etapa->label() }}</p>
                        <span class="badge bg-{{ $etapa->status->cor() }}-focus text-{{ $etapa->status->cor() }}-main px-8 py-4 radius-4 text-xs">
                            {{ $etapa->status->label() }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Detalhes de cada etapa --}}
            <div class="row gy-3">
                @foreach ($aditivo->workflowAprovacoes as $etapa)
                    <div class="col-12">
                        <div class="d-flex align-items-start gap-16 p-16 border rounded {{ $etapaAtual && $etapa->id === $etapaAtual->id ? 'border-primary' : '' }}">
                            <div class="w-40-px h-40-px rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                                {{ $etapa->status->value === 'aprovado' ? 'bg-success-focus' : ($etapa->status->value === 'reprovado' ? 'bg-danger-focus' : 'bg-neutral-100') }}">
                                <span class="fw-bold text-sm {{ $etapa->status->value === 'aprovado' ? 'text-success-main' : ($etapa->status->value === 'reprovado' ? 'text-danger-main' : 'text-neutral-600') }}">
                                    {{ $etapa->etapa_ordem }}
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="fw-semibold text-neutral-900">{{ $etapa->etapa->label() }}</span>
                                    <span class="badge bg-{{ $etapa->status->cor() }}-focus text-{{ $etapa->status->cor() }}-main px-12 py-4 radius-4 text-xs">
                                        <iconify-icon icon="{{ $etapa->status->icone() }}" class="me-4"></iconify-icon>
                                        {{ $etapa->status->label() }}
                                    </span>
                                </div>
                                @if ($etapa->user)
                                    <p class="text-neutral-600 text-sm mb-4">
                                        Processado por: <span class="fw-medium">{{ $etapa->user->nome }}</span>
                                    </p>
                                @endif
                                @if ($etapa->parecer)
                                    <p class="text-neutral-600 text-sm mb-4">
                                        Parecer: <em>{{ $etapa->parecer }}</em>
                                    </p>
                                @endif
                                @if ($etapa->decided_at)
                                    <p class="text-neutral-400 text-xs mb-0">
                                        {{ $etapa->decided_at->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Formulario Aprovar/Reprovar (etapa atual) --}}
            @if ($etapaAtual && auth()->user()->hasPermission('aditivo.aprovar'))
                <div class="border rounded p-16 mt-16">
                    <h6 class="mb-12">Acao na Etapa: {{ $etapaAtual->etapa->label() }}</h6>
                    <div class="mb-12">
                        <label class="form-label">Parecer (opcional para aprovacao, obrigatorio para reprovacao)</label>
                        <textarea id="parecer-workflow" class="form-control radius-8" rows="3" placeholder="Digite seu parecer..."></textarea>
                    </div>
                    <div class="d-flex gap-12">
                        <form action="{{ route('tenant.aditivos.aprovar', $aditivo) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="parecer" id="parecer-aprovar">
                            <button type="submit" class="btn btn-success-600 radius-8"
                                    onclick="document.getElementById('parecer-aprovar').value = document.getElementById('parecer-workflow').value;">
                                <iconify-icon icon="solar:check-circle-bold" class="icon"></iconify-icon> Aprovar
                            </button>
                        </form>
                        <form action="{{ route('tenant.aditivos.reprovar', $aditivo) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="parecer" id="parecer-reprovar">
                            <button type="submit" class="btn btn-danger-600 radius-8"
                                    onclick="document.getElementById('parecer-reprovar').value = document.getElementById('parecer-workflow').value;">
                                <iconify-icon icon="solar:close-circle-bold" class="icon"></iconify-icon> Reprovar
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        @else
            <div class="text-center text-secondary-light py-24">
                <iconify-icon icon="solar:clock-circle-bold" class="text-4xl mb-8 d-block"></iconify-icon>
                Nenhuma etapa de workflow registrada para este aditivo.
            </div>
        @endif

    </div>
</div>

@endsection
