# Banco de Tema — Referência Visual e UI

> Consultado pelo **Arquiteto** (Agente 04) e **Engenheiro** (Agente 05) ao implementar interfaces.
> Define COMO as páginas devem ser construídas visualmente.

---

## Identificação do Template / Design System

| Campo | Valor |
|---|---|
| Nome | WowDash |
| Versão | Laravel (Bootstrap) |
| Autor/Fonte | ThemeForest |
| Localização | `tmp/templates/wowdash-tailwind-bootstrap-react-next-django-2026-02-10-17-20-47-utc/Laravel` |

---

## Stack Frontend

| Tecnologia | Versão | Uso |
|---|---|---|
| Bootstrap 5 | 5.x | Framework CSS principal |
| jQuery | 3.7.1 | Manipulação DOM, plugins |
| ApexCharts | latest | Gráficos do dashboard |
| DataTables | latest | Tabelas interativas com busca e paginação |
| Iconify Icon | latest | Web component para ícones (iconify-icon) |
| RemixIcon | latest | Ícones via fonte CSS (ri-*) |
| Flatpickr | latest | Seletor de datas |
| jQuery UI | latest | Drag/drop, sortable |
| Magnific Popup | latest | Lightbox para imagens |
| Vite | 5.0 | Build/bundling de assets |

---

## Estrutura de Layout

### Layout Principal (Dashboard/Admin)

```html
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<x-head />
<body>
    <!-- Sidebar / Menu Lateral -->
    <aside class="sidebar">
        <!-- Logo -->
        <div>
            <img src="assets/images/logo.png" />        <!-- modo light -->
            <img src="assets/images/logo-light.png" />  <!-- modo dark -->
            <img src="assets/images/logo-icon.png" />    <!-- sidebar recolhida -->
        </div>

        <!-- Menu -->
        <ul class="sidebar-menu" id="sidebar-menu">
            <li class="dropdown">
                <a href="#"><iconify-icon icon="..."></iconify-icon> Item</a>
                <ul class="sidebar-submenu">
                    <li><a href="#">Sub-item</a></li>
                </ul>
            </li>
        </ul>
    </aside>

    <!-- Conteúdo Principal -->
    <main class="dashboard-main">
        <!-- Header / Navbar -->
        <div class="navbar-header">
            <!-- Toggle sidebar + Search + Theme toggle + Notifications + User dropdown -->
        </div>

        <!-- Page Content -->
        <div class="dashboard-main-body">
            <!-- Breadcrumb -->
            <x-breadcrumb title='Título da Página' subTitle='Subtítulo' />

            <!-- Conteúdo da página -->
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="d-footer">
            <!-- Copyright -->
        </footer>
    </main>
</body>
</html>
```

### Layout de Autenticação (Login/Registro)

```html
<!-- Sem sidebar/navbar — layout independente -->
<section class="auth bg-base d-flex flex-wrap">
    <!-- Coluna esquerda: Ilustração (d-lg-block d-none) -->
    <div class="auth-left">
        <img src="assets/images/auth/auth-img.png" />
    </div>

    <!-- Coluna direita: Formulário -->
    <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
        <div class="max-w-464-px mx-auto w-100">
            <!-- Logo -->
            <!-- Formulário de login/registro -->
            <!-- Social login (opcional) -->
        </div>
    </div>
</section>
```

---

## Classes CSS de Referência

### Navegação / Sidebar

| Classe | Uso |
|---|---|
| `.sidebar` | Container `<aside>` do menu lateral |
| `.sidebar-menu` | `<ul>` principal do menu |
| `.sidebar-submenu` | `<ul>` de submenu expandível |
| `.dropdown` | `<li>` com submenu |
| `.sidebar-toggle` | Botão toggle sidebar (desktop) |
| `.sidebar-mobile-toggle` | Botão toggle sidebar (mobile) |

### Header / Navbar

| Classe | Uso |
|---|---|
| `.navbar-header` | Container da barra superior |
| `data-theme-toggle` | Botão de alternância light/dark |

### Conteúdo

| Classe | Uso |
|---|---|
| `.dashboard-main` | Container `<main>` do conteúdo |
| `.dashboard-main-body` | Área interna do conteúdo (abaixo do header) |
| `.d-footer` | Footer |
| `.card` | Card container |
| `.card-header` | Cabeçalho do card |
| `.card-body` | Corpo do card |
| `.shadow-none` | Remove sombra do card |
| `.border` | Adiciona borda ao card |

### Classes Utilitárias Customizadas (NÃO é Tailwind)

O WowDash usa classes utilitárias customizadas definidas em `style.css`:

| Padrão | Exemplo | Descrição |
|---|---|---|
| `px-{n}` | `px-24` | Padding horizontal |
| `py-{n}` | `py-32` | Padding vertical |
| `mb-{n}` | `mb-16` | Margin bottom |
| `w-{n}-px` | `w-40-px` | Largura fixa em px |
| `h-{n}-px` | `h-56-px` | Altura fixa em px |
| `radius-{n}` | `radius-12` | Border radius |
| `max-w-{n}-px` | `max-w-464-px` | Max-width |
| `text-{cor}-{tom}` | `text-primary-600` | Cor de texto |
| `bg-{cor}-{tom}` | `bg-neutral-200` | Cor de fundo |
| `text-{semantic}-main` | `text-success-main` | Cor semântica principal |
| `bg-gradient-start-{n}` | `bg-gradient-start-1` | Cards com gradiente (1 a 5) |

### Paleta de Cores (CSS Custom Properties)

| Variável | Valor | Uso |
|---|---|---|
| `--primary-600` | #487FFF | Cor principal (azul) |
| `--success-main` | #45B369 | Sucesso (verde) |
| `--warning-main` | #FF9F29 | Atenção (amarelo) |
| `--danger-main` | #EF4A00 | Perigo/erro (vermelho) |
| `--info-main` | #144BD6 | Informação (azul escuro) |
| `--neutral-50` | #F5F6FA | Fundo claro |
| `--neutral-900` | #111827 | Texto mais escuro |
| `--base` | #fff | Fundo branco |

**Tipografia:** Fonte **Inter** (Google Fonts)

---

## Padrões de Página

### Padrão: Listagem (CRUD — Index)

```html
@extends('layout.layout')

@php
    $title = 'Contratos';
    $subTitle = 'Listagem de contratos';
    $script = '<script src="' . asset('assets/js/lib/dataTables.min.js') . '"></script>';
@endphp

@section('content')
<div class="card basic-data-table">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Contratos</h5>
        <a href="{{ route('contratos.create') }}" class="btn btn-primary-600">
            <iconify-icon icon="ic:baseline-plus" class="icon"></iconify-icon> Novo Contrato
        </a>
    </div>
    <div class="card-body">
        <table class="table bordered-table mb-0" id="dataTable" data-page-length="10">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Objeto</th>
                    <th>Fornecedor</th>
                    <th>Vigência</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- linhas da tabela -->
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### Padrão: Formulário Simples (Create / Edit)

```html
@extends('layout.layout')

