@extends('layout.layout')

@php
    $title = 'Efetividade Mensal';
    $subTitle = 'Relatorios';
@endphp

@section('title', 'Efetividade Mensal')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-4">Relatorio de Efetividade Mensal</h6>
        <p class="text-neutral-500 text-sm mb-0">RN-057 — Contratos regularizados a tempo vs. vencidos sem acao</p>
    </div>
    <a href="{{ route('tenant.relatorios.index') }}" class="btn btn-outline-neutral-600 btn-sm d-flex align-items-center gap-4">
        <iconify-icon icon="solar:arrow-left-bold" class="text-lg"></iconify-icon> Voltar
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show radius-8 mb-24" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

{{-- FILTROS --}}
<div class="card radius-8 border-0 mb-24">
    <div class="card-body p-24">
        <form method="GET" action="{{ route('tenant.relatorios.efetividade-mensal') }}">
            <div class="row g-16 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-4">Mes *</label>
                    <select name="mes" class="form-select radius-8" required>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (int) request('mes', now()->month) === $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-4">Ano *</label>
                    <select name="ano" class="form-select radius-8" required>
                        @for ($a = now()->year; $a >= 2020; $a--)
                            <option value="{{ $a }}" {{ (int) request('ano', now()->year) === $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary-light text-sm mb-4">Secretaria</label>
                    <select name="secretaria_id" class="form-select select2" data-placeholder="Todas as secretarias">
                        <option value="">Todas</option>
                        @foreach ($secretarias as $sec)
                            <option value="{{ $sec->id }}" {{ request('secretaria_id') == $sec->id ? 'selected' : '' }}>{{ $sec->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary-600 btn-sm d-flex align-items-center gap-4">
                        <iconify-icon icon="solar:chart-2-bold" class="text-lg"></iconify-icon> Gerar Relatorio
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if ($dados)
{{-- INDICADORES --}}
<div class="row g-16 mb-24">
    <div class="col-sm-6 col-lg-3">
        <div class="card radius-8 border-0 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center gap-12">
                    <div class="w-40-px h-40-px bg-primary-100 rounded-circle d-flex align-items-center justify-content-center">
                        <iconify-icon icon="solar:document-text-bold" class="text-primary-600 text-xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-neutral-500 text-sm mb-0">Contratos Monitorados</p>
                        <h5 class="fw-bold mb-0">{{ $dados['resumo']['total_elegiveis'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card radius-8 border-0 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center gap-12">
                    <div class="w-40-px h-40-px bg-success-100 rounded-circle d-flex align-items-center justify-content-center">
                        <iconify-icon icon="solar:check-circle-bold" class="text-success-600 text-xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-neutral-500 text-sm mb-0">Regularizados a Tempo</p>
                        <h5 class="fw-bold mb-0 text-success-600">{{ $dados['resumo']['regularizados_a_tempo'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card radius-8 border-0 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center gap-12">
                    <div class="w-40-px h-40-px bg-danger-100 rounded-circle d-flex align-items-center justify-content-center">
                        <iconify-icon icon="solar:close-circle-bold" class="text-danger-600 text-xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-neutral-500 text-sm mb-0">Vencidos sem Acao</p>
                        <h5 class="fw-bold mb-0 text-danger-600">{{ $dados['resumo']['vencidos_sem_acao'] }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card radius-8 border-0 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center gap-12">
                    <div class="w-40-px h-40-px rounded-circle d-flex align-items-center justify-content-center" style="background-color: {{ $dados['resumo']['taxa_efetividade'] >= 70 ? '#dcfce7' : ($dados['resumo']['taxa_efetividade'] >= 40 ? '#fef3c7' : '#fee2e2') }};">
                        <iconify-icon icon="solar:graph-up-bold" class="{{ $dados['resumo']['taxa_efetividade'] >= 70 ? 'text-success-600' : ($dados['resumo']['taxa_efetividade'] >= 40 ? 'text-warning-600' : 'text-danger-600') }} text-xl"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-neutral-500 text-sm mb-0">Taxa de Efetividade</p>
                        <h5 class="fw-bold mb-0 {{ $dados['resumo']['taxa_efetividade'] >= 70 ? 'text-success-600' : ($dados['resumo']['taxa_efetividade'] >= 40 ? 'text-warning-600' : 'text-danger-600') }}">{{ $dados['resumo']['taxa_efetividade'] }}%</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- INDICADORES COMPLEMENTARES --}}
<div class="row g-16 mb-24">
    <div class="col-sm-6">
        <div class="card radius-8 border-0 h-100">
            <div class="card-body p-20 d-flex align-items-center gap-12">
                <div class="w-40-px h-40-px bg-warning-100 rounded-circle d-flex align-items-center justify-content-center">
                    <iconify-icon icon="solar:history-bold" class="text-warning-600 text-xl"></iconify-icon>
                </div>
                <div>
                    <p class="text-neutral-500 text-sm mb-0">Regularizados Retroativamente</p>
                    <h5 class="fw-bold mb-0 text-warning-600">{{ $dados['resumo']['regularizados_retroativos'] }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card radius-8 border-0 h-100">
            <div class="card-body p-20 d-flex align-items-center gap-12">
                <div class="w-40-px h-40-px bg-info-100 rounded-circle d-flex align-items-center justify-content-center">
                    <iconify-icon icon="solar:clock-circle-bold" class="text-info-600 text-xl"></iconify-icon>
                </div>
                <div>
                    <p class="text-neutral-500 text-sm mb-0">Tempo Medio de Antecipacao</p>
                    <h5 class="fw-bold mb-0 text-info-600">{{ $dados['resumo']['tempo_medio_antecipacao'] }} dias</h5>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-16 mb-24">
    {{-- GRAFICO DONUT --}}
    <div class="col-lg-5">
        <div class="card radius-8 border-0 h-100">
            <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                <h6 class="text-lg fw-semibold mb-0">Distribuicao</h6>
            </div>
            <div class="card-body p-24">
                <div id="chart-efetividade"></div>
            </div>
        </div>
    </div>

    {{-- TABELA POR SECRETARIA --}}
    <div class="col-lg-7">
        <div class="card radius-8 border-0 h-100">
            <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                <h6 class="text-lg fw-semibold mb-0">Efetividade por Secretaria</h6>
            </div>
            <div class="card-body p-24">
                @if (count($dados['por_secretaria']) > 0)
                <div class="table-responsive">
                    <table class="table bordered-table text-sm">
                        <thead>
                            <tr>
                                <th>Secretaria</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Regulariz.</th>
                                <th class="text-center">Vencidos</th>
                                <th class="text-center">Retroat.</th>
                                <th class="text-center">Taxa %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dados['por_secretaria'] as $sec)
                            <tr>
                                <td>{{ $sec['secretaria'] }}</td>
                                <td class="text-center">{{ $sec['total'] }}</td>
                                <td class="text-center text-success-600 fw-semibold">{{ $sec['regularizados'] }}</td>
                                <td class="text-center text-danger-600 fw-semibold">{{ $sec['vencidos'] }}</td>
                                <td class="text-center text-warning-600">{{ $sec['retroativos'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $sec['taxa'] >= 70 ? 'success' : ($sec['taxa'] >= 40 ? 'warning' : 'danger') }}-focus text-{{ $sec['taxa'] >= 70 ? 'success' : ($sec['taxa'] >= 40 ? 'warning' : 'danger') }}-main px-12 py-4 rounded-pill fw-semibold text-sm">
                                        {{ $sec['taxa'] }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-neutral-500 text-sm mb-0">Nenhum contrato elegivel no periodo selecionado.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- TABELA DETALHADA --}}
<div class="card radius-8 border-0 mb-24">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
        <h6 class="text-lg fw-semibold mb-0">Detalhamento dos Contratos</h6>
        <div class="d-flex gap-8">
            <form method="POST" action="{{ route('tenant.relatorios.efetividade-mensal.pdf') }}" class="d-inline">
                @csrf
                <input type="hidden" name="mes" value="{{ request('mes', now()->month) }}">
                <input type="hidden" name="ano" value="{{ request('ano', now()->year) }}">
                @if (request('secretaria_id'))
                    <input type="hidden" name="secretaria_id" value="{{ request('secretaria_id') }}">
                @endif
                <button type="submit" class="btn btn-outline-danger-600 btn-sm d-flex align-items-center gap-4">
                    <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> PDF
                </button>
            </form>
            <a href="{{ route('tenant.relatorios.efetividade-mensal.excel', request()->only(['mes', 'ano', 'secretaria_id'])) }}" class="btn btn-outline-success-600 btn-sm d-flex align-items-center gap-4">
                <iconify-icon icon="solar:file-download-bold" class="text-lg"></iconify-icon> Excel
            </a>
        </div>
    </div>
    <div class="card-body p-24">
        @if (count($dados['contratos']) > 0)
        <div class="table-responsive">
            <table class="table bordered-table text-sm" id="dataTable">
                <thead>
                    <tr>
                        <th>Numero</th>
                        <th>Objeto</th>
                        <th>Secretaria</th>
                        <th>Data Fim</th>
                        <th>Efetividade</th>
                        <th>Aditivo</th>
                        <th class="text-center">Dias Antecip.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dados['contratos'] as $contrato)
                    <tr>
                        <td class="fw-semibold">{{ $contrato['numero'] }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($contrato['objeto'], 50) }}</td>
                        <td>{{ $contrato['secretaria'] }}</td>
                        <td>{{ $contrato['data_fim'] }}</td>
                        <td>
                            @php
                                $badgeCor = match($contrato['status_efetividade']) {
                                    'regularizado_a_tempo' => 'success',
                                    'regularizado_retroativo' => 'warning',
                                    'vencido_sem_acao' => 'danger',
                                    default => 'secondary',
                                };
                                $badgeLabel = match($contrato['status_efetividade']) {
                                    'regularizado_a_tempo' => 'Regularizado',
                                    'regularizado_retroativo' => 'Retroativo',
                                    'vencido_sem_acao' => 'Vencido',
                                    default => '-',
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeCor }}-focus text-{{ $badgeCor }}-main px-12 py-4 rounded-pill fw-semibold text-sm">
                                {{ $badgeLabel }}
                            </span>
                        </td>
                        <td>{{ $contrato['aditivo'] }}</td>
                        <td class="text-center">{{ $contrato['dias_antecipacao'] !== null ? $contrato['dias_antecipacao'] . 'd' : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-neutral-500 text-sm mb-0">Nenhum contrato com vencimento no periodo selecionado.</p>
        @endif
    </div>
</div>
@endif
@endsection

@push('scripts')
@if ($dados && $dados['resumo']['total_elegiveis'] > 0)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof ApexCharts !== 'undefined') {
            var options = {
                series: [
                    {{ $dados['resumo']['regularizados_a_tempo'] }},
                    {{ $dados['resumo']['vencidos_sem_acao'] }},
                    {{ $dados['resumo']['regularizados_retroativos'] }}
                ],
                labels: ['Regularizados a Tempo', 'Vencidos sem Acao', 'Regularizados Retroativamente'],
                chart: {
                    type: 'donut',
                    height: 300,
                },
                colors: ['#45B369', '#EF4A00', '#FF9F29'],
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '55%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Efetividade',
                                    formatter: function () {
                                        return '{{ $dados['resumo']['taxa_efetividade'] }}%';
                                    }
                                }
                            }
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: { height: 250 },
                        legend: { position: 'bottom' }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector('#chart-efetividade'), options);
            chart.render();
        }
    });
</script>
@endif
@endpush
