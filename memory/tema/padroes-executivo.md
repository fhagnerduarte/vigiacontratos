# Tema — Padrões do Painel Executivo

> Extraído de `banco-de-tema.md`. Carregar quando implementando o dashboard executivo ou configurações.
> Contém: Painel Executivo (Dashboard Estratégico completo com 5 blocos), Configurações.

---

## Padrões de Página

### Padrão: Painel Executivo (Dashboard Estratégico)

```html
@extends('layout.layout')

@php
    $title = 'Painel Executivo';
    $subTitle = 'Visão estratégica da gestão contratual';
    $script = '<script src="' . asset('assets/js/lib/apexcharts.min.js') . '"></script>
               <script src="' . asset('assets/js/dashboardExecutivo.js') . '"></script>';
@endphp

@section('content')
<!-- Score de Gestão Contratual -->
<div class="row mb-24">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body p-24 text-center">
                <h6 class="mb-12 text-neutral-600">Score de Gestão</h6>
                <h2 class="mb-8 fw-bold text-{{ $scoreClasse }}">{{ $scoreGestao }}/100</h2>
                <span class="badge bg-{{ $scoreClasse }}-focus text-{{ $scoreClasse }}-main px-20 py-9 radius-4 text-lg">
                    {{ $scoreClassificacao }}
                </span>
                <div class="progress mt-16" style="height: 8px;">
                    <div class="progress-bar bg-{{ $scoreClasse }}" style="width: {{ $scoreGestao }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <!-- Filtros Inteligentes -->
        <div class="card h-100">
            <div class="card-body p-20">
                <h6 class="mb-12">Filtros</h6>
                <form id="dashboard-filtros">
                    <div class="row gy-3">
                        <div class="col-md-4">
                            <select class="form-select" name="secretaria_id">
                                <option value="">Todas as Secretarias</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="nivel_risco">
                                <option value="">Todos os Riscos</option>
                                <option value="baixo">Baixo</option>
                                <option value="medio">Médio</option>
                                <option value="alto">Alto</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="tipo_contrato">
                                <option value="">Todos os Tipos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="modalidade">
                                <option value="">Todas as Modalidades</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="faixa_valor">
                                <option value="">Todas as Faixas</option>
                                <option value="ate_100k">Até R$ 100.000</option>
                                <option value="100k_500k">R$ 100.000 - R$ 500.000</option>
                                <option value="500k_1m">R$ 500.000 - R$ 1.000.000</option>
                                <option value="acima_1m">Acima de R$ 1.000.000</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="fonte_recurso">
                                <option value="">Todas as Fontes</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-10 justify-content-end mt-12">
                        <button type="reset" class="btn btn-outline-secondary-600 btn-sm">Limpar</button>
                        <button type="submit" class="btn btn-primary-600 btn-sm">
                            <iconify-icon icon="ic:baseline-search" class="icon"></iconify-icon> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- BLOCO 1: Visão Geral Financeira (5 Cards) -->
<div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">
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
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Valor Contratado</p>
                        <h6 class="mb-0">R$ {{ number_format($valorTotalContratado, 2, ',', '.') }}</h6>
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
                        <p class="fw-medium text-primary-light mb-1">Valor Executado</p>
                        <h6 class="mb-0">R$ {{ number_format($valorTotalExecutado, 2, ',', '.') }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:wallet-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-4 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Saldo Remanescente</p>
                        <h6 class="mb-0">R$ {{ number_format($saldoTotal, 2, ',', '.') }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-info-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:wallet-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-5 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Ticket Médio</p>
                        <h6 class="mb-0">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:chart-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BLOCO 2: Mapa de Risco + BLOCO 3: Vencimentos por Janela -->
<div class="row gy-4 mt-1">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Mapa de Risco</h5>
            </div>
            <div class="card-body">
                <div id="chart-risco-donut"></div>
                <div class="d-flex justify-content-around mt-16">
                    <div class="text-center">
                        <span class="badge bg-success-focus text-success-main px-12 py-6 radius-4 mb-4">Baixo</span>
                        <h6>{{ $riscosBaixo }}</h6>
                    </div>
                    <div class="text-center">
                        <span class="badge bg-warning-focus text-warning-main px-12 py-6 radius-4 mb-4">Médio</span>
                        <h6>{{ $riscosMedio }}</h6>
                    </div>
                    <div class="text-center">
                        <span class="badge bg-danger-focus text-danger-main px-12 py-6 radius-4 mb-4">Alto</span>
                        <h6>{{ $riscosAlto }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Vencimentos por Janela de Tempo</h5>
            </div>
            <div class="card-body">
                <div id="chart-vencimentos-janela"></div>
            </div>
        </div>
    </div>
</div>

<!-- BLOCO 4: Ranking por Secretaria -->
<div class="card mt-24">
    <div class="card-header">
        <h5 class="card-title mb-0">Distribuição por Secretaria</h5>
    </div>
    <div class="card-body">
        <table class="table bordered-table mb-0">
            <thead>
                <tr>
                    <th>Secretaria</th>
                    <th class="text-center">Contratos</th>
                    <th class="text-end">Valor Total</th>
                    <th class="text-center">Em Risco</th>
                    <th class="text-center">Vencendo (30d)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rankingSecretarias as $sec)
                <tr>
                    <td>{{ $sec->nome }}</td>
                    <td class="text-center">{{ $sec->total_contratos }}</td>
                    <td class="text-end">R$ {{ number_format($sec->valor_total, 2, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $sec->pct_risco > 30 ? 'danger' : ($sec->pct_risco > 10 ? 'warning' : 'success') }}-focus text-{{ $sec->pct_risco > 30 ? 'danger' : ($sec->pct_risco > 10 ? 'warning' : 'success') }}-main px-12 py-6 radius-4">
                            {{ $sec->pct_risco }}%
                        </span>
                    </td>
                    <td class="text-center">{{ $sec->vencendo_30d }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- BLOCO 5: Contratos Essenciais -->
<div class="card mt-24 border-danger">
    <div class="card-header bg-danger-focus">
        <div class="d-flex align-items-center gap-8">
            <iconify-icon icon="solar:star-bold" class="text-danger-main text-2xl"></iconify-icon>
            <h5 class="card-title mb-0 text-danger-main">Contratos Essenciais — Vencendo em até 60 dias</h5>
        </div>
    </div>
    <div class="card-body">
        @if($essenciaisVencendo->isEmpty())
            <p class="text-success-main fw-medium mb-0">
                <iconify-icon icon="ic:baseline-check-circle" class="icon"></iconify-icon>
                Nenhum contrato essencial vencendo nos próximos 60 dias.
            </p>
        @else
            <table class="table bordered-table mb-0">
                <thead>
                    <tr>
                        <th>Contrato</th>
                        <th>Serviço</th>
                        <th>Secretaria</th>
                        <th>Vencimento</th>
                        <th class="text-center">Dias Restantes</th>
                        <th class="text-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($essenciaisVencendo as $ess)
                    <tr>
                        <td>{{ $ess->numero }}</td>
                        <td>
                            <span class="badge bg-danger-focus text-danger-main px-12 py-6 radius-4">
                                {{ $ess->categoria_servico->nomeExibido() }}
                            </span>
                        </td>
                        <td>{{ $ess->secretaria->nome }}</td>
                        <td>{{ $ess->data_fim->format('d/m/Y') }}</td>
                        <td class="text-center fw-bold text-danger-main">{{ $ess->dias_restantes }}</td>
                        <td class="text-end">R$ {{ number_format($ess->valor_global, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<!-- Tendência Mensal (Mini BI) -->
<div class="row gy-4 mt-1">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Tendência Mensal — Últimos 12 Meses</h5>
            </div>
            <div class="card-body">
                <div id="chart-tendencia-mensal"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Top 10 Fornecedores</h5>
            </div>
            <div class="card-body">
                <div id="chart-ranking-fornecedores"></div>
            </div>
        </div>
    </div>
</div>

<!-- Botão Atualizar Dados (Admin) -->
@if(auth()->user()->tipo === 'admin')
<div class="d-flex justify-content-end mt-24">
    <form action="{{ route('dashboard.atualizar') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline-primary-600">
            <iconify-icon icon="solar:refresh-bold" class="icon"></iconify-icon> Atualizar Dados
        </button>
    </form>
    <small class="text-neutral-400 ms-12 align-self-center">Última atualização: {{ $ultimaAtualizacao }}</small>
</div>
@endif
@endsection
```

### Padrão: Configurações

```html
@extends('layout.layout')

@php
    $title = 'Configurações';
    $subTitle = 'Configurações do sistema';
@endphp

@section('content')
<div class="card">
    <div class="card-body p-24">
        <!-- Tabs -->
        <ul class="nav bordered-tab" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#alertas">Alertas</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#geral">Geral</a></li>
        </ul>
        <div class="tab-content mt-24">
            <div class="tab-pane fade show active" id="alertas">
                <!-- Formulário de configuração de alertas -->
            </div>
            <div class="tab-pane fade" id="geral">
                <!-- Configurações gerais -->
            </div>
        </div>
    </div>
</div>
@endsection
```
</output>
