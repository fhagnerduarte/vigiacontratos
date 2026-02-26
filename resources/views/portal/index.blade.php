@extends('portal.layout')

@section('title', 'Portal de Transparencia')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Inicio</li>
@endsection

@section('content')
{{-- Hero --}}
<div class="portal-hero">
    <div class="d-flex align-items-center gap-3">
        <iconify-icon icon="solar:shield-check-bold" width="40" style="color: var(--portal-primary);"></iconify-icon>
        <div>
            <h2>Transparencia Contratual</h2>
            <p>Acompanhe os contratos publicos, fornecedores, aditivos e informacoes de gestao contratual do municipio. Dados atualizados conforme a Lei de Acesso a Informacao (Lei 12.527/2011).</p>
        </div>
    </div>
</div>

{{-- Indicadores --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="stat-icon bg-primary-light">
                <iconify-icon icon="solar:document-bold" width="24"></iconify-icon>
            </div>
            <div class="stat-value" style="color: var(--portal-primary);">{{ number_format($indicadores['total_contratos']) }}</div>
            <p class="stat-label">Contratos Publicados</p>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="stat-icon bg-success-light">
                <iconify-icon icon="solar:wallet-money-bold" width="24"></iconify-icon>
            </div>
            <div class="stat-value" style="color: #28a745;">R$ {{ number_format($indicadores['valor_total'], 2, ',', '.') }}</div>
            <p class="stat-label">Valor Total Contratado</p>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="stat-icon bg-info-light">
                <iconify-icon icon="solar:check-circle-bold" width="24"></iconify-icon>
            </div>
            <div class="stat-value" style="color: #17a2b8;">{{ number_format($indicadores['contratos_vigentes']) }}</div>
            <p class="stat-label">Contratos Vigentes</p>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="stat-icon bg-purple-light">
                <iconify-icon icon="solar:buildings-2-bold" width="24"></iconify-icon>
            </div>
            <div class="stat-value" style="color: #6f42c1;">{{ number_format($indicadores['total_secretarias'] ?? 0) }}</div>
            <p class="stat-label">Secretarias</p>
        </div>
    </div>
</div>

{{-- Grafico + Tabela --}}
@if(!empty($indicadores['por_secretaria']))
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="portal-chart-container">
            <h5><iconify-icon icon="solar:chart-2-bold" width="18"></iconify-icon> Contratos por Secretaria</h5>
            <div id="chartSecretarias"></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card portal-card">
            <div class="card-header">
                <h5 class="mb-0"><iconify-icon icon="solar:list-bold" width="18"></iconify-icon> Ranking por Secretaria</h5>
            </div>
            <div class="card-body p-0">
                <table class="table portal-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Secretaria</th>
                            <th class="text-end">Contratos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($indicadores['por_secretaria'] as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item['nome'] }}</td>
                            <td class="text-end fw-bold">{{ $item['total'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Links Rapidos --}}
<h3 class="portal-section-title">Acesso Rapido</h3>
<div class="row g-3">
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('portal.contratos', $tenant->slug) }}" class="portal-link-card">
            <div class="link-icon"><iconify-icon icon="solar:document-bold" width="22"></iconify-icon></div>
            <h6>Contratos</h6>
            <p>Consulte todos os contratos publicos</p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('portal.fornecedores', $tenant->slug) }}" class="portal-link-card">
            <div class="link-icon"><iconify-icon icon="solar:users-group-rounded-bold" width="22"></iconify-icon></div>
            <h6>Fornecedores</h6>
            <p>Empresas contratadas pelo municipio</p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('portal.lai.create', $tenant->slug) }}" class="portal-link-card">
            <div class="link-icon"><iconify-icon icon="solar:chat-round-dots-bold" width="22"></iconify-icon></div>
            <h6>e-SIC</h6>
            <p>Solicite informacoes publicas</p>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('portal.dados-abertos', $tenant->slug) }}" class="portal-link-card">
            <div class="link-icon"><iconify-icon icon="solar:database-bold" width="22"></iconify-icon></div>
            <h6>Dados Abertos</h6>
            <p>Exporte dados em JSON ou CSV</p>
        </a>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/lib/apexcharts.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/lib/apexcharts.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(!empty($indicadores['por_secretaria']))
    var categories = @json(array_column($indicadores['por_secretaria'], 'nome'));
    var data = @json(array_map('intval', array_column($indicadores['por_secretaria'], 'total')));

    new ApexCharts(document.querySelector('#chartSecretarias'), {
        series: [{ name: 'Contratos', data: data }],
        chart: { type: 'bar', height: Math.max(280, categories.length * 40), toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
        plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 4 } },
        colors: [getComputedStyle(document.documentElement).getPropertyValue('--portal-primary').trim() || '#1b55e2'],
        dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
        xaxis: { categories: categories, labels: { style: { fontSize: '12px' } } },
        yaxis: { labels: { style: { fontSize: '12px' }, maxWidth: 200 } },
        tooltip: { y: { formatter: function(val) { return val + ' contrato(s)'; } } },
        grid: { borderColor: '#f0f0f0' }
    }).render();
    @endif
});
</script>
@endpush
