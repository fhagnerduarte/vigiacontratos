# Regras — Multi-Tenant (Database-per-Tenant)

> Extraído de `banco-de-regras.md`. Carregar ao trabalhar com multi-tenant, middleware, provisioning ou isolamento de dados.

---

## Multi-Tenant (Database-per-Tenant)

### Estratégia
- **Banco de dados isolado por prefeitura** (database-per-tenant) — ADR-042
- Banco central/master: gestão de tenants, autenticação inicial, configurações globais
- Bancos tenant: `vigiacontratos_{slug}` (ex: `vigiacontratos_pref_sao_paulo`)
- **Isolamento total**: nenhuma query cross-tenant possível
- Migrations aplicadas em todos os bancos tenant simultaneamente

### Infraestrutura Central (banco master)

| Tabela | Campos Principais | Propósito |
|--------|-------------------|-----------|
| `tenants` | id, nome, slug, database_name, database_host, is_ativo, plano, created_at, updated_at | Cadastro de prefeituras-clientes |
| `tenant_users` | id, user_id, tenant_id, role, created_at | Vínculo user ↔ tenant (admin SaaS pode ter múltiplos) |

### Implementação

- Middleware `SetTenantConnection`: identifica tenant e configura connection do banco
- Config dinâmica: conexão MySQL configurada em runtime via `Config::set('database.connections.tenant', ...)`
- Toda request autenticada deve ter tenant resolvido antes de qualquer query
- Storage isolado: bucket/pasta por tenant em S3 (`{tenant_slug}/documentos/...`)
- Cache isolado: prefixo de cache Redis por tenant (`tenant_{id}:chave`)
- Queues: jobs devem carregar o contexto do tenant (tenant_id no payload)

### Regras Obrigatórias
- Nunca acessar dados de tenant sem middleware SetTenantConnection ativo
- Nunca fazer query cross-tenant (sem UNION entre bancos, sem JOIN entre connections)
- Migrations devem ser versionadas e aplicáveis a todos os tenants
- Comando artisan para provisionar novo tenant (criar banco, aplicar migrations, seeder inicial)
- Comando artisan para aplicar migrations em todos os tenants
