# Tema Core — Design System e Referência Visual

> Extraído de `banco-de-tema.md`. Carregado em TODAS as tarefas que envolvem interface/UI.
> Contém: identificação do template, stack frontend, layouts, classes CSS, componentes reutilizáveis, sistema de tema, inventário de páginas.

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
