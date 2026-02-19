# Regras — Autorização e RBAC

> Extraído de `banco-de-regras.md`. Carregar ao trabalhar com perfis, permissões, middleware ou policies.
> Define os 8 perfis padrão, implementação do RBAC e regras de autenticação/sessão.

---

## Autorização e Perfis de Usuário (RBAC)

O sistema opera com RBAC (Role-Based Access Control) via tabela `roles` dinâmica (ADR-050).

### Perfis Padrão (8 — via RoleSeeder)

| Perfil (nome) | Acesso | Descrição |
|---|---|---|
| `administrador_geral` | Acesso total + configurações + gestão de usuários + auditoria + alertas globais | TI / Controladoria Central — gerencia todo o sistema |
| `controladoria` | Visualização total + painel de risco + relatórios TCE + pareceres | Controladoria Interna — perfil estratégico, sem edição financeira |
| `secretario` | Contratos da própria secretaria + aprovação de aditivos (workflow) | Secretário Municipal — acesso restrito à pasta |
| `gestor_contrato` | CRUD de contratos, fornecedores, aditivos, documentos, fiscais, execuções | Gestor de Contrato — operação diária |
| `fiscal_contrato` | Relatórios de fiscalização, ocorrências, inconformidades, fotos | Fiscal de Contrato — acompanhamento técnico |
| `financeiro` | Empenhos, saldo contratual, pagamentos, relatórios financeiros | Financeiro / Contabilidade — parte financeira |
| `procuradoria` | Análise de aditivos, pareceres jurídicos, validação de prorrogações | Procuradoria Jurídica — validação jurídica |
| `gabinete` | Dashboard executivo, contratos críticos, mapa de risco, relatórios | Gabinete / Prefeito — visão estratégica (somente leitura) |

### Implementação

- Tabela `roles` com 8 perfis padrão (`is_padrao = true`) via RoleSeeder — admin pode criar perfis customizados (ADR-050)
- Tabela `permissions` com permissões granulares: formato `{recurso}.{ação}` (ex: `contrato.editar`, `aditivo.aprovar`) — ADR-051
- Tabela `role_permissions` — associação N:N entre roles e permissions
- Tabela `user_permissions` — permissões individuais extras com `expires_at` para temporárias (ADR-053)
- Tabela `user_secretarias` — escopo de acesso por secretaria (ADR-054)
- Middleware `EnsureUserHasPermission` (substitui `EnsureUserIsAdmin` e `EnsureUserIsGestor`)
- Policies para controle granular por entidade — verificam role + permission + secretaria
- Helper: `$user->hasPermission('contrato.editar')` no Model User
- Eloquent Global Scope por secretaria para queries filtradas automaticamente (RN-326)
- Workflow de aprovação: tabela `workflow_aprovacoes` com 5 etapas sequenciais (ADR-052)

### Segurança de Autenticação e Sessão (RBAC)

| Regra | Detalhamento |
|---|---|
| Autenticação com MFA (opcional) | TOTP para perfis administrador_geral e gestor_contrato (ADR-045) |
| Sessão com expiração | TTL de 120 minutos de inatividade (ADR-049) |
| Criptografia de senha | Argon2id obrigatório (ADR-044) — nunca bcrypt/MD5 |
| Controle por JWT (se API) | Endpoints `/api/v1/*` autenticados via Laravel Sanctum (tokens API). Sessão web via session driver |
| Registro de tentativas de login | Tabela `login_logs` (user_id, ip, user_agent, success). Lockout após 5 tentativas falhas (ADR-048) |
| HTTPS/TLS obrigatório | TLS 1.2+ em produção — nunca trafegar credenciais em texto plano |
