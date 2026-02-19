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

#### Middleware SetTenantConnection — Mecanismo de Resolução de Tenant (ADR-056)

**Método de resolução: Subdomínio (subdomain-based)**

Decisão: resolução via **subdomínio da URL** (ex: `prefeitura-abc.vigiacontratos.com.br`)
- Lê `$request->getHost()` → extrai subdomínio → consulta `tenants.slug` no banco master
- Resistente a falsificação: subdomínio é controlado pelo DNS, não pelo usuário
- Alternativas descartadas:
  - Header HTTP `X-Tenant-ID`: forjável por cliente malicioso — **não usar**
  - URL path (`/tenant/prefeitura-abc/...`): vaza informação de tenant em logs, menos elegante
  - JWT claim: adequado apenas para API-only, não para sessão web

**Lógica do middleware:**
```php
// SetTenantConnection::handle()
$host = $request->getHost(); // ex: 'prefeitura-abc.vigiacontratos.com.br'
$subdomain = explode('.', $host)[0]; // ex: 'prefeitura-abc'
$tenant = Tenant::where('slug', $subdomain)->where('is_ativo', true)->first();

if (!$tenant) {
    abort(404, 'Prefeitura não encontrada ou inativa');
}

Config::set('database.connections.tenant', [
    'driver' => 'mysql',
    'host' => $tenant->database_host ?? env('DB_HOST'),
    'database' => $tenant->database_name,
    // ... demais configs de env
]);

DB::purge('tenant');
DB::reconnect('tenant');
app()->instance('tenant', $tenant);
```

**Proteções obrigatórias:**
- Validar que `slug` existe E `is_ativo = true` antes de configurar connection
- Nunca aceitar `slug` vindo de request body, query string ou header — sempre extrair do hostname
- Whitelist de subdomínios em produção (configurável em `.env`): `APP_ALLOWED_SUBDOMAINS`
- Tenant não encontrado → HTTP 404 (não 401/403 — não revelar existência de tenants)
- Logar tentativas de acesso a slugs inexistentes para detecção de enumeração
- **Ambiente local/dev**: fallback via variável `TENANT_SLUG` no `.env` quando host é `localhost` ou `127.0.0.1`

**Configuração de rotas:**
- Rotas web: domínio curinga `{tenant}.vigiacontratos.com.br` no `routes/web.php`
- Rotas API: prefixo de subdomínio no grupo de rotas API
- Rota de landing/marketing: sem subdomínio (domínio base `vigiacontratos.com.br`)

- Config dinâmica: conexão MySQL configurada em runtime via `Config::set('database.connections.tenant', ...)`
- Toda request autenticada deve ter tenant resolvido antes de qualquer query

#### Estratégia de Isolamento de Storage S3 (ADR-058)

**V1 — Bucket único com prefixo por tenant:**
- Estrutura: `s3://vigiacontratos-prod/{tenant_slug}/documentos/contratos/{contrato_id}/{tipo}/arquivo`
- Vantagem: simplicidade de provisionamento — zero overhead ao criar novo tenant
- Controle de acesso: via IAM Policy restrita ao prefixo `/{tenant_slug}/` por usuário IAM dedicado por tenant
- Geração de URLs assinadas: sempre com prefixo do tenant validado no controller — nunca aceitar path do cliente diretamente

**V2 — Bucket dedicado por tenant (Fase de escala):**
- Provisionar bucket separado por tenant no `tenant:create` command
- Trigger: migrar para V2 quando tenant atingir >50GB ou por exigência contratual

**Regras obrigatórias independentes da estratégia:**
- Nunca gerar URL de storage a partir de input do usuário sem sanitização e validação de pertencimento ao tenant atual
- Server-side encryption obrigatória: `AES-256` (SSE-S3 ou SSE-KMS)
- Bucket nunca público — todo acesso via pre-signed URLs com TTL máximo de 1 hora
- Pre-signed URLs para download geradas pelo `DocumentoService` após verificação de `DocumentoPolicy`
- Log de geração de pre-signed URL registrado em `log_acesso_documentos`

- Cache isolado: prefixo de cache Redis por tenant (`tenant_{id}:chave`)
- Queues: jobs devem carregar o contexto do tenant (tenant_id no payload)
  - Todo job assíncrono DEVE validar tenant_id no payload antes de executar — abort se tenant_id ausente ou inválido
  - Se job falhar ao configurar tenant connection, não executar query (proteção contra vazamento para banco master)

### Regras Obrigatórias
- Nunca acessar dados de tenant sem middleware SetTenantConnection ativo
- Nunca fazer query cross-tenant (sem UNION entre bancos, sem JOIN entre connections)
- Migrations devem ser versionadas e aplicáveis a todos os tenants
- Comando artisan para provisionar novo tenant (criar banco, aplicar migrations, seeder inicial)
- Comando artisan para aplicar migrations em todos os tenants
