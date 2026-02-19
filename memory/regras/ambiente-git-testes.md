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
- Scheduler: `sail artisan schedule:run` via cron (`* * * * *`)
- Worker: `sail artisan queue:work --tries=3 --backoff=60`
- Nunca commitar `.env`
- Manter `.env.example` atualizado com novas variáveis (MAIL_*, QUEUE_CONNECTION=redis, AWS_*, SESSION_LIFETIME)

---

## Git

### Commits
- Idioma: **Português**
- Formato: `tipo: descrição`
- Tipos: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `style`

### Branches
- Features: `feature/nome-da-feature`
- Correções: `fix/descricao-do-bug`
- Refactoring: `refactor/descricao`

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
- Testar autorização por perfil via DocumentoPolicy (admin, gestor, consulta — RN-130)
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
