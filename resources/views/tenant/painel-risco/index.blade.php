@extends('layout.layout')

@php
    $title = 'Painel de Risco';
    $subTitle = 'Monitoramento de risco contratual administrativo';
@endphp

@section('title', 'Painel de Risco')

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Painel de Risco Administrativo</h6>
    @if (auth()->user()->hasPermission('painel-risco.exportar'))
        <a href="{{ route('tenant.painel-risco.exportar-tce') }}" class="btn btn-sm btn-outline-danger" target="_blank">
            <iconify-icon icon="solar:file-download-bold" class="me-1"></iconify-icon> Exportar Relatorio TCE
        </a>
    @endif
</div>

{{-- Secao 1 — 5 Cards Indicadores (RN-144/145) --}}
<div class="row row-cols-xxxl-5 row-cols-lg-5 row-cols-sm-2 row-cols-1 gy-4 mb-24">
    {{-- Total Contratos Ativos --}}
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Contratos Ativos</p>
                        <h6 class="mb-0">{{ $indicadores['total_ativos'] }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:document-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- % Alto Risco --}}
    <div class="col">
        @php
            $corAltoRisco = $indicadores['pct_alto_risco'] > 30 ? 'danger' : ($indicadores['pct_alto_risco'] > 10 ? 'warning' : 'success');
        @endphp
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Alto Risco</p>
                        <h6 class="mb-0">
                            {{ $indicadores['pct_alto_risco'] }}%
                            <small class="text-sm fw-normal text-secondary-light">({{ $indicadores['alto_risco'] }})</small>
                        </h6>
                    </div>
                    <div class="w-50-px h-50-px bg-{{ $corAltoRisco }}-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:shield-warning-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vencendo em 30d --}}
    <div class="col">
        @php
            $corVenc30 = $indicadores['vencendo_30d'] > 5 ? 'danger' : ($indicadores['vencendo_30d'] > 0 ? 'warning' : 'success');
        @endphp
        <div class="card shadow-none border bg-gradient-start-3 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Vencendo em 30 dias</p>
                        <h6 class="mb-0">{{ $indicadores['vencendo_30d'] }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-{{ $corVenc30 }}-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:alarm-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Aditivos >20% --}}
    <div class="col">
        @php
            $corAditivos = $indicadores['aditivos_acima_20'] > 3 ? 'danger' : ($indicadores['aditivos_acima_20'] > 0 ? 'warning' : 'success');
        @endphp
        <div class="card shadow-none border bg-gradient-start-4 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Aditivos > 20%</p>
                        <h6 class="mb-0">{{ $indicadores['aditivos_acima_20'] }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-{{ $corAditivos }}-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:chart-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sem doc obrigatoria --}}
    <div class="col">
        @php
            $corDoc = $indicadores['sem_doc_obrigatoria'] > 5 ? 'danger' : ($indicadores['sem_doc_obrigatoria'] > 0 ? 'warning' : 'success');
        @endphp
        <div class="card shadow-none border bg-gradient-start-5 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Sem Doc. Obrigatoria</p>
                        <h6 class="mb-0">{{ $indicadores['sem_doc_obrigatoria'] }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-{{ $corDoc }}-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:document-cross-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Secao 2 — Ranking de Risco (RN-146/147) --}}
<div class="card shadow-none border mb-24">
    <div class="card-header border-bottom bg-base py-16 px-24">
        <h6 class="text-lg fw-semibold mb-0">Ranking de Risco por Contrato</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-24 py-12">Contrato</th>
                        <th class="px-24 py-12">Secretaria</th>
                        <th class="px-24 py-12 text-center">Score</th>
                        <th class="px-24 py-12 text-center">Nivel</th>
                        <th class="px-24 py-12">Categorias de Risco</th>
                        <th class="px-24 py-12 text-center">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ranking as $contrato)
                        <tr>
                            <td class="px-24 py-12">
                                <a href="{{ route('tenant.contratos.show', $contrato->id) }}" class="text-primary-600 fw-semibold">
                                    {{ $contrato->numero }}/{{ $contrato->ano }}
                                </a>
                                <span class="d-block text-xs text-secondary-light">{{ \Illuminate\Support\Str::limit($contrato->objeto, 40) }}</span>
                            </td>
                            <td class="px-24 py-12">{{ $contrato->secretaria?->sigla ?? $contrato->secretaria?->nome ?? '-' }}</td>
                            <td class="px-24 py-12 text-center">
                                <span class="fw-bold text-{{ $contrato->nivel_expandido->cor() }}-main">{{ $contrato->score_expandido }}</span>
                            </td>
                            <td class="px-24 py-12 text-center">
                                <span class="badge bg-{{ $contrato->nivel_expandido->cor() }}-focus text-{{ $contrato->nivel_expandido->cor() }}-main px-12 py-6 radius-4">
                                    {{ $contrato->nivel_expandido->label() }}
                                </span>
                            </td>
                            <td class="px-24 py-12">
                                <div class="d-flex flex-wrap gap-4">
                                    @foreach ($contrato->categorias_risco as $cat)
                                        <span class="badge bg-{{ $cat['cor'] }}-focus text-{{ $cat['cor'] }}-main px-8 py-4 radius-4 text-xs"
                                              title="{{ implode('; ', $cat['criterios']) }}">
                                            {{ $cat['label'] }} ({{ $cat['score'] }})
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-24 py-12 text-center">
                                <a href="{{ route('tenant.contratos.show', $contrato->id) }}" class="btn btn-sm btn-outline-primary">
                                    <iconify-icon icon="solar:eye-bold"></iconify-icon>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-16">
                                <iconify-icon icon="solar:check-circle-bold" class="text-success-main text-3xl mb-8"></iconify-icon>
                                <p class="text-secondary-light mb-0">Nenhum contrato vigente encontrado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($ranking->hasPages())
            <div class="d-flex justify-content-center py-16">
                {{ $ranking->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Secao 3 — Mapa de Risco por Secretaria (RN-148/149) --}}
<div class="mb-24">
    <h6 class="fw-semibold mb-16">Mapa de Risco por Secretaria</h6>
    <div class="row row-cols-lg-4 row-cols-sm-2 row-cols-1 gy-4">
        @forelse ($mapaSecretarias as $sec)
            <div class="col">
                <div class="card shadow-none border h-100 {{ $sec['destaque'] ? 'border-danger' : '' }}">
                    <div class="card-body p-20">
                        <h6 class="fw-semibold text-sm mb-12">
                            {{ $sec['sigla'] ?? $sec['nome'] }}
                            @if ($sec['destaque'])
                                <iconify-icon icon="solar:danger-triangle-bold" class="text-danger-main ms-1"></iconify-icon>
                            @endif
                        </h6>
                        <div class="d-flex justify-content-between mb-8">
                            <span class="text-sm text-secondary-light">Total Contratos</span>
                            <span class="fw-semibold">{{ $sec['total_contratos'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-8">
                            <span class="text-sm text-secondary-light">Contratos Criticos</span>
                            <span class="fw-semibold text-danger-main">{{ $sec['contratos_criticos'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-sm text-secondary-light">% em Risco</span>
                            @php
                                $corPctRisco = $sec['pct_risco'] > 30 ? 'danger' : ($sec['pct_risco'] > 10 ? 'warning' : 'success');
                            @endphp
                            <span class="badge bg-{{ $corPctRisco }}-focus text-{{ $corPctRisco }}-main px-8 py-4 radius-4">
                                {{ $sec['pct_risco'] }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-secondary-light text-center py-16">Nenhuma secretaria com contratos ativos.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection
