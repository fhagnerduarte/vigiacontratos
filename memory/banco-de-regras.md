# Banco de Regras — Governança Técnica

> Consultado pelo **Guardião de Regras** (Agente 01) e pelo **Engenheiro Executor** (Agente 05).
> Define COMO o código deve ser escrito. Qualquer violação bloqueia a execução.

---

## Convenções de Código

### Padrão Geral
- Padrão de código: **PSR-12** estrito
- Linguagem principal: **PHP 8.2+** com typed properties e enums nativos
- Framework: **Laravel 12**

### Nomenclatura

| Elemento | Convenção | Exemplo |
|---|---|---|
| Models / Entidades | Singular PascalCase | `Contrato`, `Fornecedor`, `Fiscal` |
| Tabelas / Coleções | Plural snake_case | `contratos`, `fornecedores`, `fiscais` |
| Colunas / Campos | snake_case | `data_inicio`, `valor_total`, `is_ativo` |
| Controllers / Handlers | Plural PascalCase + Controller | `ContratosController`, `FiscaisController` |
| Services | Singular PascalCase + Service | `ContratoService`, `RiscoService` |
| Validadores | Store/Update + Model + Request | `StoreContratoRequest`, `StoreFiscalRequest` |
| Resources | Singular PascalCase + Resource | `ContratoResource`, `FiscalResource` |
| Enums | PascalCase (sem sufixo) | `StatusContrato`, `ModalidadeContratacao` |
| Policies | Singular PascalCase + Policy | `ContratoPolicy`, `FornecedorPolicy` |
| Rotas API | kebab-case, prefixo `/api/v1/` | `/api/v1/contratos`, `/api/v1/fornecedores` |

### Classes Esperadas por Tipo

**Controllers:**
- Admin/DashboardController
- Admin/ContratosController
- Admin/FornecedoresController
- Admin/AditivosController
- Admin/FiscaisController
- Admin/AlertasController
- Admin/DocumentosController
- Admin/ExecucoesFinanceirasController
- Admin/RelatoriosController
- Admin/SecretariasController
- Admin/UsersController
- Admin/ConfiguracoesController
- Admin/PainelRiscoController (painel de risco + exportação relatório TCE de risco)
- Admin/RolesController (CRUD de perfis)
- Admin/PermissoesController (gestão de permissões)

**Services:**
- ContratoService
- FornecedorService
- AditivoService (criação, limites legais, percentual acumulado, reequilíbrio, atualização do contrato pai)
- FiscalService
- AlertaService (geração e resolução de alertas)
- NotificacaoService (orquestração de envio por canal)
- DocumentoService
- ExecucaoFinanceiraService
- AuditoriaService
- RiscoService
- DashboardService (agregação de dados, score de gestão, indicadores do painel executivo)
- PainelRiscoService (indicadores do painel de risco, ranking, mapa por secretaria, relatório TCE de risco)
- RelatorioService
- PermissaoService (verificação, atribuição, revogação de permissões, expiração)
- WorkflowService (criação de fluxo, avanço de etapas, reprovação, notificações)

**Console Commands:**
- VerificarVencimentosCommand (`alertas:verificar-vencimentos` — cron diário)
- AgregarDashboardCommand (`dashboard:agregar-dados` — cron noturno para pré-cálculo de métricas do painel executivo)
- VerificarPermissoesExpiradasCommand (`permissoes:verificar-expiradas` — cron diário)

**Jobs:**
- EnviarNotificacaoAlertaJob (envio assíncrono de notificação com retry exponencial)

**Notifications (Laravel Notification):**
- AlertaVencimentoNotification (canais: mail + database)

**Validadores (Form Requests):**
- StoreContratoRequest / UpdateContratoRequest
- StoreFornecedorRequest / UpdateFornecedorRequest
- StoreAditivoRequest / UpdateAditivoRequest
- StoreFiscalRequest / UpdateFiscalRequest
- StoreExecucaoFinanceiraRequest
- StoreDocumentoRequest
- UpdateConfiguracaoAlertaRequest
- StoreSecretariaRequest / UpdateSecretariaRequest
- StoreUserRequest / UpdateUserRequest
- StoreRoleRequest / UpdateRoleRequest
- AssignPermissionRequest

**Transformadores (API Resources):**
- ContratoResource / ContratoCollection
- FornecedorResource / FornecedorCollection
- AditivoResource
- FiscalResource
- AlertaResource / AlertaCollection
- LogNotificacaoResource
- DocumentoResource
- PainelRiscoResource
- ExecucaoFinanceiraResource
- HistoricoAlteracaoResource
- SecretariaResource
- UserResource
- RoleResource
- PermissionResource
- WorkflowAprovacaoResource

**Enums / Constantes:**
- StatusContrato (vigente, vencido, cancelado, suspenso, encerrado, rescindido)
- TipoContrato (servico, obra, compra, locacao)
- ModalidadeContratacao (pregao_eletronico, pregao_presencial, concorrencia, tomada_preco, convite, leilao, dispensa, inexigibilidade, adesao_ata)
- TipoPagamento (mensal, por_medicao, parcelado, unico)
- CategoriaContrato (essencial, nao_essencial)
- CategoriaServico (transporte, alimentacao, tecnologia, obras, limpeza, seguranca, manutencao, saude, educacao, outros)
- NivelRisco (baixo, medio, alto)
- TipoDocumentoContratual (contrato_original, termo_referencia, publicacao_oficial, parecer_juridico, aditivo_doc, nota_empenho, nota_fiscal, ordem_servico, relatorio_medicao, relatorio_fiscalizacao, justificativa, documento_complementar)
- StatusCompletudeDocumental (completo, parcial, incompleto)
- AcaoLogDocumento (upload, download, substituicao, exclusao, visualizacao)
- TipoAditivo (prazo, valor, prazo_e_valor, supressao, reequilibrio, alteracao_clausula, misto)
- StatusAditivo (vigente, vencido, cancelado)
- StatusAprovacao (pendente, aprovado, reprovado)
- EtapaWorkflow (solicitacao, aprovacao_secretario, parecer_juridico, validacao_controladoria, homologacao)
- StatusAlerta (pendente, enviado, visualizado, resolvido)
- PrioridadeAlerta (informativo, atencao, urgente)
- CanalNotificacao (email, sistema)
- TipoEventoAlerta (vencimento_vigencia, termino_aditivo, prazo_garantia, prazo_execucao_fisica)
- CategoriaRisco (vencimento, financeiro, documental, juridico, operacional)

---

## Estrutura de Diretórios