@php
    $title = 'Novo Fornecedor';
    $subTitle = 'Cadastro de fornecedor';
@endphp

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Dados do Fornecedor</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('fornecedores.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-20">
                    <label class="form-label">Razão Social</label>
                    <input type="text" class="form-control" name="razao_social" required>
                </div>
                <div class="col-md-6 mb-20">
                    <label class="form-label">CNPJ</label>
                    <input type="text" class="form-control" name="cnpj" required>
                </div>
            </div>
            <!-- mais campos -->
            <div class="d-flex gap-10 justify-content-end">
                <a href="{{ route('fornecedores.index') }}" class="btn btn-outline-secondary-600">Cancelar</a>
                <button type="submit" class="btn btn-primary-600">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
```

### Padrão: Formulário Multi-etapa / Wizard (Cadastro de Contrato)

```html
@extends('layout.layout')

@php
    $title = 'Novo Contrato';
    $subTitle = 'Cadastro inteligente de contrato';
@endphp

@section('content')
<!-- Indicador de Etapas -->
<div class="card mb-24">
    <div class="card-body py-20">
        <ul class="wizard-steps d-flex justify-content-between list-unstyled mb-0">
            <li class="wizard-step active" data-step="1">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-primary-600 rounded-circle d-flex align-items-center justify-content-center text-white fw-semibold">1</span>
                    <span class="fw-medium text-primary-600">Identificação</span>
                </div>
            </li>
            <li class="wizard-step" data-step="2">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-neutral-200 rounded-circle d-flex align-items-center justify-content-center text-neutral-600 fw-semibold">2</span>
                    <span class="fw-medium text-neutral-600">Fornecedor</span>
                </div>
            </li>
            <li class="wizard-step" data-step="3">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-neutral-200 rounded-circle d-flex align-items-center justify-content-center text-neutral-600 fw-semibold">3</span>
                    <span class="fw-medium text-neutral-600">Financeiro</span>
                </div>
            </li>
            <li class="wizard-step" data-step="4">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-neutral-200 rounded-circle d-flex align-items-center justify-content-center text-neutral-600 fw-semibold">4</span>
                    <span class="fw-medium text-neutral-600">Vigência</span>
                </div>
            </li>
            <li class="wizard-step" data-step="5">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-neutral-200 rounded-circle d-flex align-items-center justify-content-center text-neutral-600 fw-semibold">5</span>
                    <span class="fw-medium text-neutral-600">Fiscal</span>
                </div>
            </li>
            <li class="wizard-step" data-step="6">
                <div class="d-flex align-items-center gap-8">
                    <span class="w-32-px h-32-px bg-neutral-200 rounded-circle d-flex align-items-center justify-content-center text-neutral-600 fw-semibold">6</span>
                    <span class="fw-medium text-neutral-600">Documentos</span>
                </div>
            </li>
        </ul>
    </div>
</div>

<!-- Formulário com Etapas -->
<form action="{{ route('contratos.store') }}" method="POST" enctype="multipart/form-data" id="wizard-form">
    @csrf

    <!-- Etapa 1: Identificação -->
    <div class="card wizard-panel" data-step="1">
        <div class="card-header">
            <h5 class="card-title mb-0">Identificação do Contrato</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-20">
                    <label class="form-label">Número do Contrato</label>
                    <input type="text" class="form-control" name="numero" placeholder="001/2026" required>
                </div>
                <div class="col-md-4 mb-20">
                    <label class="form-label">Tipo de Contrato</label>
                    <select class="form-select" name="tipo" required>
                        <option value="">Selecione...</option>
                    </select>
                </div>
                <div class="col-md-4 mb-20">
                    <label class="form-label">Modalidade</label>
                    <select class="form-select" name="modalidade_contratacao" required>
                        <option value="">Selecione...</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-20">
                    <label class="form-label">Objeto</label>
                    <textarea class="form-control" name="objeto" rows="3" required></textarea>
                </div>
            </div>
            <!-- Secretaria, processo, fundamento legal... -->
        </div>
    </div>

    <!-- Etapa 2: Fornecedor (hidden por padrão) -->
    <div class="card wizard-panel d-none" data-step="2">
        <!-- Selecionar fornecedor existente ou cadastrar novo -->
    </div>

    <!-- Etapa 3: Financeiro (hidden) -->
    <div class="card wizard-panel d-none" data-step="3">
        <!-- Valor global, mensal, tipo pagamento, dotação, empenho -->
    </div>

    <!-- Etapa 4: Vigência (hidden) -->
    <div class="card wizard-panel d-none" data-step="4">
        <!-- Datas, prazo, prorrogação automática -->
    </div>

    <!-- Etapa 5: Fiscal (hidden) -->
    <div class="card wizard-panel d-none" data-step="5">
        <!-- Nome, matrícula, cargo, email -->
    </div>

    <!-- Etapa 6: Documentos (hidden) -->
    <div class="card wizard-panel d-none" data-step="6">
        <!-- Upload múltiplo com classificação por tipo -->
    </div>

    <!-- Navegação -->
    <div class="d-flex justify-content-between mt-24">
        <button type="button" class="btn btn-outline-secondary-600 wizard-prev d-none">
            <iconify-icon icon="ic:baseline-arrow-back" class="icon"></iconify-icon> Anterior
        </button>
        <div class="ms-auto d-flex gap-10">
            <a href="{{ route('contratos.index') }}" class="btn btn-outline-secondary-600">Cancelar</a>
            <button type="button" class="btn btn-primary-600 wizard-next">
                Próximo <iconify-icon icon="ic:baseline-arrow-forward" class="icon"></iconify-icon>
            </button>
            <button type="submit" class="btn btn-success-600 wizard-submit d-none">
                <iconify-icon icon="ic:baseline-check" class="icon"></iconify-icon> Salvar Contrato
            </button>
        </div>
    </div>
</form>
@endsection
```

### Padrão: Detalhes com Abas (Show — Contrato)

```html
@extends('layout.layout')

@php
    $title = 'Contrato ' . $contrato->numero;
    $subTitle = 'Detalhes do contrato';
@endphp

@section('content')
<!-- Cabeçalho com Score de Risco -->
<div class="card mb-24">
    <div class="card-body p-24">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-4">{{ $contrato->numero }}</h5>
                <p class="text-neutral-600 mb-0">{{ $contrato->objeto }}</p>
            </div>
            <div class="d-flex gap-12 align-items-center">
                <!-- Badge de Status -->
                <span class="badge bg-success-focus text-success-main px-20 py-9 radius-4">Vigente</span>
                <!-- Badge de Risco -->
                <span class="badge bg-warning-focus text-warning-main px-20 py-9 radius-4">
                    🟡 Risco Médio (Score: 35)
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Abas de Conteúdo -->
<div class="card">
    <div class="card-body p-24">
        <ul class="nav bordered-tab" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#dados">Dados Gerais</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#fiscal">Fiscal</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#financeiro">Financeiro</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#documentos">Documentos</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#aditivos">Aditivos</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#auditoria">Auditoria</a></li>
        </ul>
        <div class="tab-content mt-24">
            <div class="tab-pane fade show active" id="dados">
                <!-- Dados gerais do contrato em grid -->
            </div>
            <div class="tab-pane fade" id="fiscal">
                <!-- Fiscal atual + histórico de trocas -->
            </div>
            <div class="tab-pane fade" id="financeiro">
                <!-- Execuções financeiras + percentual executado + barra de progresso -->
            </div>
            <div class="tab-pane fade" id="documentos">
                <!-- Módulo 5: barra de completude + checklist obrigatório + documentos agrupados por tipo + modal upload -->
                <!-- Ver padrão "Aba Documentos Expandida" para implementação completa -->
            </div>
            <div class="tab-pane fade" id="aditivos">
                <!-- Timeline/lista de aditivos -->
            </div>
            <div class="tab-pane fade" id="auditoria">
                <!-- Log de alterações (campo, anterior, novo, quem, quando) -->
            </div>
        </div>
    </div>
</div>
@endsection
```

### Padrão: Dashboard

```html
@extends('layout.layout')

@php
    $title = 'Dashboard';
    $subTitle = 'Visão geral';
    $script = '<script src="' . asset('assets/js/lib/apexcharts.min.js') . '"></script>
               <script src="' . asset('assets/js/dashboardChart.js') . '"></script>';
@endphp

@section('content')
<!-- Cards de Estatísticas -->
<div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Contratos Vigentes</p>
                        <h6 class="mb-0">125</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:document-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- mais cards -->
</div>

<!-- Gráficos -->
<div class="row gy-4 mt-1">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Contratos por Mês</h5>
            </div>
            <div class="card-body">
                <div id="chart-contratos"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Por Tipo</h5>
            </div>
            <div class="card-body">
                <div id="chart-tipos"></div>
            </div>
        </div>
    </div>
</div>
@endsection
```

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

### Padrão: Dashboard de Alertas

```html
@extends('layout.layout')

@php
    $title = 'Alertas';
    $subTitle = 'Dashboard de alertas de vencimento';
    $script = '<script src="' . asset('assets/js/lib/apexcharts.min.js') . '"></script>';
@endphp

@section('content')
<!-- Cards de Indicadores por Faixa de Vencimento -->
<div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Vencendo em 120 dias</p>
                        <h6 class="mb-0">{{ $vencendo120d }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-info-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:bell-bold" class="text-white text-2xl"></iconify-icon>
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
                        <p class="fw-medium text-primary-light mb-1">Vencendo em 60 dias</p>
                        <h6 class="mb-0">{{ $vencendo60d }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:bell-bold" class="text-white text-2xl"></iconify-icon>
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
                        <p class="fw-medium text-primary-light mb-1">Vencendo em 30 dias</p>
                        <h6 class="mb-0">{{ $vencendo30d }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:bell-bold" class="text-white text-2xl"></iconify-icon>
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
                        <p class="fw-medium text-primary-light mb-1">Vencidos</p>
                        <h6 class="mb-0 text-danger-main">{{ $vencidos }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:danger-triangle-bold" class="text-white text-2xl"></iconify-icon>
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
                        <p class="fw-medium text-primary-light mb-1">Secretarias em Risco</p>
                        <h6 class="mb-0">{{ $secretariasRisco }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:case-round-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mt-24">
    <div class="card-body p-20">
        <div class="row gy-3">
            <div class="col-md-3">
                <select class="form-select" name="secretaria_id">
                    <option value="">Todas as Secretarias</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="prioridade">
                    <option value="">Todas as Prioridades</option>
                    <option value="informativo">Informativo</option>
                    <option value="atencao">Atenção</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="tipo_contrato">
                    <option value="">Todos os Tipos</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="faixa_valor">
                    <option value="">Todas as Faixas</option>
                    <option value="ate_100k">Até R$ 100.000</option>
                    <option value="100k_500k">R$ 100.000 - R$ 500.000</option>
                    <option value="acima_500k">Acima de R$ 500.000</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Alertas -->
<div class="card basic-data-table mt-24">
    <div class="card-header">
        <h5 class="card-title mb-0">Alertas Ativos</h5>
    </div>
    <div class="card-body">
        <table class="table bordered-table mb-0" id="dataTable" data-page-length="10">
            <thead>
                <tr>
                    <th>Contrato</th>
                    <th>Objeto</th>
                    <th>Secretaria</th>
                    <th>Vencimento</th>
                    <th>Dias Restantes</th>
                    <th>Prioridade</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Linha de alerta com badge de prioridade -->
                <tr>
                    <td>001/2026</td>
                    <td>Transporte escolar</td>
                    <td>Educação</td>
                    <td>30/06/2026</td>
                    <td>25</td>
                    <td>
                        <span class="badge bg-danger-focus text-danger-main px-20 py-9 radius-4">Urgente</span>
                    </td>
                    <td>
                        <span class="badge bg-warning-focus text-warning-main px-20 py-9 radius-4">Enviado</span>
                    </td>
                    <td>
                        <a href="#" class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:eye-bold"></iconify-icon>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### Padrão: Configuração de Alertas (Admin)

```html
<!-- Dentro da tab "Alertas" em configuracoes/index.blade.php -->
<div class="tab-pane fade show active" id="alertas">
    <h6 class="mb-16">Prazos de Antecedência para Alertas</h6>
    <p class="text-neutral-600 mb-24">Configure quantos dias antes do vencimento o sistema deve gerar alertas.</p>

    <form action="{{ route('configuracoes.alertas.update') }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($configuracoes as $config)
        <div class="row align-items-center mb-16 pb-16 border-bottom">
            <div class="col-md-3">
                <label class="form-label fw-semibold mb-0">{{ $config->dias_antecedencia }} dias antes</label>
            </div>
            <div class="col-md-3">
                <!-- Badge de prioridade -->
                @if($config->prioridade === 'urgente')
                    <span class="badge bg-danger-focus text-danger-main px-20 py-9 radius-4">Urgente</span>
                @elseif($config->prioridade === 'atencao')
                    <span class="badge bg-warning-focus text-warning-main px-20 py-9 radius-4">Atenção</span>
                @else
                    <span class="badge bg-info-focus text-info-main px-20 py-9 radius-4">Informativo</span>
                @endif
            </div>
            <div class="col-md-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="ativo[{{ $config->id }}]"
                           {{ $config->is_ativo ? 'checked' : '' }}>
                    <label class="form-check-label">Ativo</label>
                </div>
            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-end mt-24">
            <button type="submit" class="btn btn-primary-600">Salvar Configurações</button>
        </div>
    </form>
</div>
```

### Padrão: Badge de Notificação no Navbar

```html
<!-- Dentro do navbar — sino de notificações -->
<div class="dropdown">
    <button class="position-relative" data-bs-toggle="dropdown">
        <iconify-icon icon="solar:bell-bold" class="text-2xl"></iconify-icon>
        @if($alertasPendentes > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger-main">
                {{ $alertasPendentes > 99 ? '99+' : $alertasPendentes }}
            </span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 350px;">
        <div class="p-16 border-bottom">
            <h6 class="mb-0">Alertas de Vencimento</h6>
        </div>
        <div class="max-h-300-px overflow-auto">
            @foreach($alertasRecentes as $alerta)
            <a href="{{ route('alertas.show', $alerta) }}" class="d-flex align-items-start gap-12 p-16 border-bottom hover-bg-neutral-50">
                <!-- Ícone por prioridade -->
                @if($alerta->prioridade === 'urgente')
                    <span class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <iconify-icon icon="solar:danger-triangle-bold"></iconify-icon>
                    </span>
                @elseif($alerta->prioridade === 'atencao')
                    <span class="w-32-px h-32-px bg-warning-focus text-warning-main rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <iconify-icon icon="solar:bell-bold"></iconify-icon>
                    </span>
                @else
                    <span class="w-32-px h-32-px bg-info-focus text-info-main rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <iconify-icon icon="solar:info-circle-bold"></iconify-icon>
                    </span>
                @endif
                <div>
                    <p class="fw-medium text-neutral-900 mb-4">{{ $alerta->contrato->numero }}</p>
                    <p class="text-neutral-600 text-sm mb-0">{{ $alerta->mensagem }}</p>
                    <p class="text-neutral-400 text-xs mb-0">{{ $alerta->created_at->diffForHumans() }}</p>
                </div>
            </a>
            @endforeach
        </div>
        <a href="{{ route('alertas.index') }}" class="d-block text-center py-12 fw-medium text-primary-600">
            Ver todos os alertas
        </a>
    </div>
</div>
```

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

### Padrão: Central de Documentos (documentos/index.blade.php)

```html
@extends('layout.layout')

@php
    $title = 'Central de Documentos';
    $subTitle = 'Gestão centralizada de documentos contratuais';
    $script = '<script src="' . asset('assets/js/lib/dataTables.min.js') . '"></script>';
@endphp

@section('content')
<!-- Cards de Indicadores de Completude (RN-132) -->
<div class="row row-cols-lg-4 row-cols-sm-2 row-cols-1 gy-4 mb-24">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Documentação Completa</p>
                        <h6 class="mb-0 text-success-main">{{ $pctCompletos }}%</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:check-circle-bold" class="text-white text-2xl"></iconify-icon>
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
                        <p class="fw-medium text-primary-light mb-1">Sem Contrato Original</p>
                        <h6 class="mb-0 text-danger-main">{{ $totalSemContratoOriginal }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:danger-triangle-bold" class="text-white text-2xl"></iconify-icon>
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
                        <p class="fw-medium text-primary-light mb-1">Aditivos sem Documento</p>
                        <h6 class="mb-0 text-warning-main">{{ $totalAditivosSemDoc }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-warning-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:folder-error-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Secretarias com Pendências</p>
                        <h6 class="mb-0">{{ $secretariasPendentes }}</h6>
                    </div>
                    <div class="w-40-px h-40-px bg-primary-600 rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:case-round-bold" class="text-white text-2xl"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Busca e Filtros (RN-131) -->
<div class="card mb-24">
    <div class="card-body p-20">
        <form id="filtros-documentos" method="GET">
            <div class="row gy-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="numero_contrato" placeholder="Número do contrato" value="{{ request('numero_contrato') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="tipo_documento">
                        <option value="">Todos os tipos</option>
                        @foreach($tiposDocumento as $tipo)
                        <option value="{{ $tipo->value }}" {{ request('tipo_documento') === $tipo->value ? 'selected' : '' }}>
                            {{ $tipo->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="secretaria_id">
                        <option value="">Todas as secretarias</option>
                        @foreach($secretarias as $sec)
                        <option value="{{ $sec->id }}" {{ request('secretaria_id') == $sec->id ? 'selected' : '' }}>{{ $sec->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="completude">
                        <option value="">Qualquer completude</option>
                        <option value="completo">Completo</option>
                        <option value="parcial">Parcial</option>
                        <option value="incompleto">Incompleto</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="data_upload_de" placeholder="Upload de">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="data_upload_ate" placeholder="Upload até">
                </div>
                <div class="col-md-6 d-flex gap-10 align-items-end justify-content-end">
                    <a href="{{ route('documentos.index') }}" class="btn btn-outline-secondary-600">Limpar</a>
                    <button type="submit" class="btn btn-primary-600">
                        <iconify-icon icon="ic:baseline-search" class="icon"></iconify-icon> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Listagem de Contratos com Completude -->
<div class="card basic-data-table">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Contratos e Documentação</h5>
    </div>
    <div class="card-body">
        <table class="table bordered-table mb-0" id="dataTable" data-page-length="15">
            <thead>
                <tr>
                    <th>Contrato</th>
                    <th>Objeto</th>
                    <th>Secretaria</th>
                    <th class="text-center">Documentos</th>
                    <th class="text-center">Completude</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contratos as $contrato)
                <tr>
                    <td>{{ $contrato->numero }}</td>
                    <td class="text-truncate" style="max-width: 200px;">{{ $contrato->objeto }}</td>
                    <td>{{ $contrato->secretaria->nome }}</td>
                    <td class="text-center">{{ $contrato->documentos->where('is_versao_atual', true)->count() }}</td>
                    <td class="text-center">
                        @php $completude = $contrato->status_completude; @endphp
                        @if($completude === 'completo')
                            <span class="badge bg-success-focus text-success-main px-12 py-6 radius-4">Completo</span>
                        @elseif($completude === 'parcial')
                            <span class="badge bg-warning-focus text-warning-main px-12 py-6 radius-4">Parcial</span>
                        @else
                            <span class="badge bg-danger-focus text-danger-main px-12 py-6 radius-4">Incompleto</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('contratos.show', $contrato) }}#documentos"
                           class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:folder-bold"></iconify-icon>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

### Padrão: Aba Documentos Expandida (contratos/show.blade.php — tab #documentos)

```html
<!-- Aba Documentos — conteúdo expandido para Módulo 5 -->
<div class="tab-pane fade" id="documentos">

    <!-- Barra de Completude Documental -->
    <div class="d-flex align-items-center justify-content-between mb-20 p-16 border rounded
        {{ $contrato->status_completude === 'completo' ? 'bg-success-focus' :
           ($contrato->status_completude === 'parcial' ? 'bg-warning-focus' : 'bg-danger-focus') }}">
        <div class="d-flex align-items-center gap-12">
            @if($contrato->status_completude === 'completo')
                <iconify-icon icon="solar:check-circle-bold" class="text-success-main text-2xl"></iconify-icon>
                <span class="fw-semibold text-success-main">Documentação Completa</span>
            @elseif($contrato->status_completude === 'parcial')
                <iconify-icon icon="solar:danger-triangle-bold" class="text-warning-main text-2xl"></iconify-icon>
                <span class="fw-semibold text-warning-main">Documentação Parcial — itens pendentes no checklist</span>
            @else
                <iconify-icon icon="solar:close-circle-bold" class="text-danger-main text-2xl"></iconify-icon>
                <span class="fw-semibold text-danger-main">Documentação Incompleta — contrato original ausente</span>
            @endif
        </div>
        @can('create', App\Models\Documento::class)
        <button class="btn btn-primary-600 btn-sm" data-bs-toggle="modal" data-bs-target="#modalUploadDocumento">
            <iconify-icon icon="solar:upload-bold" class="icon"></iconify-icon> Adicionar Documento
        </button>
        @endcan
    </div>

    <!-- Checklist de Documentos Obrigatórios (RN-129) -->
    <div class="mb-24">
        <h6 class="fw-semibold mb-12">Checklist de Documentos Obrigatórios</h6>
        <div class="row gy-2">
            @foreach($checklistObrigatorio as $item)
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-8 p-12 border rounded">
                    @if($item['presente'])
                        <iconify-icon icon="ic:baseline-check-circle" class="text-success-main text-xl flex-shrink-0"></iconify-icon>
                    @else
                        <iconify-icon icon="ic:baseline-cancel" class="text-danger-main text-xl flex-shrink-0"></iconify-icon>
                    @endif
                    <span class="text-sm {{ $item['presente'] ? 'text-neutral-700' : 'text-danger-main fw-medium' }}">
                        {{ $item['label'] }}
                    </span>
                    @if($item['presente'])
                        <span class="badge bg-neutral-100 text-neutral-600 ms-auto text-xs">v{{ $item['versao'] }}</span>
                    @else
                        <span class="badge bg-danger-focus text-danger-main ms-auto text-xs">Pendente</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Documentos Agrupados por Tipo -->
    @foreach($documentosPorTipo as $tipo => $docs)
    <div class="mb-20">
        <h6 class="fw-semibold text-neutral-700 mb-12 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:folder-bold" class="text-primary-600"></iconify-icon>
            {{ $tipo }}
            <span class="badge bg-neutral-200 text-neutral-600 ms-2">{{ $docs->count() }}</span>
        </h6>
        @foreach($docs as $doc)
        <div class="d-flex align-items-center gap-12 p-12 border rounded mb-8
            {{ $doc->is_versao_atual ? '' : 'opacity-75 bg-neutral-50' }}">
            <iconify-icon icon="solar:file-bold" class="text-primary-600 text-xl flex-shrink-0"></iconify-icon>
            <div class="flex-grow-1">
                <p class="fw-medium mb-0 text-sm">{{ $doc->nome_original }}</p>
                <p class="text-neutral-400 text-xs mb-0">
                    v{{ $doc->versao }} — {{ number_format($doc->tamanho / 1024 / 1024, 2) }} MB
                    — {{ $doc->created_at->format('d/m/Y H:i') }}
                    — por {{ $doc->uploader->name }}
                    @if(!$doc->is_versao_atual)
                        <span class="badge bg-neutral-200 text-neutral-600 ms-2">Versão anterior</span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-8 flex-shrink-0">
                @can('download', $doc)
                <a href="{{ route('documentos.download', $doc) }}"
                   class="w-32-px h-32-px bg-primary-focus text-primary-main rounded-circle d-inline-flex align-items-center justify-content-center">
                    <iconify-icon icon="solar:download-bold"></iconify-icon>
                </a>
                @endcan
                @can('delete', $doc)
                @if($doc->is_versao_atual)
                <form action="{{ route('documentos.destroy', $doc) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit"
                       class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                       onclick="return confirm('Excluir este documento?')">
                        <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>

<!-- Modal de Upload de Documento -->
<div class="modal fade" id="modalUploadDocumento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('documentos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="documentable_type" value="App\Models\Contrato">
                <input type="hidden" name="documentable_id" value="{{ $contrato->id }}">
                <div class="modal-body">
                    <div class="mb-16">
                        <label class="form-label">Tipo de Documento <span class="text-danger-main">*</span></label>
                        <select class="form-select" name="tipo_documento" required>
                            <option value="">Selecione o tipo...</option>
                            @foreach($tiposDocumento as $tipo)
                            <option value="{{ $tipo->value }}">{{ $tipo->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-16">
                        <label class="form-label">Arquivo PDF <span class="text-danger-main">*</span></label>
                        <input type="file" class="form-control" name="arquivo" accept=".pdf" required>
                        <small class="text-neutral-400">Apenas PDF. Tamanho máximo: 20MB</small>
                    </div>
                    <div class="mb-16">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" name="descricao" placeholder="Descrição opcional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-600">
                        <iconify-icon icon="solar:upload-bold" class="icon"></iconify-icon> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

## Componentes Reutilizáveis

| Componente | Classes HTML | Uso no Projeto |
|---|---|---|
| Card | `.card .card-header .card-body` | Containers de conteúdo em todas as páginas |
| Card com gradiente | `.card .bg-gradient-start-{1-5}` | Cards de estatísticas do dashboard |
| Botão Primário | `.btn .btn-primary-600` | Ações principais (salvar, criar) |
| Botão Secundário | `.btn .btn-outline-secondary-600` | Ações secundárias (cancelar, voltar) |
| Botão Success | `.btn .btn-success-600` | Ações de confirmação |
| Botão Danger | `.btn .btn-danger-600` | Ações destrutivas (excluir) |
| Botão de Ícone | `.w-32-px .h-32-px .bg-{cor}-focus ...` | Ações em tabelas (editar, excluir, ver) |
| Badge / Tag | `.badge .bg-{cor}-focus .text-{cor}-main .px-20 .py-9 .radius-4` | Status de contratos, prioridade de alertas |
| Breadcrumb | `<x-breadcrumb title='' subTitle='' />` | Navegação hierárquica em todas as páginas |
| Dropdown | `.dropdown .dropdown-menu` + `data-bs-toggle="dropdown"` | Menus de ação em tabelas |
| Input Group | `.input-group` (Bootstrap padrão) | Campos com prefixo (R$) ou ícone |
| Icon Field | `.icon-field` com `.icon` dentro | Campos de formulário com ícone |
| Tabela (DataTable) | `.table .bordered-table` + DataTables JS | Listagens com busca e paginação |
| Modal | `.modal` (Bootstrap padrão) | Confirmações de exclusão |
| Toast | Bootstrap Toast | Notificações de sucesso/erro |
| Tabs | `.nav .bordered-tab` + `.tab-content` | Configurações, detalhes de contrato (6 abas) |
| Wizard Steps | `.wizard-steps` + `.wizard-panel` + `.wizard-step` | Formulário multi-etapa de cadastro de contrato |
| Progress Bar | `.progress .progress-bar .bg-{cor}` | Percentual executado do contrato |
| Risk Badge | `.badge .bg-{cor}-focus .text-{cor}-main` | Score de risco (🟢/🟡/🔴) |
| Audit Log | `.table .bordered-table` (read-only) | Tabela de histórico de alterações |
| Notification Badge | `.badge .rounded-pill .bg-danger-main` (dentro do sino) | Contador de alertas pendentes no navbar |
| Notification Dropdown | `.dropdown-menu` com lista scrollável `.max-h-300-px` | Lista de alertas recentes no navbar |
| Alert Priority Badge | `.badge .bg-{cor}-focus .text-{cor}-main` | Prioridade do alerta (urgente=vermelho, atenção=amarelo, informativo=azul) |
| Indicator Card | `.card .bg-gradient-start-{n}` com ícone circular | Cards de contagem no dashboard de alertas |
| Filter Bar | `.card` com `.row` de selects `.form-select` | Barra de filtros combinados |
| Score Gauge | `.card` com número grande `h2` + `.progress` + `.badge` classificação | Nota de gestão contratual 0-100 no painel executivo |
| Donut Chart | `.card` + `<div id="chart-risco-donut">` (ApexCharts donut) | Mapa de risco contratual (3 faixas: baixo/médio/alto) |
| Bar Chart Horizontal | `.card` + `<div id="chart-vencimentos-janela">` (ApexCharts bar) | Distribuição de vencimentos por janela temporal |
| Ranking Table | `.table .bordered-table` sem paginação, ordenada por valor | Ranking de secretarias no painel executivo |
| Essential Alert Panel | `.card .border-danger` + `.card-header .bg-danger-focus` | Painel de destaque para contratos essenciais vencendo |
| Monetary Card | `.card .bg-gradient-start-{n}` com `R$` + `number_format()` | Cards de valores financeiros formatados |
| Filter Bar Extended | `.card` com `.row` de 6 selects + botões filtrar/limpar | Filtros inteligentes do dashboard executivo (6 critérios) |
| Trend Chart | `.card` + `<div id="chart-tendencia-mensal">` (ApexCharts line/area) | Tendência mensal de indicadores (mini BI) |
| Supplier Ranking Chart | `.card` + `<div id="chart-ranking-fornecedores">` (ApexCharts bar horizontal) | Top 10 fornecedores por volume financeiro |
| Timeline de Aditivos | `.list-unstyled` com items de número circular `.w-40-px .h-40-px .rounded-circle` + dados por aditivo | Lista cronológica de aditivos de um contrato com número sequencial destacado e item ativo com `bg-primary-50` |
| Barra de Limite Legal | `.progress .progress-bar .bg-{cor}` + `.badge` de percentual vs. limite | Indicador visual de percentual acumulado em relação ao limite legal configurado (verde/amarelo/vermelho) |
| Painel de Reequilíbrio | `.border .rounded .p-16 .bg-neutral-50` com `.row` de dados | Bloco condicional de dados específicos de reequilíbrio econômico-financeiro (índice, motivo, valores antes/depois) |
| Aditivos Indicator Card | `.card .bg-gradient-start-{n}` com ícone `solar:add-circle-bold` | Cards de contagem e valores no dashboard de aditivos |
| Completude Badge | `.badge .bg-{cor}-focus .text-{cor}-main` (verde/amarelo/vermelho) | Status de completude documental (completo/parcial/incompleto) |
| Completude Status Bar | `.d-flex .p-16 .border .rounded .bg-{cor}-focus` com ícone e texto | Barra de status de completude no topo da aba de documentos |
| Document Checklist | `.row .gy-2` com items `.d-flex .gap-8 .p-12 .border .rounded` | Checklist de documentos obrigatórios com check/cancel + badge de versão ou "Pendente" |
| Upload Modal | `.modal` com form `enctype="multipart/form-data"` + select tipo + file input + descrição | Modal de upload de documento com seleção de tipo obrigatória |
| Document Group | `div.mb-20` com título (tipo) + lista de docs `.d-flex .gap-12 .p-12 .border .rounded` | Documentos agrupados por tipo com versão, tamanho, data, uploader e botões de ação |
| Document Indicator Card | `.card .bg-gradient-start-{n}` com ícone contextual | Cards de indicadores no dashboard de documentos (4 métricas) |
| Document Filter Bar | `.card` com `.row` de inputs + selects + botões buscar/limpar | Barra de filtros combinados na Central de Documentos (6 campos) |

---

## Sistema de Tema

| Opção | Valores | Persistência |
|---|---|---|
| Modo | Light / Dark | localStorage (atributo `data-theme` no `<html>`) |
| Sidebar | Expandida / Recolhida | Toggle via `.sidebar-toggle` |

**Dark Mode:** Ativado por `data-theme="dark"` no `<html>`. Variáveis CSS:
- `--dark-1: #1B2431` (fundo principal)
- `--dark-2: #273142` (fundo cards)
- `--dark-3: #323D4E` (bordas)

---

## Inventário de Páginas do Template

| Categoria | Página | Arquivo no Template | Uso no vigiacontratos |
|---|---|---|---|
| Dashboard | AI Dashboard | `dashboard/index.blade.php` | Base para Dashboard principal |
| Dashboard | CRM | `dashboard/index2.blade.php` | Referência para layout de indicadores |
| Auth | Login | `authentication/signin.blade.php` | Login do sistema |
| Auth | Forgot Password | `authentication/forgotPassword.blade.php` | Recuperação de senha |
| CRUD | Users List | `users/users-list.blade.php` | Listagem de contratos/fornecedores |
| CRUD | Add User | `users/add-user.blade.php` | Formulário de cadastro |
| CRUD | View Profile | `users/view-profile.blade.php` | Detalhes de contrato |
| Invoice | Invoice List | `invoice/invoice-list.blade.php` | Referência para listagem com valores |
| Invoice | Invoice Preview | `invoice/invoice-preview.blade.php` | Referência para visualização de contrato |
| Settings | Theme | `settings/theme.blade.php` | Configurações do sistema |
| Settings | Notification | `settings/notification.blade.php` | Configuração de alertas |
| Table | DataTable | `table/tabledata.blade.php` | Referência para tabelas |
| Chart | Column Chart | `chart/columnchart.blade.php` | Gráficos de barras |
| Chart | Pie Chart | `chart/piechart.blade.php` | Gráficos de pizza |
| Components | Alert | `componentspage/alert.blade.php` | Alertas visuais |
| Components | Badge | `componentspage/badges.blade.php` | Status em tabelas |
| Role | Role & Access | `roleandaccess/roleAaccess.blade.php` | Referência para perfis de acesso |

---

## Mapeamento Template → Módulos do Sistema

| Módulo do Sistema | Página Template Base | Adaptações Necessárias |
|---|---|---|
| Dashboard Executivo | `dashboard/index.blade.php` | Painel completo com 5 blocos: (1) 5 cards financeiros com gradiente, (2) donut chart de risco, (3) bar chart de vencimentos por janela, (4) ranking de secretarias em tabela, (5) painel de essenciais com borda danger. Score de gestão 0-100 com progress bar. Filtros inteligentes (6 critérios). Tendência mensal e ranking de fornecedores. JS: `dashboardExecutivo.js` |
| Contratos — Listagem | `users/users-list.blade.php` + `table/tabledata.blade.php` | Adaptar colunas para dados de contrato, adicionar badges de status |
| Contratos — Cadastro | `users/add-user.blade.php` + `forms/form-layout.blade.php` | **Wizard multi-etapa** (6 passos): Identificação, Fornecedor, Financeiro, Vigência, Fiscal, Documentos |
| Contratos — Detalhes | `users/view-profile.blade.php` + `invoice/invoice-preview.blade.php` | **Detalhes com abas** (6 abas): Dados, Fiscal, Financeiro, Documentos, Aditivos, Auditoria. Inclui score de risco e percentual executado |
| Fornecedores | `users/users-list.blade.php` + `users/add-user.blade.php` | Adaptar para dados de fornecedor (CNPJ, contato) |
| Aditivos — Listagem | `invoice/invoice-list.blade.php` | Adaptar para lista de aditivos com tipo, percentual acumulado, status |
| Aditivos — Criação | `users/add-user.blade.php` + `forms/form-layout.blade.php` | Formulário com campos condicionais por tipo, exibição em tempo real de percentual acumulado, alerta de limite legal |
| Aditivos — Detalhes / Timeline | `users/view-profile.blade.php` | Timeline lateral com histórico de todos os aditivos do contrato + detalhes do aditivo atual (barra de limite, campos de reequilíbrio condicionais) |
| Alertas — Dashboard | `dashboard/index.blade.php` + `dashboard/index2.blade.php` | **Dashboard de alertas** com 5 cards indicadores (120d/60d/30d/vencidos/secretarias), filtros combinados, tabela de alertas ativos |
| Alertas — Listagem | `settings/notification.blade.php` + `table/tabledata.blade.php` | Listagem com filtros por secretaria, prioridade, tipo contrato, faixa valor. Badges de prioridade e status |
| Alertas — Config | `settings/notification.blade.php` | Configuração de prazos (6 linhas) com toggle ativo/inativo. Apenas admin |
| Documentos — Central | `table/tabledata.blade.php` + `dashboard/index.blade.php` | Central de Documentos com 4 indicadores de completude, busca combinada (6 filtros), tabela de contratos com badge de completude (verde/amarelo/vermelho) |
| Documentos — Aba | `users/view-profile.blade.php` + `componentspage/imageUpload.blade.php` | Aba expandida no show do contrato: barra de completude, checklist obrigatório, documentos agrupados por tipo com versionamento, modal de upload com seleção de tipo |
| Relatórios | `chart/columnchart.blade.php` + `chart/piechart.blade.php` | Gráficos de relatórios |
| Secretarias | `users/users-list.blade.php` + `users/add-user.blade.php` | CRUD simples |
| Usuários | `users/users-list.blade.php` + `users/add-user.blade.php` | Já pronto no template |
| Configurações | `settings/notification.blade.php` + `settings/theme.blade.php` | Config de alertas + tema |
| Login | `authentication/signin.blade.php` | Trocar logo e textos |
| Forgot Password | `authentication/forgotPassword.blade.php` | Trocar logo e textos |

---

## Ordem de Carregamento de Assets

### CSS (ordem importa)
1. `assets/css/lib/bootstrap.min.css` — Framework CSS
2. `assets/css/lib/*.css` — Vendors (ApexCharts, DataTables, Flatpickr, etc.)
3. `assets/css/remixicon.css` — Ícones RemixIcon
4. `assets/css/style.css` — CSS principal do WowDash (tema + utilitários customizados)

### JS (ordem importa)
1. `assets/js/lib/jquery-3.7.1.min.js` — jQuery
2. `assets/js/lib/bootstrap.bundle.min.js` — Bootstrap JS + Popper
3. `assets/js/lib/*.js` — Vendors (ApexCharts, DataTables, Iconify, etc.)
4. `assets/js/app.js` — JS principal (sidebar, theme toggle, active menu)
5. `assets/js/{page}.js` — JS específico da página (passado via `$script`)

---

## Integração com o Framework

### Estrutura de Views/Templates

```
resources/views/
├── layout/
│   └── layout.blade.php           (layout principal com sidebar/navbar)
├── components/
│   ├── head.blade.php              (<x-head /> — CSS links)
│   ├── sidebar.blade.php           (<x-sidebar /> — menu lateral)
│   ├── navbar.blade.php            (<x-navbar /> — barra superior)
│   ├── breadcrumb.blade.php        (<x-breadcrumb /> — título + breadcrumb)
│   ├── footer.blade.php            (<x-footer /> — rodapé)
│   └── script.blade.php            (<x-script /> — JS scripts)
├── admin/
│   ├── dashboard/
│   │   └── index.blade.php
│   ├── contratos/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── show.blade.php
│   ├── fornecedores/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   ├── aditivos/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── show.blade.php           (timeline de aditivos do contrato + detalhes)
│   ├── alertas/
│   │   ├── index.blade.php              (listagem de alertas com filtros)
│   │   └── dashboard.blade.php          (dashboard de alertas com indicadores)
│   ├── documentos/
│   │   ├── index.blade.php             (Central de Documentos — busca + listagem com completude)
│   │   └── dashboard.blade.php         (dashboard de documentos — 4 indicadores + ranking pendências)
│   ├── relatorios/
│   │   └── index.blade.php
│   ├── secretarias/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   ├── users/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   └── configuracoes/
│       └── index.blade.php
└── auth/
    ├── login.blade.php
    └── forgot-password.blade.php
```

### Assets no Projeto

```
public/assets/
├── css/
│   ├── lib/            (Bootstrap, vendors)
│   ├── remixicon.css   (ícones)
│   └── style.css       (tema WowDash)
├── fonts/              (RemixIcon font files)
├── images/
│   ├── auth/           (ilustrações de login)
│   ├── avatar/         (fotos de perfil)
│   ├── logo.png        (logo light mode)
│   ├── logo-light.png  (logo dark mode)
│   ├── logo-icon.png   (logo sidebar recolhida)
│   └── favicon.png
└── js/
    ├── lib/            (jQuery, Bootstrap, vendors)
    ├── app.js          (JS principal)
    └── *.js            (JS por página)
```

---

## Ícones de Referência

O WowDash usa duas bibliotecas de ícones:
- **RemixIcon** (via CSS): prefixo `ri-` em classes CSS
- **Iconify** (via web component): `<iconify-icon icon="nome-do-icone">`

| Contexto | Ícone Iconify | Alternativa RemixIcon |
|---|---|---|
| Dashboard | `solar:home-smile-angle-outline` | `ri-home-line` |
| Contratos | `solar:document-bold` | `ri-file-text-line` |
| Fornecedores | `solar:buildings-bold` | `ri-building-line` |
| Aditivos | `solar:add-circle-bold` | `ri-add-circle-line` |
| Alertas | `solar:bell-bold` | `ri-alarm-warning-line` |
| Documentos | `solar:folder-bold` | `ri-folder-line` |
| Relatórios | `solar:chart-bold` | `ri-bar-chart-line` |
| Secretarias | `solar:case-round-bold` | `ri-government-line` |
| Usuários | `solar:users-group-two-rounded-bold` | `ri-group-line` |
| Configurações | `solar:settings-bold` | `ri-settings-3-line` |
| Editar | `solar:pen-bold` | `ri-edit-line` |
| Excluir | `solar:trash-bin-trash-bold` | `ri-delete-bin-line` |
| Visualizar | `solar:eye-bold` | `ri-eye-line` |
| Adicionar | `ic:baseline-plus` | `ri-add-line` |
| Buscar | `ic:baseline-search` | `ri-search-line` |
| Download | `solar:download-bold` | `ri-download-line` |
| Upload | `solar:upload-bold` | `ri-upload-line` |
| Valor Financeiro | `solar:wallet-bold` | `ri-money-dollar-circle-line` |
| Score/Nota | `solar:medal-ribbons-star-bold` | `ri-award-line` |
| Tendência | `solar:graph-up-bold` | `ri-line-chart-line` |
| Ranking | `solar:sort-from-top-to-bottom-bold` | `ri-sort-desc` |
| Risco/Escudo | `solar:shield-warning-bold` | `ri-shield-check-line` |
| Essencial/Estrela | `solar:star-bold` | `ri-star-line` |
| Atualizar | `solar:refresh-bold` | `ri-refresh-line` |
| Reequilíbrio / Balança | `solar:balance-bold` | `ri-scales-line` |
| Limite Legal / Alerta % | `solar:danger-circle-bold` | `ri-error-warning-line` |

---

## Estrutura de Menu / Sidebar

```
MENU PRINCIPAL
├── Dashboard                         [ícone: solar:home-smile-angle-outline]
│
├── GESTÃO CONTRATUAL
│   ├── Contratos                     [ícone: solar:document-bold]
│   │   ├── Todos os Contratos
│   │   └── Novo Contrato
│   ├── Aditivos                      [ícone: solar:add-circle-bold]
│   └── Documentos                    [ícone: solar:folder-bold]
│
├── CADASTROS
│   ├── Fornecedores                  [ícone: solar:buildings-bold]
│   └── Secretarias                   [ícone: solar:case-round-bold]
│
├── MONITORAMENTO
│   ├── Alertas                       [ícone: solar:bell-bold]
│   └── Relatórios                    [ícone: solar:chart-bold]
│
├── Painel de Risco                   [ícone: solar:shield-warning-bold]   (novo — Módulo 6)
│   └── /painel-risco                 Rota dedicada (ADR-039)
│
└── ADMINISTRAÇÃO (apenas admin)
    ├── Usuários                      [ícone: solar:users-group-two-rounded-bold]
    └── Configurações                 [ícone: solar:settings-bold]
```

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

## Requisitos Não-Funcionais de UI

> Requisitos que impactam a experiência do usuário e a percepção de qualidade do sistema para venda a prefeituras.

| Requisito | Descrição | Implementação | Fase |
|-----------|-----------|---------------|------|
| Interface simples | Princípio: se precisa de manual para usar, está errado. Telas limpas, ações claras, feedback imediato | Componentes WowDash com labels descritivos, tooltips contextuais, mensagens de validação claras | V1 |
| Responsivo | Funcionar em desktop, tablet e mobile sem perda de funcionalidade | Bootstrap 5 grid system (já suportado pelo template). Testar breakpoints: `xs`, `sm`, `md`, `lg`, `xl` | V1 |
| Tempo de resposta < 2s | Qualquer página deve carregar em menos de 2 segundos | Cache Redis, dados pré-agregados, paginação obrigatória, eager loading | V1 |
| Disponibilidade 24/7 | Sistema acessível a qualquer hora — prefeituras operam em horários variados | Infraestrutura de produção com monitoramento e alertas | V1 |
| Manual online | Ajuda contextual in-app: ícone de `?` em cada seção com explicação da funcionalidade | Tooltips, modais de ajuda, guia de primeiros passos | Fase 2 |
| Treinamento incluso | Documentação de onboarding para novos municípios | Vídeos, tutoriais step-by-step, FAQ | Fase 2 |
| Suporte WhatsApp | Canal de suporte via WhatsApp institucional para prefeituras-clientes | WhatsApp Business API integrada | Fase 2 (ADR-041) |

---

## Instruções de Manutenção

### Quando atualizar este arquivo?
- Quando um **novo componente** for adotado ou criado
- Quando o **design system** mudar (nova versão do template, troca de ícones)
- Quando um **novo padrão de página** for estabelecido
- Quando a **estrutura de menu** for alterada

### Boas práticas:
- Sempre incluir exemplos de HTML para componentes complexos
- Manter a ordem de carregamento de assets atualizada
- Documentar classes customizadas criadas no projeto (não só as do template)
