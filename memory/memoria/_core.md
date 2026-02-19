# Memória Core — Estado Atual e Implementações

> Extraído de `banco-de-memoria.md`. Carregado em **TODAS** as tarefas.
> Consultado pelo **Gestor de Memória** (Agente 02) antes de cada ação.
> Atualizado ao final de cada implementação significativa (Etapa 7 do pipeline).

---

## Estado Atual

| Campo | Valor |
|---|---|
| Projeto | vigiacontratos |
| Tipo | Sistema de gestão contratual municipal |
| Fase Atual | Fase 0 — Setup Inicial |
| Última Atualização | 2026-02-18 |
| Próximo Passo | Fase 1 — Configurar projeto Laravel + banco + template WowDash |

### Cadeia de Fases

```
[Fase 0: Setup] → [Fase 1: Infraestrutura] → [Fase 2: Cadastros Base + RBAC] → [Fase 3: Contratos] → [Fase 4: Alertas] → [Fase 5: Dashboard + Painel de Risco + Relatórios] → [Fase 6: Refinamento]
     ▲ atual
```

**Detalhamento das Fases:**
- **Fase 0 — Setup Inicial:** Preencher bases de conhecimento, definir stack e convenções
- **Fase 1 — Infraestrutura:** Criar projeto Laravel, configurar Docker/Sail, MySQL, Redis, integrar template WowDash, autenticação, migrations base
- **Fase 2 — Cadastros Base + RBAC:** CRUD de Secretarias, Fornecedores + Módulo 7: Perfis de Usuário (RBAC com 8 roles, permissions, user_secretarias, workflow de aprovação)
- **Fase 3 — Contratos (Cadastro Inteligente):** Wizard multi-etapa, CRUD completo de Contratos + Aditivos + Fiscais + Central de Documentos (Módulo 5: pasta digital, versionamento, completude, log de acesso, busca, dashboard, relatório TCE) + Execução Financeira + Score de Risco + Audit Trail
- **Fase 4 — Alertas (Motor de Monitoramento):** Command agendado (cron diário) + Queue (Redis) + Notifications (mail + database) + Dashboard de alertas + Configuração de prazos + Log de notificação + Bloqueio preventivo + Resolução automática
- **Fase 5 — Dashboard Executivo, Painel de Risco e Relatórios:** Painel Executivo com 5 blocos estratégicos (financeiro, risco, vencimentos, secretarias, essenciais), score de gestão 0-100, tendências mensais, ranking de fornecedores, visão do controlador, agregação noturna, cache Redis + Painel de Risco Administrativo dedicado (score expandido com 5 categorias, ranking de risco, mapa por secretaria, relatório TCE de risco, alertas preventivos inteligentes) + relatórios gerenciais e exportação
- **Fase 6 — Refinamento:** Testes, ajustes de UX, performance, segurança final

---

## Registro de Implementações

| ID | Data | Descrição | Arquivos Afetados | Status |
|---|---|---|---|---|
| IMP-001 | 2026-02-18 | Preenchimento das bases de conhecimento do projeto | CLAUDE.md, memory/*.md | Concluído |
| IMP-002 | 2026-02-18 | Detalhamento do Módulo 1 — Cadastro Inteligente de Contratos | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-003 | 2026-02-18 | Detalhamento do Módulo 2 — Alertas Automáticos de Vencimento | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-004 | 2026-02-18 | Detalhamento do Módulo 3 — Painel Executivo (Dashboard) | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-005 | 2026-02-18 | Detalhamento do Módulo 4 — Gestão de Aditivos (expansão completa: 7 tipos, limites legais, reequilíbrio, score de risco, timeline, dashboard) | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-006 | 2026-02-18 | Detalhamento do Módulo 5 — Central de Documentos (pasta digital por contrato, 12 tipos de documento, versionamento não-destrutivo, log de acesso, completude documental, validações automáticas, busca inteligente, dashboard de indicadores, relatório TCE, OCR como Fase 2) | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-007 | 2026-02-18 | Detalhamento do Módulo 6 — Painel de Risco Administrativo (grande diferencial estratégico: score expandido com 5 categorias de risco, dashboard dedicado com ranking e semáforo, mapa de risco por secretaria, relatório automatizado para TCE, alertas preventivos inteligentes, WhatsApp como Fase 2) | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-008 | 2026-02-18 | Detalhamento dos Requisitos Técnicos Estratégicos — SaaS multi-tenant com banco isolado por prefeitura, segurança expandida (Argon2id, MFA, lockout, logs de login), LGPD (base legal, retenção, anonimização), auditoria (hash SHA-256, logs imutáveis, relatórios exportáveis), performance (capacidade por tenant, paginação, disponibilidade 24/7), armazenamento S3-compatible, requisitos não-funcionais de UI | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-009 | 2026-02-18 | Detalhamento do Módulo 7 — Perfis de Usuário (RBAC): 8 perfis dinâmicos (tabela roles), permissões granulares por recurso.ação, escopo por secretaria, permissões temporárias com expires_at, workflow de aprovação 5 etapas para aditivos, logs de auditoria expandidos com perfil do usuário | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md | Concluído |
| IMP-010 | 2026-02-18 | Expansão do Módulo 7 — Perfis de Usuário: objetivos estratégicos do módulo (segregação, rastreabilidade, antifraude, controle formal), ocupantes típicos por perfil, matriz de permissões granulares completa (12 recursos × 8 perfis com indicação de escopo por secretaria), exemplo concreto de log de auditoria, regras de segurança de autenticação/sessão (MFA, JWT/Sanctum, lockout, TLS) | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md | Concluído |

### Como registrar:
1. Use ID sequencial (IMP-XXX)
2. Descreva o que foi feito (não como)
3. Liste os arquivos principais afetados
4. Status: `Concluído` | `Parcial` | `Revertido`

---

## Problemas Conhecidos

| ID | Descrição | Severidade | Módulo | Status |
|---|---|---|---|---|
| — | Nenhum problema registrado | — | — | — |
