# Padrões de Tema — Portal Público de Transparência

> Design system do portal público (`/{slug}/portal/*`).
> CSS dedicado: `public/assets/css/portal.css` (~400 linhas).
> Layout: `resources/views/portal/layout.blade.php`.
> Inspiração: coreau.ce.gov.br/contratos.php.

---

## CSS Variables (Customizáveis por Tenant)

```css
--portal-primary: #1b55e2;   /* sobrescrito via tenant->cor_primaria */
--portal-secondary: #0b3a9e; /* sobrescrito via tenant->cor_secundaria */
--portal-dark: #1a1a2e;
--portal-light: #f8f9fa;
--portal-font-size: 16px;    /* ajustável via acessibilidade (14/16/18/20px) */
```

Injetados no layout via inline style no `<html>`:
```blade
<html style="--portal-primary: {{ $tenant->cor_primaria ?? '#1b55e2' }}; --portal-secondary: {{ $tenant->cor_secundaria ?? '#0b3a9e' }};">
```

---

## Estrutura do Layout

1. **Barra de Acessibilidade** (`.portal-accessibility`) — fundo dark, botões A-/A/A+, contraste
2. **Header Institucional** (`.portal-header`) — gradiente primary→secondary, logo + nome tenant + subtítulo LAI
3. **Navegação** (`.portal-nav`) — sticky top, fundo branco, ícones Iconify, offcanvas mobile
4. **Breadcrumb** (`.portal-breadcrumb`) — fundo light, font 0.85rem
5. **Conteúdo** (`<main class="container py-4">`) — @yield('content')
6. **Footer** (`.portal-footer`) — 3 colunas (institucional, contato, links rápidos)
7. **LGPD Banner** (`.portal-lgpd`) — fixed bottom, localStorage para persistência

---

## Classes CSS do Portal

### Cards
- `.portal-card` — border-top 3px primary, box-shadow, border-radius 8px
- `.portal-stat-card` — texto centralizado, ícone + valor grande + label, hover translateY(-2px)
- `.portal-link-card` — card de link rápido com seta → no hover
- `.portal-info-card` — card informativo com ícone info, borda-esquerda primary

### Tabelas
- `.portal-table` — thead dark (#1a1a2e), th font-weight 600, zebra rows, hover azul claro

### Filtros
- `.portal-filter-panel` — fundo light, borda #e9ecef, border-radius 8px, padding 1.25rem
- `.portal-search-wrapper` — input com ícone de lupa posicionado à esquerda

### Status Badges (contratos)
- `.portal-badge-vigente` — bg verde
- `.portal-badge-vencido` — bg vermelho
- `.portal-badge-cancelado` — bg cinza
- `.portal-badge-suspenso` — bg amarelo
- `.portal-badge-encerrado` — bg azul
- `.portal-badge-rescindido` — bg roxo

### Timeline (LAI show)
- `.portal-timeline` — padding-left 2rem, linha vertical 2px cinza
- `.portal-timeline-item` — círculo 12px primary antes de cada item, margin-bottom 1.5rem

### Seções
- `.portal-section-title` — border-left 4px primary, padding-left 0.75rem
- `.portal-hero` — fundo gradiente light, border-radius 12px, padding 2rem
- `.portal-empty-state` — texto centralizado, ícone 48px, cor cinza
- `.portal-result-counter` — badge de contagem de resultados

### Footer
- `.portal-footer` — fundo dark, h5 com border-bottom primary
- `.portal-footer-bottom` — border-top separador, texto menor, copyright

### Alto Contraste
- `body.high-contrast` — fundo preto, texto branco, cards/tabelas escuros, borders brancos

---

## Bibliotecas Usadas

| Lib | Uso | Carregamento |
|-----|-----|--------------|
| Bootstrap 5.3 | Grid, forms, offcanvas, badges | CDN no layout |
| Iconify | Ícones (solar set) | CDN `iconify-icon` web component |
| ApexCharts | Gráficos na home (bar chart secretarias) | @push('scripts') apenas em index |
| jQuery 3.7.1 | Compatibilidade Bootstrap | asset local |

---

## Views do Portal

| View | Rota | Conteúdo |
|------|------|----------|
| `portal/layout.blade.php` | — | Layout master |
| `portal/index.blade.php` | `portal.index` | Home: hero + stats + gráfico + ranking + links |
| `portal/contratos/index.blade.php` | `portal.contratos` | 5 filtros + tabela + badges + paginação |
| `portal/contratos/show.blade.php` | `portal.contratos.show` | Dados gerais + contratado + valores + vigência + aditivos |
| `portal/fornecedores/index.blade.php` | `portal.fornecedores` | Busca + tabela + empty state + paginação |
| `portal/dados-abertos.blade.php` | `portal.dados-abertos` | Cards JSON/CSV + explicativo dados abertos |
| `portal/lai/create.blade.php` | `portal.lai.create` | Formulário nova solicitação LAI |
| `portal/lai/consultar.blade.php` | `portal.lai.consultar` | Consulta por protocolo + email |
| `portal/lai/show.blade.php` | `portal.lai.show` | 2 colunas: dados+resposta+prorrogação | sidebar status+timeline+ações |

---

## Branding por Tenant

Campos na tabela `tenants` (migration master 000007):
- `logo_path` — path S3 do logo/brasão
- `cor_primaria` — hex 7 chars (default #1b55e2)
- `cor_secundaria` — hex 7 chars (default #0b3a9e)
- `endereco`, `telefone`, `email_contato`, `horario_atendimento` — dados footer
- `cnpj`, `gestor_nome` — dados institucionais

Accessor `$tenant->logo_url` retorna URL S3 (ou null).

Admin SaaS: editar via `/admin-saas/tenants/{id}` → seção "Portal de Transparência (Branding)".