```
app/
├── Console/
│   └── Commands/
│       ├── VerificarVencimentosCommand.php   (cron diário: alertas:verificar-vencimentos)
│       ├── AgregarDashboardCommand.php       (cron noturno: dashboard:agregar-dados)
│       └── VerificarPermissoesExpiradasCommand.php (novo — Módulo 7: permissoes:verificar-expiradas)
├── Enums/
│   ├── StatusContrato.php
│   ├── TipoContrato.php
│   ├── ModalidadeContratacao.php
│   ├── TipoPagamento.php
│   ├── CategoriaContrato.php
│   ├── CategoriaServico.php
│   ├── NivelRisco.php
│   ├── TipoDocumentoContratual.php        (12 valores — expandido no Módulo 5)
│   ├── StatusCompletudeDocumental.php     (novo — Módulo 5)
│   ├── AcaoLogDocumento.php               (novo — Módulo 5)
│   ├── TipoAditivo.php
│   ├── StatusAditivo.php
│   ├── StatusAlerta.php
│   ├── PrioridadeAlerta.php
│   ├── CanalNotificacao.php
│   ├── TipoEventoAlerta.php
│   ├── CategoriaRisco.php                   (novo — Módulo 6)
│   ├── StatusAprovacao.php              (novo — Módulo 7)
│   └── EtapaWorkflow.php                (novo — Módulo 7)
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── ContratosController.php
│   │       ├── FornecedoresController.php
│   │       ├── AditivosController.php
│   │       ├── FiscaisController.php
│   │       ├── AlertasController.php           (listagem + dashboard de alertas)
│   │       ├── DocumentosController.php
│   │       ├── ExecucoesFinanceirasController.php
│   │       ├── RelatoriosController.php
│   │       ├── SecretariasController.php
│   │       ├── UsersController.php
│   │       ├── ConfiguracoesController.php     (inclui config de alertas)
│   │       ├── PainelRiscoController.php      (novo — Módulo 6: painel de risco + relatório TCE)
│   │       ├── RolesController.php           (novo — Módulo 7: CRUD de perfis)
│   │       └── PermissoesController.php      (novo — Módulo 7: gestão de permissões)
│   ├── Middleware/
│   │   ├── EnsureUserHasPermission.php    (novo — Módulo 7: substitui EnsureUserIsAdmin + EnsureUserIsGestor)
│   │   └── SetTenantConnection.php
│   ├── Requests/
│   │   ├── StoreContratoRequest.php
│   │   ├── UpdateContratoRequest.php
│   │   ├── StoreFornecedorRequest.php
│   │   ├── UpdateFornecedorRequest.php
│   │   ├── StoreAditivoRequest.php
│   │   ├── UpdateAditivoRequest.php
│   │   ├── StoreFiscalRequest.php
│   │   ├── UpdateFiscalRequest.php
│   │   ├── StoreExecucaoFinanceiraRequest.php
│   │   ├── StoreDocumentoRequest.php
│   │   ├── UpdateConfiguracaoAlertaRequest.php
│   │   ├── StoreSecretariaRequest.php
│   │   ├── UpdateSecretariaRequest.php
│   │   ├── StoreUserRequest.php
│   │   ├── UpdateUserRequest.php
│   │   ├── StoreRoleRequest.php              (novo — Módulo 7)
│   │   ├── UpdateRoleRequest.php             (novo — Módulo 7)
│   │   └── AssignPermissionRequest.php       (novo — Módulo 7)
│   └── Resources/
│       ├── ContratoResource.php
│       ├── ContratoCollection.php
│       ├── FornecedorResource.php
│       ├── FornecedorCollection.php
│       ├── AditivoResource.php
│       ├── FiscalResource.php
│       ├── AlertaResource.php
│       ├── AlertaCollection.php
│       ├── LogNotificacaoResource.php
│       ├── DocumentoResource.php
│       ├── ExecucaoFinanceiraResource.php
│       ├── HistoricoAlteracaoResource.php
│       ├── SecretariaResource.php
│       ├── UserResource.php
│       └── PainelRiscoResource.php            (novo — Módulo 6)
├── Jobs/
│   └── EnviarNotificacaoAlertaJob.php        (envio assíncrono com retry exponencial)
├── Models/
│   ├── User.php
│   ├── Contrato.php
│   ├── Fornecedor.php
│   ├── Secretaria.php
│   ├── Fiscal.php
│   ├── Aditivo.php
│   ├── Documento.php                      (expandido — Módulo 5: SoftDeletes, is_versao_atual, nome padronizado)
│   ├── LogAcessoDocumento.php             (novo — Módulo 5: log append-only de acesso a documentos)
│   ├── ExecucaoFinanceira.php
│   ├── HistoricoAlteracao.php
│   ├── Alerta.php
│   ├── ConfiguracaoAlerta.php
│   ├── LogNotificacao.php
│   ├── DashboardAgregado.php
│   ├── ConfiguracaoLimiteAditivo.php
│   ├── Role.php                          (novo — Módulo 7: perfis de usuário)
│   ├── Permission.php                    (novo — Módulo 7: permissões granulares)
│   ├── UserPermission.php                (novo — Módulo 7: permissões individuais/temporárias)
│   └── WorkflowAprovacao.php             (novo — Módulo 7: etapas de aprovação)
├── Notifications/
│   └── AlertaVencimentoNotification.php      (canais: mail + database)
├── Observers/
│   ├── ContratoObserver.php                  (audit trail + score de risco)
│   └── DocumentoObserver.php                 (novo — recalcula completude documental + log de acesso)
├── Policies/
│   ├── ContratoPolicy.php
│   ├── FornecedorPolicy.php
│   ├── AditivoPolicy.php
│   ├── DocumentoPolicy.php                (novo — Módulo 5: controle de acesso por perfil)
│   ├── RolePolicy.php                    (novo — Módulo 7: controle de acesso a perfis)
│   └── WorkflowPolicy.php               (novo — Módulo 7: controle de acesso ao workflow)
└── Services/
    ├── ContratoService.php
    ├── FornecedorService.php
    ├── AditivoService.php
    ├── FiscalService.php
    ├── AlertaService.php                     (geração, resolução, prioridade)
    ├── NotificacaoService.php                (orquestração de envio por canal)
    ├── DocumentoService.php
    ├── ExecucaoFinanceiraService.php
    ├── AuditoriaService.php                  (log de alterações)
    ├── RiscoService.php                      (cálculo de score de risco)
    ├── DashboardService.php                 (agregação, score de gestão, indicadores)
    ├── PainelRiscoService.php               (novo — Módulo 6: indicadores, ranking, mapa, relatório TCE)
    ├── RelatorioService.php
    ├── PermissaoService.php               (novo — Módulo 7: verificação, atribuição, revogação de permissões)
    └── WorkflowService.php               (novo — Módulo 7: criação de fluxo, avanço, reprovação, notificações)

database/
├── migrations/
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── SecretariaSeeder.php
│   ├── UserSeeder.php
│   ├── ConfiguracaoAlertaSeeder.php
│   ├── ConfiguracaoLimiteAditivoSeeder.php  (serviço=25%, obra=50%)
│   ├── RoleSeeder.php                    (novo — Módulo 7: 8 perfis padrão)
│   ├── PermissionSeeder.php              (novo — Módulo 7: permissões granulares)
│   └── RolePermissionSeeder.php          (novo — Módulo 7: associação role ↔ permissions)
└── factories/
    ├── UserFactory.php
    ├── ContratoFactory.php
    ├── FornecedorFactory.php
    ├── AlertaFactory.php
    ├── AditivoFactory.php            (testes do módulo de aditivos)
    └── DocumentoFactory.php          (novo — testes do módulo de documentos)

routes/
├── web.php
└── admin.php

resources/
├── views/
│   ├── layout/
│   │   └── layout.blade.php
│   ├── components/
│   │   ├── head.blade.php
│   │   ├── sidebar.blade.php
│   │   ├── navbar.blade.php
│   │   ├── breadcrumb.blade.php
│   │   ├── footer.blade.php
│   │   └── script.blade.php
│   ├── admin/
│   │   ├── dashboard/
│   │   │   └── index.blade.php
│   │   ├── contratos/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php         (wizard multi-etapa)
│   │   │   ├── edit.blade.php
│   │   │   └── show.blade.php           (abas: dados, fiscal, financeiro, documentos, auditoria)
│   │   ├── fornecedores/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── aditivos/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   ├── edit.blade.php
│   │   │   └── show.blade.php           (timeline de aditivos do contrato + detalhes)
│   │   ├── alertas/
│   │   │   ├── index.blade.php              (listagem de alertas com filtros)
│   │   │   └── dashboard.blade.php          (dashboard de alertas com indicadores)
│   │   ├── documentos/
│   │   │   ├── index.blade.php             (Central de Documentos — busca + listagem com completude)
│   │   │   └── dashboard.blade.php         (dashboard de documentos — 4 indicadores + ranking pendências)
│   │   ├── painel-risco/
│   │   │   └── index.blade.php             (novo — Módulo 6: dashboard completo de risco)
│   │   ├── relatorios/
│   │   │   └── index.blade.php
│   │   ├── secretarias/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── users/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── roles/
│   │   │   ├── index.blade.php           (novo — Módulo 7)
│   │   │   ├── create.blade.php          (novo — Módulo 7)
│   │   │   └── edit.blade.php            (novo — Módulo 7)
│   │   ├── permissoes/
│   │   │   └── index.blade.php           (novo — Módulo 7: gestão de permissões por role)
│   │   └── configuracoes/
│   │       └── index.blade.php
│   └── auth/
│       ├── login.blade.php
│       └── forgot-password.blade.php
└── css/
    └── app.css

tests/
├── Unit/
│   └── Services/
│       ├── ContratoServiceTest.php
│       ├── AlertaServiceTest.php
│       ├── NotificacaoServiceTest.php
│       ├── RiscoServiceTest.php
│       ├── AuditoriaServiceTest.php
│       ├── DashboardServiceTest.php
│       ├── AditivoServiceTest.php    (limites legais, cálculo percentual, reequilíbrio)
│       ├── DocumentoServiceTest.php  (upload, versionamento, completude, nomes padronizados)
│       ├── PainelRiscoServiceTest.php (novo — Módulo 6: indicadores, ranking, mapa, relatório)
│       ├── PermissaoServiceTest.php      (novo — Módulo 7)
│       └── WorkflowServiceTest.php       (novo — Módulo 7)
└── Feature/
    ├── ContratoTest.php
    ├── FornecedorTest.php
    ├── FiscalTest.php
    ├── AlertaTest.php
    ├── AditivoTest.php               (fluxo completo de criação e atualização do contrato pai)
    ├── LimiteLegalAditivoTest.php    (testes de bloqueio e alerta ao atingir limite)
    ├── DocumentoTest.php             (fluxo completo: upload → completude → score → log)
    ├── DocumentoRelatorioTest.php    (relatório TCE)
    ├── PainelRiscoTest.php          (novo — Módulo 6: fluxo completo, filtros, exportação PDF)
    ├── PerfilUsuarioTest.php          (novo — Módulo 7: acesso por perfil)
    ├── PermissaoTemporariaTest.php    (novo — Módulo 7: expires_at)
    ├── WorkflowAprovacaoTest.php      (novo — Módulo 7: fluxo completo 5 etapas)
    └── Commands/
        ├── VerificarVencimentosCommandTest.php
        └── AgregarDashboardCommandTest.php
```

---

## Regras de Banco de Dados

### Gerais
- Migrations sempre com **rollback funcional** (`down()` implementado)
- Tabelas nomeadas em português plural snake_case
- Foreign keys com **cascade rules explícitas**
- Soft deletes **obrigatório** em: contratos, aditivos, fornecedores, documentos
- `timestamps()` sempre incluído
- Tabelas de auditoria (`historico_alteracoes`) são **imutáveis** — sem update/delete
- Tabela `log_notificacoes` é **append-only** — sem update/delete
- Tabela `log_acesso_documentos` é **append-only** — sem update/delete (RN-122, ADR-035)
- **Índice obrigatório** em `contratos.data_fim` (performance do motor de monitoramento)
- **Índice composto** em `alertas` (contrato_id + tipo_evento + dias_antecedencia_config) para unique constraint
- **Índice** em `contratos.secretaria_id` (ranking por secretaria no dashboard executivo)
- **Índice** em `contratos.status` (filtro de contratos ativos no dashboard)
- **Índice** em `contratos.valor_global` (faixas de valor no dashboard)
- **Índice** em `contratos.categoria` (filtro de contratos essenciais)
- **Índice composto** em `dashboard_agregados` (data_referencia + tipo_metrica + chave)
- **Índice** em `aditivos.contrato_id` (performance na listagem e cálculo de percentual acumulado)
- **Índice composto** em `aditivos` (contrato_id + data_assinatura) para consultas de frequência de aditivos
- **Índice composto** em `documentos` (documentable_type + documentable_id) para consulta de documentos por contrato/aditivo
- **Índice** em `documentos.tipo_documento` (filtro por tipo no dashboard e busca)
- **Índice** em `documentos.is_versao_atual` (listagem de apenas versões atuais)
- **Índice** em `log_acesso_documentos.documento_id` (histórico de acesso por documento)
- **Índice** em `log_acesso_documentos.user_id` (auditoria por usuário)

