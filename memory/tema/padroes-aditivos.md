# Tema — Padrões de Aditivos

> Extraído de `banco-de-tema.md`. Carregar quando implementando aditivos contratuais.
> Contém: Timeline de Aditivos (Show), Dashboard de Aditivos (Indicadores Anuais).

---

## Padrões de Página

### Padrão: Timeline de Aditivos (Detalhes — aditivos/show.blade.php)

```html
@extends('layout.layout')

@php
    $title = $aditivo->numero_sequencial . 'º Termo Aditivo';
    $subTitle = 'Contrato ' . $aditivo->contrato->numero;
@endphp

@section('content')
<!-- Cabeçalho com Status e Tipo -->
<div class="card mb-24">
    <div class="card-body p-24">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-4">{{ $aditivo->numero_sequencial }}º Termo Aditivo</h5>
                <p class="text-neutral-600 mb-8">
                    Contrato: <a href="{{ route('contratos.show', $aditivo->contrato) }}" class="text-primary-600">
                        {{ $aditivo->contrato->numero }}
                    </a>
                </p>
                <p class="text-neutral-600 mb-0">Assinado em: {{ $aditivo->data_assinatura->format('d/m/Y') }}</p>
            </div>
            <div class="d-flex gap-12 align-items-center">
                <!-- Badge de Tipo -->
                <span class="badge bg-primary-focus text-primary-600 px-20 py-9 radius-4">
                    {{ $aditivo->tipo->label() }}
                </span>
                <!-- Badge de Status -->
                <span class="badge bg-success-focus text-success-main px-20 py-9 radius-4">
                    {{ $aditivo->status->label() }}
                </span>
                <!-- Badge Percentual Acumulado (se acima de 20%) -->
                @if($aditivo->percentual_acumulado > 20)
                <span class="badge bg-warning-focus text-warning-main px-20 py-9 radius-4">
                    Acumulado: {{ $aditivo->percentual_acumulado }}%
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Cards de Impacto Financeiro/Temporal -->
<div class="row gy-4 mb-24">
    @if($aditivo->valor_acrescimo)
    <div class="col-md-4">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <p class="fw-medium text-primary-light mb-1">Valor Acrescido</p>
                <h6 class="mb-0 text-success-main">+ R$ {{ number_format($aditivo->valor_acrescimo, 2, ',', '.') }}</h6>
            </div>
        </div>
    </div>
    @endif
    @if($aditivo->valor_supressao)
    <div class="col-md-4">
        <div class="card shadow-none border bg-gradient-start-4 h-100">
            <div class="card-body p-20">
                <p class="fw-medium text-primary-light mb-1">Valor Suprimido</p>
                <h6 class="mb-0 text-danger-main">- R$ {{ number_format($aditivo->valor_supressao, 2, ',', '.') }}</h6>
            </div>
        </div>
    </div>
    @endif
    @if($aditivo->nova_data_fim)
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

<!-- Timeline + Detalhes (lado a lado) -->
<div class="row gy-4">
    <!-- Timeline de Todos os Aditivos do Contrato -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Histórico de Aditivos</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-unstyled mb-0">
                    @foreach($todosAditivos as $item)
                    <li class="d-flex gap-16 p-16 border-bottom {{ $item->id === $aditivo->id ? 'bg-primary-50' : '' }}">
                        <!-- Número sequencial circular -->
                        <div class="w-40-px h-40-px {{ $item->id === $aditivo->id ? 'bg-primary-600' : 'bg-neutral-200' }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                            <span class="{{ $item->id === $aditivo->id ? 'text-white' : 'text-neutral-600' }} fw-bold text-sm">
                                {{ $item->numero_sequencial }}
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-medium text-neutral-900">{{ $item->tipo->label() }}</span>
                                <span class="badge bg-{{ $item->status->value === 'vigente' ? 'success' : 'neutral' }}-focus text-{{ $item->status->value === 'vigente' ? 'success' : 'neutral' }}-main px-12 py-4 radius-4 text-xs">
                                    {{ $item->status->label() }}
                                </span>
                            </div>
                            <p class="text-neutral-600 text-sm mb-4">{{ $item->data_assinatura->format('d/m/Y') }}</p>
                            @if($item->valor_acrescimo)
                                <p class="text-success-main text-sm mb-0 fw-medium">+ R$ {{ number_format($item->valor_acrescimo, 2, ',', '.') }}</p>
                            @endif
                            @if($item->valor_supressao)
                                <p class="text-danger-main text-sm mb-0 fw-medium">- R$ {{ number_format($item->valor_supressao, 2, ',', '.') }}</p>
                            @endif
                            @if($item->nova_data_fim)
                                <p class="text-info-main text-sm mb-0">Até: {{ $item->nova_data_fim->format('d/m/Y') }}</p>
                            @endif
                        </div>
                        @if($item->id !== $aditivo->id)
                        <a href="{{ route('aditivos.show', $item) }}" class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0">
                            <iconify-icon icon="solar:eye-bold"></iconify-icon>
                        </a>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Detalhes do Aditivo Atual -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Detalhes do {{ $aditivo->numero_sequencial }}º Aditivo</h5>
            </div>
            <div class="card-body">
                <!-- Fundamentação Legal -->
                <div class="mb-20">
                    <label class="form-label fw-semibold text-neutral-600 text-sm">Fundamentação Legal</label>
                    <p class="mb-0">{{ $aditivo->fundamentacao_legal }}</p>
                </div>
                <!-- Justificativa Técnica -->
                <div class="mb-20">
                    <label class="form-label fw-semibold text-neutral-600 text-sm">Justificativa Técnica</label>
                    <p class="mb-0">{{ $aditivo->justificativa_tecnica }}</p>
                </div>
                <!-- Campos de Reequilíbrio (condicional) -->
                @if($aditivo->tipo->value === 'reequilibrio')
                <div class="border rounded p-16 mb-20 bg-neutral-50">
                    <h6 class="mb-12 text-neutral-700">Dados do Reequilíbrio</h6>
                    <div class="row">
                        <div class="col-md-6 mb-8">
                            <p class="text-neutral-600 text-sm mb-2">Índice Utilizado</p>
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
                <!-- Barra de Limite Legal -->
                <div class="mb-20">
                    <div class="d-flex justify-content-between mb-8">
                        <label class="form-label fw-semibold text-neutral-600 text-sm mb-0">Percentual Acumulado</label>
                        <span class="text-{{ $aditivo->percentual_acumulado > $limiteConfiguracao ? 'danger' : 'success' }}-main fw-bold text-sm">
                            {{ $aditivo->percentual_acumulado }}% / {{ $limiteConfiguracao }}%
                        </span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-{{ $aditivo->percentual_acumulado > $limiteConfiguracao ? 'danger' : ($aditivo->percentual_acumulado > ($limiteConfiguracao * 0.8) ? 'warning' : 'success') }}"
                             style="width: {{ min(100, ($aditivo->percentual_acumulado / $limiteConfiguracao) * 100) }}%">
                        </div>
                    </div>
                    <small class="text-neutral-400">Limite legal configurado: {{ $limiteConfiguracao }}%</small>
                </div>
                <!-- Documentos Anexados -->
                @if($aditivo->documentos->count() > 0)
                <div>
                    <label class="form-label fw-semibold text-neutral-600 text-sm">Documentos Anexados</label>
                    @foreach($aditivo->documentos as $doc)
                    <div class="d-flex align-items-center gap-12 p-12 border rounded mb-8">
                        <iconify-icon icon="solar:folder-bold" class="text-primary-600 text-xl flex-shrink-0"></iconify-icon>
                        <div class="flex-grow-1">
                            <p class="fw-medium mb-0 text-sm">{{ $doc->nome }}</p>
                            <p class="text-neutral-400 text-xs mb-0">{{ $doc->tipo_documento->label() }}</p>
                        </div>
                        <a href="{{ route('documentos.download', $doc) }}" class="btn btn-outline-primary-600 btn-sm">
                            <iconify-icon icon="solar:download-bold" class="icon"></iconify-icon>
                        </a>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
```

