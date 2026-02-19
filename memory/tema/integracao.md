# Tema — Integração e Assets

> Extraído de `banco-de-tema.md`. Carregar quando trabalhando com layout, menu, sidebar, assets ou ícones.
> Contém: Ordem de carregamento de CSS/JS, estrutura de views, ícones de referência, estrutura de menu/sidebar.

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