### Tipos de Dados

| Tipo de Dado | Tipo no Banco | Observação |
|---|---|---|
| Valores monetários | `decimal(15,2)` | Nunca usar float |
| Percentuais | `decimal(5,2)` | Nunca usar float |
| Score de risco | `integer` | Calculado (0-100+) |
| Textos curtos | `varchar(255)` | Padrão |
| Textos longos | `text` | Descrições, observações, objeto do contrato |
| Booleanos | `boolean` | Com default definido |
| Datas | `date` | Datas de vigência, vencimento |
| Data/hora | `datetime` | Timestamps, logs |
| Números de processo | `varchar(50)` | Números de licitação/processo/empenho |
| Dotação orçamentária | `varchar(255)` | Classificação orçamentária completa |
| IP address | `varchar(45)` | IPv4 e IPv6 |

### Tabelas do Sistema

**Módulo Contratos (Cadastro Inteligente):**
- `contratos` — Contratos municipais (campos expandidos: modalidade, score_risco, percentual_executado, etc.)
- `fiscais` — Fiscais de contrato (com histórico de troca)
- `aditivos` — Aditivos contratuais (expandida: numero_sequencial, data_inicio_vigencia, valor_acrescimo, valor_supressao, percentual_acumulado, fundamentacao_legal, justificativa_tecnica, campos de reequilíbrio)
- `configuracoes_limite_aditivo` — Limites legais de acréscimo por tipo de contrato (25% serviços, 50% obras — configurável pelo admin)
- `execucoes_financeiras` — Registros de execução financeira/medições
- `historico_alteracoes` — Log de auditoria de todas as alterações (polimórfico, imutável)

