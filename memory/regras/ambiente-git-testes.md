# Regras — Container, Ambiente, Git e Testes

> Extraído de `banco-de-regras.md`. Carregar ao configurar ambiente, fazer commits ou escrever testes.

---

## Container / Ambiente

- Container: **Docker / Laravel Sail**
- Linguagem: **PHP 8.2+**
- Banco: **MySQL 8** (banco master + bancos tenant isolados)
- Cache: **Redis** (com prefixo por tenant)
- Queue: **Redis** (driver de filas para jobs assíncronos, com tenant_id no payload)
- Storage: **S3-compatible** (MinIO em dev / AWS S3 em produção) — ADR-043

#### MinIO — Configuração de Desenvolvimento

Adicionar ao `.env.example` (obrigatório — nunca omitir):
```env
# Storage S3-compatible (MinIO em dev / AWS S3 em prod)
AWS_ACCESS_KEY_ID=vigiacontratos_dev
AWS_SECRET_ACCESS_KEY=dev_secret_local_apenas
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=vigiacontratos-dev
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true

# MinIO Console (acesso via browser: http://localhost:8900)
MINIO_ROOT_USER=vigiacontratos
MINIO_ROOT_PASSWORD=dev_secret_local
```

Adicionar ao `docker-compose.yml` do Sail (via `sail:publish` e customização):
```yaml
minio:
  image: 'minio/minio:latest'
  ports:
    - '${FORWARD_MINIO_PORT:-9000}:9000'
    - '${FORWARD_MINIO_CONSOLE_PORT:-8900}:8900'
  environment:
    MINIO_ROOT_USER: '${MINIO_ROOT_USER}'
    MINIO_ROOT_PASSWORD: '${MINIO_ROOT_PASSWORD}'
  command: 'server /data/minio --console-address ":8900"'
  volumes:
    - 'sail-minio:/data/minio'
```

**Importante:** As credenciais acima são exclusivas para ambiente de desenvolvimento local. Nunca usar estas credenciais em staging ou produção.
- Scheduler: `sail artisan schedule:run` via cron (`* * * * *`)
- Worker: `sail artisan queue:work --tries=3 --backoff=60`
- Nunca commitar `.env`
- Manter `.env.example` atualizado com novas variáveis (MAIL_*, QUEUE_CONNECTION=redis, AWS_*, SESSION_LIFETIME)

### Checklist de Hardening para Produção

Antes de cada deploy em ambiente de produção, verificar:

**Configurações de Aplicação:**
- [ ] `APP_DEBUG=false` — nunca `true` em produção
- [ ] `APP_ENV=production`
- [ ] `APP_KEY` gerado via `artisan key:generate` e rotacionado periodicamente
- [ ] `LOG_LEVEL=error` (não `debug` em produção)
- [ ] `SESSION_SECURE_COOKIE=true` (apenas HTTPS)
- [ ] `SESSION_SAME_SITE=strict`

**PHP:**
- [ ] `expose_php = Off` no `php.ini`
- [ ] `display_errors = Off`
- [ ] `display_startup_errors = Off`
- [ ] `log_errors = On`

**Ferramentas de Debug (NUNCA em produção):**
- [ ] Laravel Telescope: desabilitado (`TELESCOPE_ENABLED=false`) ou restrito a IP interno
- [ ] Laravel Debugbar: removido do `require-dev` e não instalado em produção
- [ ] `sail` e ferramentas de dev: não presentes no `vendor` de produção

**Headers de Segurança (configurar no servidor web Nginx/Apache):**
- [ ] `X-Frame-Options: DENY`
- [ ] `X-Content-Type-Options: nosniff`
- [ ] `X-XSS-Protection: 1; mode=block`
- [ ] `Strict-Transport-Security: max-age=31536000; includeSubDomains` (HSTS)
- [ ] `Content-Security-Policy`: definir política restritiva por aplicação
- [ ] `Referrer-Policy: strict-origin-when-cross-origin`
- [ ] Remover header `Server: nginx` ou `Server: Apache` (não revelar versão)

**Banco de Dados:**
- [ ] Usuário MySQL da aplicação com privilégios mínimos (sem SUPER, sem FILE)
- [ ] Usuário de apenas SELECT+INSERT nas tabelas imutáveis (ver `auditoria-performance.md`)
- [ ] `DB_PASSWORD` com senha forte (mínimo 32 chars, gerada aleatoriamente)
- [ ] Acesso ao MySQL: apenas de dentro da rede privada (não exposto na porta 3306 externamente)

**S3 / Storage:**
- [ ] Credenciais AWS: IAM user com políticas mínimas (GetObject, PutObject no bucket específico)
- [ ] Bucket S3: ACL privado, sem public access
- [ ] SSE-S3 ou SSE-KMS habilitado

---

## Git

### Commits
- Idioma: **Português**
- Formato: `tipo: descrição`
- Tipos: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `style`

### Branches

#### Nomenclatura
- Features: `feature/nome-da-feature`
- Correções: `fix/descricao-do-bug`
- Refactoring: `refactor/descricao`

#### Fluxo de Integração (obrigatório)

Todo trabalho de implementação deve seguir este fluxo sequencial:

```
feature/* (ou fix/* ou refactor/*)
       ↓  Pull Request
    homolog  ← ambiente de homologação/validação
       ↓  após validação aprovada
      main   ← produção
```

**Regras do fluxo:**
- Nunca fazer merge direto de feature/* para `main`
- Branch `homolog` representa o ambiente de homologação/staging
- Toda implementação passa por `homolog` antes de ir para `main`
- Pull Request obrigatório para merge em `homolog`
- Merge para `main` apenas após validação completa em `homolog`
- Branch `main` deve sempre refletir o estado de produção estável

---

## Testes

- Framework de testes: **PHPUnit** (via `sail test`)
- Cobertura mínima: Services e rotas críticas
- Testes unitários para: Services (ContratoService, AlertaService, NotificacaoService, RiscoService, AuditoriaService), cálculos, validações
- Testes de integração para: endpoints da API, fluxos CRUD completos, fluxo de cadastro multi-etapa
- Testes de feature para: motor de monitoramento (VerificarVencimentosCommand), envio de notificações, resolução de alertas
- Factories para dados de teste (ContratoFactory, FornecedorFactory, UserFactory, AlertaFactory, DashboardAgregadoFactory, DocumentoFactory)
- `RefreshDatabase` trait em testes de integração

### Casos de Teste Planejados

**Contratos e Score:**
- Testar cálculo de score de risco com diferentes cenários
- Testar validação de CNPJ (dígito verificador)
- Testar imutabilidade do audit trail

**Alertas:**
- Testar geração de alertas por faixa de dias (120, 90, 60, 30, 15, 7)
- Testar prioridade automática (informativo/atenção/urgente)
- Testar não-duplicação de alertas (unique constraint)
- Testar resolução automática de alertas (via aditivo de prazo)
- Testar retry com backoff exponencial (EnviarNotificacaoAlertaJob)
- Testar bloqueio de aditivo retroativo sem justificativa

**Dashboard:**
- Testar cálculos de agregação do DashboardService (indicadores financeiros)
- Testar score de gestão contratual (penalidades por critério)
- Testar AgregarDashboardCommand (processamento noturno)
- Testar cache Redis do dashboard (hit, miss, invalidação)
- Testar performance do dashboard (<2 segundos com dados agregados)

**Aditivos:**
- Testar geração automática de numero_sequencial por contrato
- Testar cálculo de percentual_acumulado após múltiplos aditivos
- Testar bloqueio por limite legal (is_bloqueante = true)
- Testar alerta por limite legal (is_bloqueante = false) com justificativa obrigatória
- Testar atualização automática do contrato pai após aditivo (valor_global, data_fim, score)
- Testar critérios de score de risco relacionados a aditivos (RN-106, RN-107, RN-108)
- Testar fluxo de reequilíbrio econômico-financeiro (campos obrigatórios condicionais)
- Testar imutabilidade de aditivo salvo (RN-116) e auditoria (RN-117)

**Documentos:**
- Testar upload de documento com versionamento automático (RN-120, RN-121)
- Testar cálculo de completude documental por checklist (RN-128, RN-129)
- Testar nomes padronizados de arquivo (contrato_{numero}_{tipo}_v{versao}.pdf)
- Testar imutabilidade do log_acesso_documentos (append-only — RN-122)
- Testar autorização por permissão RBAC via DocumentoPolicy (documento.visualizar, documento.criar, documento.download, documento.excluir — RN-130)
- Testar geração de relatório TCE com lista de documentos (RN-133)
- Testar soft delete de documento (exclusão lógica mantém no storage — RN-134)

**Painel de Risco:**
- Testar cálculo expandido do score de risco com 5 categorias (RN-136 a RN-142)
- Testar indicadores do painel de risco (5 cards — RN-144)
- Testar ranking de risco ordenado por score DESC (RN-146)
- Testar categorias múltiplas de risco simultâneas por contrato (RN-147)
- Testar mapa de risco por secretaria com ordenação por críticos DESC (RN-148, RN-149)
- Testar geração de relatório de risco para TCE em PDF (RN-150 a RN-152)
- Testar alertas preventivos com mensagens contextualizadas (RN-153)
- Testar performance do painel de risco (<2 segundos com cache Redis)

**RBAC:**
- Testar verificação de permissão por role (PermissaoServiceTest)
- Testar verificação de permissão individual por usuário (PermissaoServiceTest)
- Testar expiração automática de permissão temporária (PermissaoTemporariaTest)
- Testar workflow de aprovação completo — 5 etapas (WorkflowAprovacaoTest)
- Testar bloqueio de avanço sem aprovação da etapa anterior (WorkflowServiceTest)
- Testar reprovação com notificação ao solicitante (WorkflowServiceTest)
- Testar acesso por perfil a recursos protegidos (PerfilUsuarioTest)
- Testar escopo por secretaria (queries filtradas automaticamente)
- Testar perfis padrão não deletáveis (is_padrao = true)
- Testar CRUD de perfis customizados pelo admin
