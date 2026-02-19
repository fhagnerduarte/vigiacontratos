# Tema — Padrões do Painel de Risco

> Extraído de `banco-de-tema.md`. Carregar quando implementando o Painel de Risco Administrativo.
> Contém: Dashboard de Risco completo (3 seções: indicadores, ranking, mapa por secretaria).

---

## Padrões de Página

### Padrão: Painel de Risco Administrativo (Módulo 6 — Dashboard Dedicado)

```html
@extends('layout.layout')

@php
    $title = 'Painel de Risco';
    $subTitle = 'Análise de riscos contratuais';
    $script = '<script src="' . asset('assets/js/lib/apexcharts.min.js') . '"></script>';
@endphp

@section('content')
<!-- Header com botão de exportação -->
<div class="d-flex justify-content-between align-items-center mb-24">
    <div>
        <h5 class="mb-4">Painel de Risco Administrativo</h5>
        <p class="text-neutral-600 mb-0">Visão estratégica dos riscos contratuais do município</p>
    </div>
    <a href="{{ route('painel-risco.exportar-tce') }}" class="btn btn-outline-danger-600">
        <iconify-icon icon="solar:document-bold" class="icon"></iconify-icon> Exportar Relatório TCE
    </a>
</div>

<!-- SEÇÃO 1: 5 Cards de Indicadores com Semáforo (RN-144, RN-145) -->
<div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">
    <!-- Card 1: Total Contratos Ativos -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Contratos Ativos</p>
                        <h6 class="mb-0">{{ $totalContratosAtivos }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:document-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Card 2: % Risco Alto (vermelho se > 20%) -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-4 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Risco Alto</p>
                        <h6 class="mb-0 {{ $pctRiscoAlto > 20 ? 'text-danger-main' : '' }}">{{ $pctRiscoAlto }}%</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:danger-triangle-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Card 3: Vencendo em 30 dias -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Vencendo 30 dias</p>
                        <h6 class="mb-0 {{ $vencendo30d > 0 ? 'text-warning-main' : '' }}">{{ $vencendo30d }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:calendar-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Card 4: Aditivos > 20% -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Aditivos > 20%</p>
                        <h6 class="mb-0">{{ $aditivosAcima20 }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-info-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:chart-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Card 5: Sem Documentação -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-5 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Sem Doc. Obrigatória</p>
                        <h6 class="mb-0">{{ $semDocObrigatoria }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:folder-error-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SEÇÃO 2: Ranking de Risco (RN-146, RN-147) -->
<div class="card basic-data-table mt-24">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Ranking de Risco</h5>
        <span class="text-neutral-400 text-sm">Ordenado por score (maior → menor)</span>
    </div>
    <div class="card-body">
        <table class="table bordered-table mb-0" id="dataTable" data-page-length="10">
            <thead>
                <tr>
                    <th>Contrato</th>
                    <th>Secretaria</th>
                    <th>Tipo(s) de Risco</th>
                    <th class="text-center">Score</th>
                    <th class="text-center">Urgência</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rankingRisco as $contrato)
                <tr>
                    <td>
                        <a href="{{ route('contratos.show', $contrato) }}" class="text-primary-600 fw-medium">
                            {{ $contrato->numero }}
                        </a>
                        <p class="text-neutral-600 text-sm mb-0">{{ Str::limit($contrato->objeto, 50) }}</p>
                    </td>
                    <td>{{ $contrato->secretaria->nome }}</td>
                    <td>
                        <!-- Badges de categorias de risco (RN-147) -->
                        @foreach($contrato->categorias_risco as $cat)
                            @switch($cat)
                                @case('vencimento')
                                    <span class="badge bg-warning-focus text-warning-main px-8 py-4 radius-4 text-xs mb-4">Vencimento</span>
                                    @break
                                @case('financeiro')
                                    <span class="badge bg-danger-focus text-danger-main px-8 py-4 radius-4 text-xs mb-4">Financeiro</span>
                                    @break
                                @case('documental')
                                    <span class="badge bg-info-focus text-info-main px-8 py-4 radius-4 text-xs mb-4">Documental</span>
                                    @break
                                @case('juridico')
                                    <span class="badge bg-primary-focus text-primary-600 px-8 py-4 radius-4 text-xs mb-4">Jurídico</span>
                                    @break
                                @case('operacional')
                                    <span class="badge bg-neutral-200 text-neutral-600 px-8 py-4 radius-4 text-xs mb-4">Operacional</span>
                                    @break
                            @endswitch
                        @endforeach
                    </td>
                    <td class="text-center fw-bold">{{ $contrato->score_risco }}</td>
                    <td class="text-center">
                        @if($contrato->nivel_risco->value === 'alto')
                            <span class="badge bg-danger-focus text-danger-main px-20 py-9 radius-4">Crítico</span>
                        @elseif($contrato->nivel_risco->value === 'medio')
                            <span class="badge bg-warning-focus text-warning-main px-20 py-9 radius-4">Atenção</span>
                        @else
                            <span class="badge bg-success-focus text-success-main px-20 py-9 radius-4">Regular</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- SEÇÃO 3: Mapa de Risco por Secretaria (RN-148, RN-149) -->
<div class="card mt-24">
    <div class="card-header">
        <h5 class="card-title mb-0">Mapa de Risco por Secretaria</h5>
    </div>
    <div class="card-body">
        <div class="row gy-3">
            @foreach($mapaRiscoSecretaria as $sec)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-none border h-100 {{ $sec->pct_criticos > 30 ? 'border-danger' : '' }}">
                    <div class="card-body p-16">
                        <div class="d-flex justify-content-between align-items-center mb-12">
                            <h6 class="mb-0 text-sm">{{ $sec->nome }}</h6>
                            @if($sec->pct_criticos > 30)
                                <span class="badge bg-danger-focus text-danger-main px-8 py-4 radius-4 text-xs">Alerta</span>
                            @endif
                        </div>
                        <p class="text-neutral-600 mb-8">
                            {{ $sec->total_contratos }} contratos
                            <span class="fw-bold {{ $sec->contratos_criticos > 0 ? 'text-danger-main' : 'text-success-main' }}">
                                ({{ $sec->contratos_criticos }} críticos)
                            </span>
                        </p>
                        <!-- Progress bar de risco -->
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $sec->pct_criticos > 30 ? 'danger' : ($sec->pct_criticos > 10 ? 'warning' : 'success') }}"
                                 style="width: {{ $sec->pct_criticos }}%"></div>
                        </div>
                        <p class="text-neutral-400 text-xs mt-4 mb-0">{{ round($sec->pct_criticos) }}% em risco</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
```

**Notas sobre o padrão visual do Painel de Risco:**
- Usa as mesmas classes CSS do WowDash (sem dependências adicionais)
- Semáforo de cores: `bg-success-*` (verde/regular), `bg-warning-*` (amarelo/atenção), `bg-danger-*` (vermelho/crítico)
- Badges de categorias de risco com cores distintas por tipo (RN-147)
- Cards de secretaria com borda vermelha quando > 30% dos contratos em risco (RN-149)
- Botão "Exportar Relatório TCE" no header (RN-150)
- DataTable no ranking para busca e paginação

---
</output>