**Módulo Documentos (Central de Documentos — Módulo 5):**
- `documentos` — Documentos vinculados a contratos e aditivos (polimórfico, com tipo, versionamento, is_versao_atual, soft delete — expandida no Módulo 5)
- `log_acesso_documentos` — Log de acesso e ações sobre documentos (append-only, imutável — RN-122, ADR-035)

**Módulo Cadastros:**
- `fornecedores` — Empresas fornecedoras (com validação de CNPJ)
- `secretarias` — Secretarias/órgãos da prefeitura

**Módulo Alertas (Motor de Monitoramento):**
- `alertas` — Alertas de vencimento (expandida: tipo_evento, dias_antecedencia_config, data_disparo, tentativas_envio)
- `configuracoes_alerta` — Prazos configuráveis de alerta (6 prazos padrão: 120, 90, 60, 30, 15, 7 dias)
- `log_notificacoes` — Log de cada envio de notificação (canal, destinatário, sucesso, resposta_gateway)

**Módulo Dashboard Executivo:**
- `dashboard_agregados` — Dados pré-calculados do painel executivo (atualizado diariamente via cron noturno)

**Módulo Usuários:**
- `users` — Usuários do sistema

**Módulo Perfis de Usuário (RBAC — Módulo 7):**
- `roles` — Perfis de usuário dinâmicos (8 padrão via seeder + customizáveis pelo admin)
- `permissions` — Permissões granulares no formato `recurso.acao`
- `role_permissions` — Vínculo N:N entre roles e permissions (pivot)
- `user_permissions` — Permissões individuais extras, com `expires_at` para temporárias
- `user_secretarias` — Vínculo N:N entre users e secretarias (escopo de acesso)
- `workflow_aprovacoes` — Registro de etapas de aprovação (polimórfico: aprovavel_type + aprovavel_id)

---

## Regras de Arquitetura

### Obrigatório

| Camada | Propósito | Regra |
|---|---|---|
| Form Requests | Validação de input | Toda validação de request **deve** estar no Form Request, nunca inline |
| API Resources | Output da API | Toda resposta **deve** usar Resource, nunca retornar Model diretamente |
| Services | Lógica de negócio | Lógica complexa ou com múltiplas operações **deve** estar em um Service |
| Enums | Valores fixos | Status, tipos, categorias **devem** usar Enum nativo PHP 8.1+, nunca strings hardcoded |
| $fillable | Mass assignment | **Sempre** usar $fillable explícito em todos os Models |
| Policies | Autorização | Verificações de autorização **devem** usar Policies |
| Observers | Eventos de Model | Audit trail e cálculos derivados via Eloquent Observers |
| Commands | Tarefas agendadas | Scheduled commands via `schedule()` no Kernel |
| Jobs | Tarefas assíncronas | Processamento em background via Laravel Queue (Redis) |
| Notifications | Notificações multi-canal | Laravel Notification para email + database |

### Anti-patterns Proibidos

| Anti-pattern | Motivo | Solução |
|---|---|---|
| Lógica no Controller | Viola separação de responsabilidades | Mover para Service |
| Queries raw sem necessidade | Risco de SQL injection, difícil manutenção | Usar Eloquent |
| Migration sem rollback | Impossibilita reverter mudanças | Sempre implementar `down()` |
| Variáveis de ambiente hardcoded | Quebra em diferentes ambientes | Usar `config()` / `env()` |
| Overengineering | Complexidade desnecessária | Só abstrair quando houver uso concreto |
| N+1 queries | Problema de performance grave | Usar eager loading (`with()`) |
| Retornar Model na API | Expõe estrutura interna do banco | Usar API Resource |
| Deletar registros de auditoria | Compromete integridade do log | Tabela imutável, nunca delete/update |
| Job sem retry/backoff | Notificações perdidas silenciosamente | Usar `$tries`, `$backoff` no Job |
| Notificação síncrona | Bloqueia request do usuário | Usar queue para envio assíncrono |
| Deletar log de notificação | Perde rastreabilidade de envios | Tabela imutável (append-only) |

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

---

## Regras de Upload / Mídia

| Entidade | Tipos Permitidos | Tamanho Máximo |
|---|---|---|
| Documento de contrato | pdf | 20MB |
| Documento de aditivo | pdf | 20MB |
| Comprovante/Anexo geral | pdf, jpg, png | 5MB |