### Padrão: Dashboard de Aditivos (Indicadores Anuais)

```html
<!-- Pode ser seção dentro do dashboard executivo ou página independente -->
@extends('layout.layout')

@php
    $title = 'Aditivos';
    $subTitle = 'Dashboard de aditivos contratuais';
    $script = '<script src="' . asset('assets/js/lib/apexcharts.min.js') . '"></script>';
@endphp

@section('content')
<!-- Cards de Indicadores Anuais (RN-109 a RN-114) -->
<div class="row row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4 mb-24">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Aditivos no Ano</p>
                        <h6 class="mb-0">{{ $totalAditivosAno }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:add-circle-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Valor Total Acrescido</p>
                        <h6 class="mb-0 text-success-main">R$ {{ number_format($valorTotalAcrescido, 2, ',', '.') }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:wallet-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">% Médio de Acréscimo</p>
                        <h6 class="mb-0">{{ $percentualMedioAcrescimo }}%</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:chart-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ranking de Contratos Mais Alterados + Secretarias -->
<div class="row gy-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Contratos Mais Alterados (Top 10)</h5>
            </div>
            <div class="card-body">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            <th>Contrato</th>
                            <th class="text-center">Aditivos</th>
                            <th class="text-end">% Acumulado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rankingContratosMaisAlterados as $item)
                        <tr>
                            <td>
                                <a href="{{ route('contratos.show', $item->contrato_id) }}" class="text-primary-600">
                                    {{ $item->numero }}
                                </a>
                            </td>
                            <td class="text-center">{{ $item->total_aditivos }}</td>
                            <td class="text-end">
                                <span class="badge bg-{{ $item->percentual_acumulado > 20 ? 'warning' : 'success' }}-focus text-{{ $item->percentual_acumulado > 20 ? 'warning' : 'success' }}-main px-12 py-4 radius-4">
                                    {{ $item->percentual_acumulado }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Secretarias com Mais Aditivos</h5>
            </div>
            <div class="card-body">
                <div id="chart-secretarias-aditivos"></div>
            </div>
        </div>
    </div>
</div>
@endsection
```
