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
- Helper: `$user->hasPermission('contrato.editar')` no Model User — com verificação em tempo real de `expires_at`

#### Helper `$user->hasPermission()` — Verificação em Tempo Real

```php
// Especificação do comportamento obrigatório no Model User
public function hasPermission(string $permission): bool
{
    // 1. Verificar via role (permissões do perfil — sem expiração)
    if ($this->role->permissions()->where('nome', $permission)->exists()) {
        return true;
    }

    // 2. Verificar permissão individual — com verificação de expiração em tempo real
    return $this->permissions()
        ->where('nome', $permission)
        ->where(function ($query) {
            $query->whereNull('user_permissions.expires_at')           // permanente
                  ->orWhere('user_permissions.expires_at', '>', now()); // ainda válida
        })
        ->exists();
}
```

**Regra:** A verificação de `expires_at` DEVE acontecer em tempo real no `hasPermission()` — não confiar apenas no job diário `permissoes:verificar-expiradas`.

**Justificativa:** O job diário remove permissões do banco (limpeza), mas se uma permissão expirar às 14h e o job só rodar à meia-noite, o usuário teria acesso indevido por até 10 horas. A verificação em tempo real no método é a linha primária de defesa.

**Job diário `permissoes:verificar-expiradas` (RN-333):** mantido como limpeza/housekeeping do banco — remove registros expirados e gera log de auditoria. Não é a linha de defesa primária.

**Cache:** Se performance for impactada em sistemas de alto volume, cachear `hasPermission()` com TTL de 5 minutos (Redis, chave `perm:{user_id}:{permission}`) — mas sempre invalidar cache quando `user_permissions` for modificada.
- Eloquent Global Scope por secretaria para queries filtradas automaticamente (RN-326)
- Workflow de aprovação: tabela `workflow_aprovacoes` com 5 etapas sequenciais (ADR-052)

#### Limites do Administrador Geral sobre Tabelas Imutáveis

O perfil `administrador_geral` tem acesso total ao sistema — mas este acesso não se estende à modificação direta de tabelas imutáveis:

| Tabela Imutável | O Admin PODE | O Admin NÃO PODE |
|---|---|---|
| `historico_alteracoes` | Visualizar, exportar, filtrar | Editar, deletar qualquer registro |
| `log_acesso_documentos` | Visualizar, exportar | Editar, deletar |
| `login_logs` | Visualizar, exportar, correlacionar | Editar, deletar |
| `log_notificacoes` | Visualizar | Editar, deletar |
| `workflow_aprovacoes` | Visualizar histórico de aprovações | Alterar etapas já aprovadas/reprovadas |

**Implementação:** Policies para estas tabelas devem retornar `false` para as ações `update` e `delete` independentemente do perfil do usuário — inclusive `administrador_geral`.

**Proteção adicional:** Ver `memory/regras/auditoria-performance.md` para especificação de triggers MySQL que reforçam esta regra no nível de banco de dados.

### Segurança de Autenticação e Sessão (RBAC)

| Regra | Detalhamento |
|---|---|
| Autenticação com MFA (opcional) | TOTP para perfis administrador_geral e gestor_contrato (ADR-045) |
| Sessão com expiração | TTL de 120 minutos de inatividade (ADR-049) |
| Criptografia de senha | Argon2id obrigatório (ADR-044) — nunca bcrypt/MD5 |
| Controle por JWT (se API) | Endpoints `/api/v1/*` autenticados via Laravel Sanctum (tokens API). Sessão web via session driver |
| Registro de tentativas de login | Tabela `login_logs` (user_id, ip, user_agent, success). Lockout após 5 tentativas falhas (ADR-048) |
| HTTPS/TLS obrigatório | TLS 1.2+ em produção — nunca trafegar credenciais em texto plano |