- Upload múltiplo permitido (vários arquivos por vez)
- Versionamento automático (mesmo tipo de documento → incrementa versão)
- Armazenamento local em desenvolvimento, S3 em produção
- Nomes de arquivo sanitizados (sem caracteres especiais)
- Organização por contrato e tipo: `documentos/contratos/{contrato_id}/{tipo_documento}/{arquivo}` (ADR-033)
- Nome de arquivo padronizado automaticamente: `contrato_{numero}_{tipo}_v{versao}.pdf` (RN-121)
- Nome original preservado no campo `nome_original` do Model
- Versionamento automático: campo `versao` (int) + `is_versao_atual` (boolean). Versões anteriores mantidas, nunca deletadas (ADR-034)
- Soft delete obrigatório em documentos — nunca deletar fisicamente do storage (RN-134)
- Todo acesso (upload, download, visualização, substituição, exclusão) registrado em `log_acesso_documentos` (RN-122, ADR-035)
- Documentos nunca expostos publicamente (acesso via controller autenticado + DocumentoPolicy)
- Registro automático de quem fez upload (uploaded_by) e data/hora
- Classificação obrigatória por tipo (TipoDocumentoContratual — 12 valores)

---

## Segurança

### Segurança de Acesso
- Autenticação via **Session-based** (Laravel padrão) para web
- CSRF em todas as rotas web
- Senhas: **Argon2id** (driver `argon2id` no `config/hashing.php`) — resistente a ataques de GPU e side-channel (ADR-044)
- **MFA opcional** para admin/gestor via TOTP (Google Authenticator / Authy) — ADR-045
- **Bloqueio de login**: lockout após 5 tentativas, cooldown 15min (configurável) — ADR-046
- **Logs de login**: tabela `login_logs` (user_id, tenant_id, ip_address, user_agent, success, created_at) — ADR-048
- **Sessão**: expiração automática (`SESSION_LIFETIME` no `.env`, padrão 120min) — ADR-049
- Rate limiting em endpoints de login
- Dados sensíveis nunca expostos (CPF/CNPJ parcialmente mascarados em listagens)
- Inputs financeiros sanitizados
- Documentos acessíveis apenas via controller autenticado (não via URL pública)
- **Audit trail** obrigatório em contratos (toda alteração logada com IP)

### Segurança de Dados
- **HTTPS obrigatório** em produção (ForceHttps middleware)
- **TLS 1.2+** mínimo (configuração do servidor web / proxy reverso)
- Criptografia de campos sensíveis: `$casts = ['campo' => 'encrypted']` do Laravel
- **Backup criptografado** (configuração do serviço de backup)
- **Logs imutáveis**: tabelas de log são append-only (sem UPDATE/DELETE)
- Arquivos sensíveis em storage criptografado (S3 server-side encryption)

### LGPD / Privacidade
- Dados de fornecedores (CNPJ, contatos) protegidos
- Documentos contratuais nunca expostos publicamente (acesso somente via DocumentosController autenticado)
- Todo acesso a documento registrado em `log_acesso_documentos` (RN-122, ADR-035)
- Documentos excluídos mantidos em storage (soft delete — RN-134)
- DocumentoPolicy aplicada em todos os endpoints de documentos (RN-130)
- Registros de auditoria imutáveis (nunca deletar)
- **Registro de base legal** por tratamento de dados pessoais (RN-210)
- **Controle de acesso por perfil**: Policy obrigatório em todos os endpoints (RN-211)
- **Log de acesso a dados sensíveis**: login_logs + log_acesso_documentos (RN-211)
- **Política de retenção** de dados configurável por tenant (RN-212)
- **Anonimização**: dados pessoais anonimizáveis sob solicitação (RN-213)

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

---

## Auditoria e Conformidade

### Obrigatório
- **Relatório de logs por período**: exportável em PDF/CSV para auditoria externa
- **Histórico imutável**: tabelas de log são append-only (sem UPDATE/DELETE em historico_alteracoes, log_acesso_documentos, log_notificacoes, login_logs)
- **Hash de integridade de documento**: `hash('sha256', file_get_contents($arquivo))` armazenado no campo `hash_integridade` do Model Documento no momento do upload (ADR-047, RN-220)
- **Verificação de integridade**: hash verificável a qualquer momento — comparação do hash armazenado com hash recalculado do arquivo em storage (RN-221)
- **Relatório de conformidade**: lista de documentos com hash, data de upload, responsável, status de integridade
- **Proteção contra adulteração**: hash SHA-256 serve como prova de que o documento não foi alterado após upload

