@extends('layout.layout')

@php
    $title = 'Dashboard';
    $subTitle = 'Painel executivo de gestao contratual';
@endphp

@section('title', 'Dashboard Executivo')

@section('content')

{{-- Bloco 1 — Score de Gestao + Filtros --}}
<div class="row gy-4 mb-24">
    {{-- Score de Gestao (RN-075 a RN-077) --}}
    <div class="col-lg-4">
        <div class="card shadow-none border h-100">
            <div class="card-body p-24 text-center">
                <h6 class="fw-semibold text-primary-light mb-8">Score de Gestao</h6>
                <div id="chartScoreGestao"></div>
                <p class="text-secondary-light text-sm mt-8 mb-0">
                    Atualizado em {{ $dados['data_agregacao'] ?? now()->format('d/m/Y H:i') }}
                </p>
                @if ($isControlador && auth()->user()->hasPermission('dashboard.atualizar'))
                    <form id="formAtualizarDashboard" action="{{ route('tenant.dashboard.atualizar') }}" method="POST" class="mt-12">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary" id="btnAtualizarDashboard">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                            <iconify-icon icon="solar:refresh-bold" class="me-1 btn-icon"></iconify-icon> Atualizar dados
                        </button>
                    </form>
                @endif
                {{-- Acoes rapidas --}}
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-12">
                    @if (auth()->user()->hasPermission('contrato.criar'))
                        <a href="{{ route('tenant.contratos.create') }}" class="btn btn-sm btn-primary-600">
                            <iconify-icon icon="ic:baseline-plus" class="me-1"></iconify-icon> Novo Contrato
                        </a>
                    @endif
                    @if (auth()->user()->hasPermission('alerta.visualizar'))
                        <a href="{{ route('tenant.alertas.index') }}" class="btn btn-sm btn-outline-warning">
                            <iconify-icon icon="solar:bell-bold" class="me-1"></iconify-icon> Alertas
                        </a>
                    @endif
                    @if (auth()->user()->hasPermission('relatorio.visualizar'))
                        <a href="{{ route('tenant.relatorios.index') }}" class="btn btn-sm btn-outline-info">
                            <iconify-icon icon="solar:chart-bold" class="me-1"></iconify-icon> Relatorios
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros inteligentes (RN-073/074) --}}
    <div class="col-lg-8">
        <div class="card shadow-none border h-100">
            <div class="card-body p-16">
                <h6 class="fw-semibold text-primary-light mb-12">Filtros</h6>
                <form method="GET" action="{{ route('tenant.dashboard') }}" class="row g-12 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-4">Secretaria</label>
                        <select name="secretaria_id" class="form-control radius-8 form-select select2" data-placeholder="Todas">
                            <option value="">Todas</option>
                            @foreach ($secretarias as $sec)
                                <option value="{{ $sec->id }}" {{ ($filtros['secretaria_id'] ?? '') == $sec->id ? 'selected' : '' }}>
                                    {{ $sec->sigla ? $sec->sigla . ' - ' : '' }}{{ $sec->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-4">Tipo Contrato</label>
                        <select name="tipo_contrato" class="form-control radius-8 form-select select2" data-placeholder="Todos">
                            <option value="">Todos</option>
                            @foreach ($tiposContrato as $tipo)
                                <option value="{{ $tipo->value }}" {{ ($filtros['tipo_contrato'] ?? '') == $tipo->value ? 'selected' : '' }}>
                                    {{ $tipo->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-primary-light text-sm mb-4">Nivel de Risco</label>
                        <select name="nivel_risco" class="form-control radius-8 form-select select2" data-placeholder="Todos">
                            <option value="">Todos</option>
                            @foreach ($niveisRisco as $nivel)
                                <option value="{{ $nivel->value }}" {{ ($filtros['nivel_risco'] ?? '') == $nivel->value ? 'selected' : '' }}>
                                    {{ $nivel->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary-600 w-100">
                            <iconify-icon icon="ion:search-outline"></iconify-icon>
                        </button>
                        <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary w-100">
                            <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Bloco 2 — Indicadores Financeiros (RN-058 a RN-061) --}}
<div class="row row-cols-xxxl-5 row-cols-lg-5 row-cols-sm-2 row-cols-1 gy-4 mb-24">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Contratos com status Vigente">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Contratos Ativos</p>
                        <h6 class="mb-0" data-countup="{{ $dados['financeiros']['total_contratos_ativos'] }}">{{ $dados['financeiros']['total_contratos_ativos'] }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:document-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Soma do valor global de todos os contratos vigentes">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Valor Contratado</p>
                        <h6 class="mb-0" data-countup="{{ $dados['financeiros']['valor_total_contratado'] }}" data-countup-prefix="R$ " data-countup-decimals="2">R$ {{ number_format($dados['financeiros']['valor_total_contratado'], 2, ',', '.') }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:wallet-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Valor ja executado com base no percentual de execucao">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Valor Executado</p>
                        <h6 class="mb-0" data-countup="{{ $dados['financeiros']['valor_total_executado'] }}" data-countup-prefix="R$ " data-countup-decimals="2">R$ {{ number_format($dados['financeiros']['valor_total_executado'], 2, ',', '.') }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-info-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:chart-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-4 h-100" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Diferenca entre valor contratado e executado">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Saldo Remanescente</p>
                        <h6 class="mb-0" data-countup="{{ $dados['financeiros']['saldo_remanescente'] }}" data-countup-prefix="R$ " data-countup-decimals="2">R$ {{ number_format($dados['financeiros']['saldo_remanescente'], 2, ',', '.') }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:safe-circle-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-5 h-100" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Valor medio por contrato vigente">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Ticket Medio</p>
                        <h6 class="mb-0" data-countup="{{ $dados['financeiros']['ticket_medio'] }}" data-countup-prefix="R$ " data-countup-decimals="2">R$ {{ number_format($dados['financeiros']['ticket_medio'], 2, ',', '.') }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:tag-price-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bloco 3 — Graficos: Mapa de Risco + Vencimentos (RN-062 a RN-067) --}}
<div class="row gy-4 mb-24">
    {{-- Donut Mapa de Risco --}}
    <div class="col-lg-5">
        <div class="card shadow-none border h-100">
            <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                <h6 class="text-lg fw-semibold mb-0">Mapa de Risco</h6>
                <span class="badge bg-success-focus text-success-main px-12 py-6 radius-4">
                    {{ $dados['mapa_risco']['pct_conformes'] }}% conformes
                </span>
            </div>
            <div class="card-body p-24">
                <div id="chartMapaRisco"></div>
                <div class="d-flex justify-content-center gap-24 mt-16">
                    <div class="text-center">
                        <span class="badge bg-success-focus text-success-main px-8 py-4 radius-4">{{ $dados['mapa_risco']['baixo'] }}</span>
                        <p class="text-sm text-secondary-light mb-0 mt-4">Baixo</p>
                    </div>
                    <div class="text-center">
                        <span class="badge bg-warning-focus text-warning-main px-8 py-4 radius-4">{{ $dados['mapa_risco']['medio'] }}</span>
                        <p class="text-sm text-secondary-light mb-0 mt-4">Medio</p>
                    </div>
                    <div class="text-center">
                        <span class="badge bg-danger-focus text-danger-main px-8 py-4 radius-4">{{ $dados['mapa_risco']['alto'] }}</span>
                        <p class="text-sm text-secondary-light mb-0 mt-4">Alto</p>
                    </div>
                </div>
                @if (auth()->user()->hasPermission('painel-risco.visualizar'))
                    <div class="text-center mt-16">
                        <a href="{{ route('tenant.painel-risco.index') }}" class="btn btn-sm btn-outline-primary">
                            <iconify-icon icon="solar:shield-warning-bold" class="me-1"></iconify-icon> Ver detalhes
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bar Vencimentos por Janela --}}
    <div class="col-lg-7">
        <div class="card shadow-none border h-100">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">Vencimentos por Periodo</h6>
            </div>
            <div class="card-body p-24">
                <div id="chartVencimentos"></div>
            </div>
        </div>
    </div>
</div>

{{-- Bloco 4 — Rankings: Secretarias + Fornecedores (RN-068 a RN-080) --}}
<div class="row gy-4 mb-24">
    {{-- Ranking Secretarias --}}
    <div class="col-lg-7">
        <div class="card shadow-none border h-100">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">Ranking por Secretaria</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="px-24 py-12">Secretaria</th>
                                <th class="px-24 py-12 text-center">Contratos</th>
                                <th class="px-24 py-12 text-end">Valor Total</th>
                                <th class="px-24 py-12 text-center">% Risco</th>
                                <th class="px-24 py-12 text-center">Vencendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dados['ranking_secretarias'] as $sec)
                                <tr>
                                    <td class="px-24 py-12">{{ $sec['sigla'] ?? $sec['nome'] }}</td>
                                    <td class="px-24 py-12 text-center">{{ $sec['total_contratos'] }}</td>
                                    <td class="px-24 py-12 text-end">R$ {{ number_format($sec['valor_total'] ?? 0, 2, ',', '.') }}</td>
                                    <td class="px-24 py-12 text-center">
                                        @php
                                            $corRisco = ($sec['pct_risco'] ?? 0) > 30 ? 'danger' : (($sec['pct_risco'] ?? 0) > 10 ? 'warning' : 'success');
                                        @endphp
                                        <span class="badge bg-{{ $corRisco }}-focus text-{{ $corRisco }}-main px-8 py-4 radius-4">
                                            {{ $sec['pct_risco'] ?? 0 }}%
                                        </span>
                                    </td>
                                    <td class="px-24 py-12 text-center">
                                        @if (($sec['vencendo_proximos'] ?? 0) > 0)
                                            <span class="badge bg-warning-focus text-warning-main px-8 py-4 radius-4">{{ $sec['vencendo_proximos'] }}</span>
                                        @else
                                            <span class="text-secondary-light">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-16 text-secondary-light">Nenhuma secretaria com contratos ativos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 10 Fornecedores --}}
    <div class="col-lg-5">
        <div class="card shadow-none border h-100">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">Top 10 Fornecedores</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="px-24 py-12">Fornecedor</th>
                                <th class="px-24 py-12 text-end">Volume</th>
                                <th class="px-24 py-12 text-center">Idx Aditivos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dados['ranking_fornecedores'] as $forn)
                                <tr>
                                    <td class="px-24 py-12">
                                        <span class="d-block text-sm">{{ \Illuminate\Support\Str::limit($forn['razao_social'], 30) }}</span>
                                        <span class="text-xs text-secondary-light">{{ $forn['total_contratos'] }} contratos</span>
                                    </td>
                                    <td class="px-24 py-12 text-end text-sm">R$ {{ number_format($forn['volume_financeiro'], 2, ',', '.') }}</td>
                                    <td class="px-24 py-12 text-center">
                                        <span class="badge bg-{{ $forn['cor_indice'] }}-focus text-{{ $forn['cor_indice'] }}-main px-8 py-4 radius-4">
                                            {{ $forn['indice_aditivos'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-16 text-secondary-light">Nenhum fornecedor encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bloco 5 — Contratos Essenciais (RN-070 a RN-072) --}}
@if (count($dados['contratos_essenciais'] ?? []) > 0)
<div class="card shadow-none border mb-24">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center gap-2">
        <iconify-icon icon="solar:danger-triangle-bold" class="text-warning-main text-xl"></iconify-icon>
        <h6 class="text-lg fw-semibold mb-0">Contratos Essenciais Vencendo em 60 Dias</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-24 py-12">Contrato</th>
                        <th class="px-24 py-12">Objeto</th>
                        <th class="px-24 py-12">Secretaria</th>
                        <th class="px-24 py-12">Categoria</th>
                        <th class="px-24 py-12 text-center">Vencimento</th>
                        <th class="px-24 py-12 text-center">Dias Restantes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dados['contratos_essenciais'] as $ess)
                        <tr>
                            <td class="px-24 py-12 fw-semibold">
                                <a href="{{ route('tenant.contratos.show', $ess['id']) }}" class="text-primary-600">
                                    {{ $ess['numero'] }}
                                </a>
                            </td>
                            <td class="px-24 py-12">{{ \Illuminate\Support\Str::limit($ess['objeto'], 50) }}</td>
                            <td class="px-24 py-12">{{ $ess['secretaria'] }}</td>
                            <td class="px-24 py-12">{{ $ess['categoria_servico'] ?? '-' }}</td>
                            <td class="px-24 py-12 text-center">{{ $ess['data_fim'] }}</td>
                            <td class="px-24 py-12 text-center">
                                @php
                                    $corDias = $ess['dias_restantes'] <= 15 ? 'danger' : ($ess['dias_restantes'] <= 30 ? 'warning' : 'info');
                                @endphp
                                <span class="badge bg-{{ $corDias }}-focus text-{{ $corDias }}-main px-12 py-6 radius-4 fw-semibold">
                                    {{ $ess['dias_restantes'] }} dias
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Bloco 6 — Tendencias (RN-078) --}}
<div class="card shadow-none border mb-24">
    <div class="card-header border-bottom bg-base py-16 px-24">
        <h6 class="text-lg fw-semibold mb-0">Tendencias dos Ultimos 12 Meses</h6>
    </div>
    <div class="card-body p-24">
        <div id="chartTendencias"></div>
    </div>
</div>

{{-- Bloco 7 — Visao Controlador (RN-081 a RN-083) --}}
@if ($isControlador && !empty($dados['visao_controlador']))
<div class="row gy-4 mb-24">
    {{-- Irregularidades --}}
    <div class="col-lg-5">
        <div class="card shadow-none border h-100">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">
                    <iconify-icon icon="solar:shield-warning-bold" class="text-danger-main me-1"></iconify-icon>
                    Irregularidades
                </h6>
            </div>
            <div class="card-body p-24">
                @foreach ($dados['visao_controlador']['irregularidades'] as $irreg)
                    <div class="d-flex align-items-center justify-content-between py-8 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex align-items-center gap-12">
                            <div class="w-36-px h-36-px bg-{{ $irreg['cor'] }}-focus rounded-circle d-flex justify-content-center align-items-center">
                                <iconify-icon icon="{{ $irreg['icone'] }}" class="text-{{ $irreg['cor'] }}-main text-lg"></iconify-icon>
                            </div>
                            <span class="text-sm">{{ $irreg['label'] }}</span>
                        </div>
                        <span class="badge bg-{{ $irreg['cor'] }}-focus text-{{ $irreg['cor'] }}-main px-12 py-6 radius-4 fw-bold">
                            {{ $irreg['total'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Alteracoes Recentes --}}
    <div class="col-lg-7">
        <div class="card shadow-none border h-100">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">
                    <iconify-icon icon="solar:history-bold" class="text-info-main me-1"></iconify-icon>
                    Alteracoes Recentes (30 dias)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="px-24 py-12">Campo</th>
                                <th class="px-24 py-12">De</th>
                                <th class="px-24 py-12">Para</th>
                                <th class="px-24 py-12">Usuario</th>
                                <th class="px-24 py-12">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dados['visao_controlador']['alteracoes_recentes'] as $alt)
                                <tr>
                                    <td class="px-24 py-12 text-sm">{{ $alt['campo'] }}</td>
                                    <td class="px-24 py-12 text-sm text-secondary-light">{{ \Illuminate\Support\Str::limit($alt['anterior'] ?? '-', 30) }}</td>
                                    <td class="px-24 py-12 text-sm">{{ \Illuminate\Support\Str::limit($alt['novo'] ?? '-', 30) }}</td>
                                    <td class="px-24 py-12 text-sm">{{ $alt['usuario'] }}</td>
                                    <td class="px-24 py-12 text-sm text-secondary-light">{{ $alt['data'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-16 text-secondary-light">Nenhuma alteracao nos ultimos 30 dias.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@php
    $corHexMap = ['success' => '#22c55e', 'info' => '#3b82f6', 'warning' => '#f59e0b', 'danger' => '#ef4444'];
    $scoreComHex = array_merge($dados['score_gestao'], [
        'cor_hex' => $corHexMap[$dados['score_gestao']['cor']] ?? '#6b7280',
    ]);
@endphp

@push('scripts')
<script>
    var dashboardData = {
        risco: @json($dados['mapa_risco']),
        vencimentos: @json($dados['vencimentos']),
        tendencias: @json($dados['tendencias_mensais'] ?? []),
        score: @json($scoreComHex)
    };
</script>
<script src="{{ asset('assets/js/dashboard-charts.js') }}"></script>
<script src="{{ asset('assets/js/dashboard-enhancements.js') }}"></script>
@endpush
