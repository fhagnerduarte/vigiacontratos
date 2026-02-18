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
                <!-- Lista de documentos com tipo, versão, download -->
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
| Dashboard | `dashboard/index.blade.php` | Substituir cards e gráficos por dados de contratos |
| Contratos — Listagem | `users/users-list.blade.php` + `table/tabledata.blade.php` | Adaptar colunas para dados de contrato, adicionar badges de status |
| Contratos — Cadastro | `users/add-user.blade.php` + `forms/form-layout.blade.php` | **Wizard multi-etapa** (6 passos): Identificação, Fornecedor, Financeiro, Vigência, Fiscal, Documentos |
| Contratos — Detalhes | `users/view-profile.blade.php` + `invoice/invoice-preview.blade.php` | **Detalhes com abas** (6 abas): Dados, Fiscal, Financeiro, Documentos, Aditivos, Auditoria. Inclui score de risco e percentual executado |
| Fornecedores | `users/users-list.blade.php` + `users/add-user.blade.php` | Adaptar para dados de fornecedor (CNPJ, contato) |
| Aditivos | `invoice/invoice-list.blade.php` | Adaptar para lista de aditivos por contrato |
| Alertas | `settings/notification.blade.php` | Adaptar para lista de alertas com prioridade |
| Documentos | `componentspage/imageUpload.blade.php` | Adaptar upload para PDF |
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
│   │   └── edit.blade.php
│   ├── alertas/
│   │   └── index.blade.php
│   ├── documentos/
│   │   └── index.blade.php
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
└── ADMINISTRAÇÃO (apenas admin)
    ├── Usuários                      [ícone: solar:users-group-two-rounded-bold]
    └── Configurações                 [ícone: solar:settings-bold]
```

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