### Tabelas Imutáveis (append-only)
- `historico_alteracoes` — audit trail de entidades
- `log_acesso_documentos` — acesso a documentos
- `log_notificacoes` — envio de notificações
- `login_logs` — acessos ao sistema
- `workflow_aprovacoes` — etapas de aprovação (imutável após aprovação/reprovação)

---

## Performance e Escalabilidade

### Requisitos Mínimos por Prefeitura
- 5.000 a 20.000 contratos
- 50.000+ documentos
- 100 usuários ativos simultâneos

### Regras Obrigatórias
- **Tempo de resposta**: < 2 segundos para qualquer página
- **Paginação obrigatória** em todas as listagens (máximo 50 registros por página, configurável)
- **Indexação adequada**: índices em todas as colunas usadas em WHERE, JOIN e ORDER BY frequentes
- **Processamento assíncrono**: OCR (Fase 2), notificações, agregações de dashboard — sempre via Queue
- **Cache Redis**: dashboard (TTL 24h), painel de risco (TTL 24h), dados frequentes
- **Jobs noturnos**: agregação de dados fora do horário comercial (AgregarDashboardCommand)
- **Eager loading** obrigatório: usar `with()` para evitar N+1 queries
- **Disponibilidade**: 24/7 (infraestrutura de produção com monitoramento)

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
- Testar cálculo de score de risco com diferentes cenários
- Testar validação de CNPJ (dígito verificador)
- Testar imutabilidade do audit trail
- Testar geração de alertas por faixa de dias (120, 90, 60, 30, 15, 7)
- Testar prioridade automática (informativo/atenção/urgente)
- Testar não-duplicação de alertas (unique constraint)
- Testar resolução automática de alertas (via aditivo de prazo)
- Testar retry com backoff exponencial (EnviarNotificacaoAlertaJob)
- Testar bloqueio de aditivo retroativo sem justificativa
- Testar cálculos de agregação do DashboardService (indicadores financeiros)
- Testar score de gestão contratual (penalidades por critério)
- Testar AgregarDashboardCommand (processamento noturno)
- Testar cache Redis do dashboard (hit, miss, invalidação)
- Testar performance do dashboard (<2 segundos com dados agregados)
- Testar geração automática de numero_sequencial por contrato
- Testar cálculo de percentual_acumulado após múltiplos aditivos
- Testar bloqueio por limite legal (is_bloqueante = true)
- Testar alerta por limite legal (is_bloqueante = false) com justificativa obrigatória
- Testar atualização automática do contrato pai após aditivo (valor_global, data_fim, score)
- Testar critérios de score de risco relacionados a aditivos (RN-106, RN-107, RN-108)
- Testar fluxo de reequilíbrio econômico-financeiro (campos obrigatórios condicionais)
- Testar imutabilidade de aditivo salvo (RN-116) e auditoria (RN-117)
- Testar upload de documento com versionamento automático (RN-120, RN-121)
- Testar cálculo de completude documental por checklist (RN-128, RN-129)
- Testar nomes padronizados de arquivo (contrato_{numero}_{tipo}_v{versao}.pdf)
- Testar imutabilidade do log_acesso_documentos (append-only — RN-122)
- Testar autorização por perfil via DocumentoPolicy (admin, gestor, consulta — RN-130)
- Testar geração de relatório TCE com lista de documentos (RN-133)
- Testar soft delete de documento (exclusão lógica mantém no storage — RN-134)
- Testar cálculo expandido do score de risco com 5 categorias (RN-136 a RN-142)
- Testar indicadores do painel de risco (5 cards — RN-144)
- Testar ranking de risco ordenado por score DESC (RN-146)
- Testar categorias múltiplas de risco simultâneas por contrato (RN-147)
- Testar mapa de risco por secretaria com ordenação por críticos DESC (RN-148, RN-149)
- Testar geração de relatório de risco para TCE em PDF (RN-150 a RN-152)
- Testar alertas preventivos com mensagens contextualizadas (RN-153)
- Testar performance do painel de risco (<2 segundos com cache Redis)
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
