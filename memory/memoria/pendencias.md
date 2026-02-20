# Memória — Pendências por Módulo

> Extraído de `banco-de-memoria.md`. Carregar ao planejar trabalho ou verificar o que falta implementar.
> Atualizado conforme pendências são concluídas ou criadas.

---

### Módulo: Infraestrutura
- [x] Criar projeto Laravel 12 via Sail *(IMP-012)*
- [x] Configurar Docker (MySQL 8 + Redis + MinIO) *(IMP-012)*
- [x] Integrar template WowDash (assets, layout, componentes) *(IMP-013)*
- [x] Configurar autenticação (login, logout, forgot password) *(IMP-013)*
- [x] Criar migrations base (roles, secretarias, fornecedores, alter login_logs, alter users FK) *(IMP-014)*
- [x] Configurar S3-compatible storage (MinIO para dev, AWS S3 para prod) — ADR-043 *(IMP-012)*

### Módulo: Multi-Tenant (Database-per-Tenant)
- [x] Migration banco master: tabela `tenants` (nome, slug, database_name, database_host, is_ativo, plano) *(IMP-012)*
- [ ] Migration banco master: tabela `tenant_users` (user_id, tenant_id, role) — *adiada: sem necessidade concreta na Fase 1a*
- [x] Model: Tenant (banco master) *(IMP-012)*
- [x] Middleware: SetTenantConnection (resolve tenant e configura connection MySQL) *(IMP-012)*
- [x] Comando artisan: `tenant:create` (provisionar novo tenant: criar banco, aplicar migrations, seeder admin) *(IMP-012)*
- [x] Comando artisan: `tenant:migrate` (aplicar migrations pendentes em todos os tenants ativos) *(IMP-012)*
- [x] Configuração dinâmica de connection MySQL em runtime *(IMP-012)*
- [ ] Configuração de storage isolado por tenant (prefixo S3 por slug)
- [x] Configuração de cache Redis com prefixo por tenant *(IMP-012)*

### Módulo: Painel Administrativo SaaS (Gestão de Tenants)
- [x] Migration banco master: tabela `admin_users` (nome, email, password, is_ativo, mfa_secret, last_login_at) *(IMP-012)*
- [x] Migration banco master: tabela `admin_login_logs` (admin_user_id, ip_address, user_agent, success, created_at — append-only) *(IMP-012)*
- [x] Model: AdminUser (guard `admin`, banco master) *(IMP-012)*
- [x] Guard `admin` em `config/auth.php` (separado do guard `web` dos tenants) *(IMP-012)*
- [x] Middleware: EnsureAdminSaaS (verifica guard admin + is_ativo) *(IMP-012)*
- [x] Rotas do painel: prefixo `/admin-saas` sem subdomínio de tenant *(IMP-012)*
- [x] TenantController (painel admin): index (listar), create/store (provisionar), show (detalhes), ativar/desativar *(IMP-012)*
- [x] Views: admin-saas/tenants/index.blade.php, create.blade.php, show.blade.php *(IMP-012)*
- [x] Seeder: AdminUserSeeder (criar usuário root inicial) *(IMP-012)*
- [ ] MFA obrigatório para AdminUser (TOTP)

### Módulo: Segurança Expandida
- [x] Configurar hashing driver Argon2id (`config/hashing.php`) — ADR-044 *(IMP-012)*
- [x] Migration banco tenant: tabela `login_logs` (user_id, ip_address, user_agent, success, created_at) — ADR-048 *(IMP-013)*
- [x] Model: LoginLog (append-only, sem update/delete) *(IMP-013)*
- [ ] Implementar MFA opcional via TOTP para admin/gestor — ADR-045
- [x] Implementar bloqueio de login após 5 tentativas com cooldown 15min — ADR-046 *(IMP-013)*
- [x] Implementar expiração de sessão configurável (SESSION_LIFETIME) — ADR-049 *(IMP-013)*
- [ ] Middleware ForceHttps para produção
- [ ] Adicionar campo `hash_integridade` ao Model Documento — ADR-047
- [ ] Implementar cálculo de hash SHA-256 no upload de documento (DocumentoService) — RN-220
- [ ] Implementar verificação de integridade (recalcular hash e comparar) — RN-221
- [ ] Implementar relatório de logs exportável (PDF/CSV) — RN-222
- [x] Criar seeders iniciais (AdminUserSeeder IMP-012, RoleSeeder IMP-014) — *SecretariaSeeder opcional para futuro*

### Módulo: Contratos (Cadastro Inteligente)
- [x] Migration da tabela contratos (campos expandidos: modalidade, score_risco, percentual_executado, etc.) *(IMP-016)*
- [x] Migration da tabela fiscais (com histórico de trocas) *(IMP-016)*
- [ ] Migration da tabela aditivos — *movido para Fase 3c*
- [x] Migration da tabela documentos (com tipo_documento e versao) *(IMP-016 — basico, expandido Fase 3b)*
- [x] Migration da tabela execucoes_financeiras *(IMP-016)*
- [x] Migration da tabela historico_alteracoes (polimórfica, imutável) *(IMP-016)*
- [x] Enums: StatusContrato, TipoContrato, ModalidadeContratacao, TipoPagamento, CategoriaContrato, CategoriaServico, NivelRisco, TipoDocumentoContratual *(IMP-016)*
- [x] Models: Contrato, Fiscal, ExecucaoFinanceira, HistoricoAlteracao, Documento *(IMP-016)*
- [x] Services: ContratoService, RiscoService, AuditoriaService, FiscalService, ExecucaoFinanceiraService *(IMP-016)*
- [ ] Observer: ContratoObserver (audit trail + recálculo de score) — *opcional, logica em ContratoService*
- [x] Formulário multi-etapa (wizard) para cadastro de contrato *(IMP-016)*
- [x] Tela de detalhes do contrato com abas (dados, fiscal, financeiro, documentos, auditoria) *(IMP-016)*
- [x] CRUD de fiscais (com troca e histórico) *(IMP-016)*
- [x] Registro de execuções financeiras *(IMP-016)*
- [ ] Upload múltiplo de documentos com classificação por tipo — *movido para Fase 3b*
- [ ] Versionamento de documentos — *movido para Fase 3b*
- [x] Validação de CNPJ (digito verificador — FornecedorService + CnpjValido Rule) *(IMP-014)*
- [x] Validações condicionais por modalidade (dispensa → fundamento legal, obra → resp. técnico) *(IMP-016)*
- [x] Cálculo automático de score de risco *(IMP-016)*
- [x] Cálculo automático de percentual executado *(IMP-016)*
- [x] Filtros inteligentes na listagem (status, secretaria, modalidade, nivel_risco) *(IMP-016)*

### Módulo: Aditivos (Gestão de Aditivos — Módulo 4)
- [ ] Atualizar enum TipoAditivo (+ reequilibrio, alteracao_clausula, misto — 4→7 valores)
- [ ] Migration de alteração da tabela aditivos (novos campos: numero_sequencial, data_inicio_vigencia, valor_acrescimo, valor_supressao, percentual_acumulado, fundamentacao_legal, justificativa_tecnica, justificativa_excesso_limite, parecer_juridico_obrigatorio, campos de reequilíbrio)
- [ ] Migration da tabela configuracoes_limite_aditivo (tipo_contrato, percentual_limite, is_bloqueante, is_ativo)
- [ ] Seeder: ConfiguracaoLimiteAditivoSeeder (servico=25%, obra=50%)
- [ ] Model: ConfiguracaoLimiteAditivo
- [ ] Atualizar Model Aditivo ($fillable + relacionamentos + novos campos)
- [ ] Atualizar StoreAditivoRequest / UpdateAditivoRequest (validações condicionais por tipo, limites legais)
- [ ] Atualizar AditivoService (geração numero_sequencial, cálculo percentual_acumulado, verificação limite legal, atualização contrato pai, processarReequilibrio())
- [ ] Atualizar RiscoService (novos critérios: RN-106, RN-107, RN-108)
- [ ] Atualizar AditivoResource (novos campos)
- [ ] Formulário de criação de aditivo com campos condicionais por tipo
- [ ] Exibição em tempo real de percentual acumulado e limite legal no formulário
- [ ] Alerta visual de limite legal ultrapassado (is_bloqueante e modo alerta)
- [ ] Página de detalhes/timeline do aditivo (aditivos/show.blade.php)
- [ ] Dashboard de aditivos (indicadores anuais — RN-109 a RN-114)
- [ ] AditivoFactory para testes
- [ ] Testes: AditivoServiceTest (limites legais, percentual, reequilíbrio)
- [ ] Testes: AditivoTest (fluxo completo)
- [ ] Testes: LimiteLegalAditivoTest (bloqueio e alerta)
- [ ] Índices em aditivos (contrato_id, composto contrato_id+data_assinatura)

### Módulo: Alertas (Motor de Monitoramento)
- [ ] Migration da tabela alertas (expandida: tipo_evento, dias_antecedencia_config, data_disparo, tentativas_envio)
- [ ] Migration da tabela configuracoes_alerta (dias_antecedencia, prioridade, is_ativo)
- [ ] Migration da tabela log_notificacoes (canal, destinatario, sucesso, resposta_gateway)
- [ ] Índice em contratos.data_fim + índice composto em alertas
- [ ] Enums: StatusAlerta (add enviado), CanalNotificacao, TipoEventoAlerta
- [ ] Model: LogNotificacao
- [ ] Command: VerificarVencimentosCommand (alertas:verificar-vencimentos)
- [ ] Job: EnviarNotificacaoAlertaJob (retry exponencial, max 3 tentativas)
- [ ] Notification: AlertaVencimentoNotification (canais: mail + database)
- [ ] Service: AlertaService (geração, resolução, prioridade automática)
- [ ] Service: NotificacaoService (orquestração de envio por canal)
- [ ] Seeder: ConfiguracaoAlertaSeeder (6 prazos: 120, 90, 60, 30, 15, 7 dias)
- [ ] Scheduler: registrar VerificarVencimentosCommand no schedule() do Kernel
- [ ] Queue: configurar Redis como driver de filas
- [ ] Dashboard de alertas (indicadores: vencendo 120d/60d/30d, vencidos, secretarias com risco)
- [ ] Listagem de alertas com filtros (secretaria, criticidade, tipo contrato, faixa valor)
- [ ] Tela de configuração de prazos de alerta (admin)
- [ ] Notificação interna no sistema (sino/badge no navbar)
- [ ] Resolução automática de alertas ao registrar aditivo de prazo
- [ ] Bloqueio preventivo: contrato vencido → IRREGULAR, aditivo retroativo exige justificativa
- [ ] Contrato essencial → prioridade elevada nos alertas
- [ ] Email institucional: template de email para alertas de vencimento
- [ ] Relatório de efetividade mensal (contratos regularizados vs vencidos)

### Módulo: Dashboard Executivo (Painel Estratégico)
- [ ] Migration da tabela dashboard_agregados (dados pré-calculados)
- [ ] Índices adicionais em contratos: data_fim, secretaria_id, status, valor_global, categoria
- [ ] Model: DashboardAgregado
- [ ] Service: DashboardService (agregação, consulta, score de gestão)
- [ ] Command: AgregarDashboardCommand (dashboard:agregar-dados — cron noturno)
- [ ] Scheduler: registrar AgregarDashboardCommand no schedule()
- [ ] Cache Redis para dados do dashboard (TTL 24h + invalidação manual)
- [ ] Bloco 1: Visão Geral Financeira (5 cards de indicadores)
- [ ] Bloco 2: Mapa de Risco Contratual (donut chart com ApexCharts)
- [ ] Bloco 3: Vencimentos por Janela de Tempo (gráfico/tabela 5 faixas)
- [ ] Bloco 4: Distribuição por Secretaria (ranking com tabela)
- [ ] Bloco 5: Contratos Essenciais (painel especial de alerta)
- [ ] Filtros inteligentes (secretaria, faixa valor, risco, tipo, modalidade, fonte recurso)
- [ ] Score de Gestão Contratual (nota 0-100 com classificação)
- [ ] Tendência Mensal (mini BI — últimos 12 meses)
- [ ] Ranking de Fornecedores (top 10 por volume, contratos, aditivos)
- [ ] Visão do Controlador (irregularidades, log recente, aditivos acima de limite)
- [ ] Botão "Atualizar Dados" (atualização manual sob demanda)
- [ ] JS do dashboard (assets/js/dashboardExecutivo.js com ApexCharts)
- [ ] Testes unitários: DashboardService (cálculos de agregação, score de gestão)
- [ ] Testes de feature: AgregarDashboardCommand (processamento noturno)
- [ ] Testes de performance: dashboard carrega em <2 segundos

### Módulo: Documentos (Central de Documentos — Módulo 5)

**Schema e Models:**
- [ ] Atualizar enum TipoDocumentoContratual (7 → 12 valores: + nota_empenho, nota_fiscal, relatorio_medicao, relatorio_fiscalizacao, justificativa; renomear `outros` → `documento_complementar`)
- [ ] Novo enum: StatusCompletudeDocumental (completo, parcial, incompleto)
- [ ] Novo enum: AcaoLogDocumento (upload, download, substituicao, exclusao, visualizacao)
- [ ] Migration: alterar tabela `documentos` (adicionar: nome_original, nome_arquivo, is_versao_atual, deleted_at; renomear `nome` → `nome_original`; ajustar enum tipo_documento para 12 valores)
- [ ] Migration: criar tabela `log_acesso_documentos` (documento_id, user_id, acao, ip_address, created_at — append-only)
- [ ] Índices em `documentos`: composto (documentable_type + documentable_id), tipo_documento, is_versao_atual
- [ ] Índices em `log_acesso_documentos`: documento_id, user_id
- [ ] Model: atualizar Documento ($fillable, SoftDeletes, is_versao_atual, nome_original, nome_arquivo, relacionamento hasMany LogAcessoDocumento)
- [ ] Model: LogAcessoDocumento (novo — $fillable, belongsTo Documento e User, sem SoftDeletes, sem updated_at)

**Controller e Service:**
- [ ] DocumentoService: método upload() com geração de nome padronizado (RN-121), versionamento automático (RN-120), registro em storage por contrato/tipo (ADR-033), log de acesso (RN-122)
- [ ] DocumentoService: método download() com verificação de autorização (DocumentoPolicy) e registro de log de acesso
- [ ] DocumentoService: método calcularCompletude(Contrato) — retorna StatusCompletudeDocumental (RN-128)
- [ ] DocumentoService: método verificarPendenciasDocumentais(Contrato) — retorna array de tipos pendentes do checklist (RN-129)
- [ ] DocumentoService: método gerarIndicadoresDashboard() — retorna os 4 indicadores (RN-132)
- [ ] DocumentosController: atualizar upload múltiplo; adicionar download autenticado; adicionar versões; adicionar soft delete
- [ ] DocumentosController: adicionar endpoint de busca inteligente com filtros combinados (RN-131)
- [ ] DocumentosController: adicionar endpoint do dashboard de documentos

**Autorização e Validação:**
- [ ] Novo: DocumentoPolicy (view, create, download, delete — por permissão RBAC: documento.visualizar, documento.criar, documento.download, documento.excluir — RN-130)
- [ ] StoreDocumentoRequest: atualizar validação (max:20480 KB, tipos MIME, tipo_documento obrigatório com 12 valores)
- [ ] DocumentoResource: atualizar (incluir versao, is_versao_atual, nome_original, nome_arquivo, tipo_documento label)

**Observer:**
- [ ] DocumentoObserver (novo): ao criar/excluir documento → recalcular completude do contrato (ADR-036); registrar log de acesso

**Relatório:**
- [ ] RelatorioService: método gerarRelatorioTCEContrato(Contrato) — lista documentos com tipo, nome, versão, data upload, responsável, status (RN-133). Exportar em PDF

**Views:**
- [ ] `documentos/index.blade.php`: Central de Documentos standalone (4 cards indicadores + busca + filtros + listagem com completude)
- [ ] Atualizar `contratos/show.blade.php` (aba Documentos): exibir completude, checklist obrigatório, lista agrupada por tipo com versões, botão download, botão substituir, modal upload
- [ ] Atualizar wizard step 6 (contratos/create.blade.php): zona de upload com seleção de tipo, feedback de completude
- [ ] `documentos/dashboard.blade.php`: 4 indicadores de completude + ranking secretarias + tabela de pendências

**Testes:**
- [ ] DocumentoFactory (novo) — para testes
- [ ] DocumentoServiceTest: upload, versionamento automático, cálculo de completude, nomes padronizados, log de acesso
- [ ] DocumentoTest: fluxo completo upload → completude → score → log
- [ ] Teste de imutabilidade do log_acesso_documentos
- [ ] Teste de autorização por perfil (DocumentoPolicy)
- [ ] DocumentoRelatorioTest: relatório TCE (geração PDF)

### Módulo: Painel de Risco Administrativo (Módulo 6 — Grande Diferencial Estratégico)

**Schema e Enums:**
- [ ] Novo enum: CategoriaRisco (vencimento, financeiro, documental, juridico, operacional)
- [ ] Atualizar RiscoService: expandir critérios do score com 5 categorias de risco (RN-136 a RN-142)
- [ ] Resolver sobreposição de critério `sem_documento` existente com critérios documentais granulares (RN-139, ADR-038)

**Service e Controller:**
- [ ] PainelRiscoService: método calcularIndicadores() — retorna os 5 indicadores do topo (RN-144)
- [ ] PainelRiscoService: método rankingRisco() — retorna tabela ordenada por score DESC com categorias (RN-146, RN-147)
- [ ] PainelRiscoService: método mapaRiscoPorSecretaria() — retorna total contratos e críticos por secretaria (RN-148, RN-149)
- [ ] PainelRiscoService: método gerarRelatorioRiscoTCE() — gera PDF com lista monitorada, justificativas de risco, plano de ação, histórico de alertas (RN-150 a RN-152)
- [ ] PainelRiscoController: index() — carrega painel de risco com cache Redis
- [ ] PainelRiscoController: exportarRelatorioTCE() — dispara geração e download do PDF
- [ ] PainelRiscoResource: dados do ranking de risco

**Integração com motor de monitoramento:**
- [ ] Atualizar VerificarVencimentosCommand para gerar alertas preventivos com mensagens contextualizadas (RN-153, RN-154)
- [ ] Integrar dados do Painel de Risco ao AgregarDashboardCommand (cron noturno para pré-calcular indicadores)

**Views:**
- [ ] `painel-risco/index.blade.php`: 5 cards indicadores com semáforo + ranking de risco (tabela) + mapa por secretaria + botão "Exportar Relatório TCE"
- [ ] Atualizar menu lateral (sidebar): adicionar item "Painel de Risco" com ícone de alerta/shield
- [ ] Atualizar Dashboard Executivo Bloco 2: adicionar link "Ver detalhes" → `/painel-risco` (ADR-039)

**Cache:**
- [ ] Cache Redis para painel de risco (chave separada `painel_risco`, TTL 24h + invalidação com agregação noturna)

**Testes:**
- [ ] PainelRiscoServiceTest: indicadores (5 cards), ranking, mapa secretaria, relatório PDF
- [ ] PainelRiscoTest: fluxo completo (acesso, filtros, exportação PDF)
- [ ] Testar score de risco expandido com critérios de 5 categorias (RN-136 a RN-142)
- [ ] Testar categorias múltiplas de risco simultâneas por contrato (RN-147)
- [ ] Testar performance do painel (<2 segundos com cache Redis)

### Módulo: Perfis de Usuário (RBAC — Módulo 7)

**Schema e Models:**
- [x] Migration: criar tabela `roles` (nome, descricao, is_padrao, is_ativo) *(IMP-014)*
- [x] Migration: criar tabela `permissions` (nome, descricao, grupo) *(IMP-015)*
- [x] Migration: criar tabela `role_permissions` (role_id, permission_id — pivot) *(IMP-015)*
- [x] Migration: criar tabela `user_permissions` (user_id, permission_id, expires_at, concedido_por) *(IMP-015)*
- [x] Migration: criar tabela `user_secretarias` (user_id, secretaria_id — pivot) *(IMP-015)*
- [ ] Migration: criar tabela `workflow_aprovacoes` (aprovavel_type/id, etapa, etapa_ordem, role_responsavel_id, user_id, status, parecer) — *movido para Fase 3c*
- [x] Migration: alterar tabela `users` (adicionar FK constraint `role_id` → roles com nullOnDelete) *(IMP-014)*
- [ ] Novo enum: StatusAprovacao (pendente, aprovado, reprovado) — *movido para Fase 3c*
- [ ] Novo enum: EtapaWorkflow (solicitacao, aprovacao_secretario, parecer_juridico, validacao_controladoria, homologacao) — *movido para Fase 3c*
- [x] Model: Role ($fillable, hasMany User, belongsToMany Permission) *(IMP-014 + IMP-015)*
- [x] Model: Permission ($fillable, belongsToMany Role, belongsToMany User) *(IMP-015)*
- [ ] Model: WorkflowAprovacao ($fillable, morphTo aprovavel, belongsTo Role, belongsTo User) — *movido para Fase 3c*
- [x] Atualizar Model User (belongsTo Role, belongsToMany Secretaria/Permission, hasPermission(), hasRole(), isPerfilEstrategico()) *(IMP-014 + IMP-015)*
- [x] Seeder: RoleSeeder (8 perfis padrao com is_padrao=true, integrado em TenantService e TenantCreateCommand) *(IMP-014)*
- [x] Seeder: PermissionSeeder (36 permissões granulares em 13 grupos) *(IMP-015)*
- [x] Seeder: RolePermissionSeeder (associação padrão role ↔ permissions, matriz 8 perfis) *(IMP-015)*
- [x] Índices: roles.nome (unique), permissions.nome (unique), user_secretarias (user_id + secretaria_id primary) *(IMP-014 + IMP-015)*

**Middleware e Autorização:**
- [x] Middleware: EnsureUserHasPermission (route parameter `permission:recurso.acao`) *(IMP-015)*
- [ ] Atualizar todas as Policies para verificar role + permission + secretaria
- [x] Helper $user->hasPermission('recurso.acao') no Model User (verificacao real-time expires_at) *(IMP-015)*
- [ ] Scope global por secretaria (Eloquent Global Scope para queries filtradas — RN-326)

**Service e Controller:**
- [x] PermissaoService: verificação, atribuição, revogação, sincronização *(IMP-015)*
- [ ] WorkflowService: criação de fluxo, avanço de etapas, reprovação, notificações — *movido para Fase 3c*
- [x] RolesController: CRUD de perfis (index, create, store, edit, update, destroy) *(IMP-015)*
- [x] PermissoesController: gestão de permissões por role (index, update) *(IMP-015)*
- [x] UsersController: CRUD com atribuição de role + secretarias *(IMP-015)*
- [x] VerificarPermissoesExpiradasCommand (`permissoes:verificar-expiradas`) *(IMP-015)*
- [ ] Scheduler: registrar VerificarPermissoesExpiradasCommand no schedule()

**Views:**
- [x] roles/index.blade.php, create.blade.php, edit.blade.php *(IMP-015)*
- [x] permissoes/index.blade.php (gestão por role — cards de checkboxes agrupados por grupo) *(IMP-015)*
- [x] users/index.blade.php, create.blade.php, edit.blade.php (com role + secretarias) *(IMP-015)*
- [x] Sidebar dinâmico (menu condicional por permissão do usuário logado) *(IMP-015)*

**Testes:**
- [ ] PermissaoServiceTest (verificação por role, por user, expiração automática)
- [ ] WorkflowServiceTest (criação de fluxo, avanço, reprovação, notificação) — *movido para Fase 3c*
- [ ] PerfilUsuarioTest (acesso por perfil a recursos protegidos)
- [ ] PermissaoTemporariaTest (concessão com expires_at, revogação automática por job)
- [ ] WorkflowAprovacaoTest (fluxo completo de aditivo com 5 etapas) — *movido para Fase 3c*
- [ ] Testar perfis padrão não deletáveis (is_padrao = true)
- [ ] Testar escopo por secretaria (queries filtradas automaticamente)

### Módulo: Relatórios
- [ ] Relatórios gerenciais (exportação PDF/Excel)

### Geral
- [ ] Testes unitários (Services: ContratoService, AlertaService, NotificacaoService, RiscoService, AuditoriaService)
- [ ] Testes unitários (validação de CNPJ, cálculo de score de risco)
- [ ] Testes de integração (fluxos CRUD, cadastro multi-etapa)
- [ ] Testes de imutabilidade do audit trail
- [ ] Testes do motor de monitoramento (VerificarVencimentosCommand por faixa de dias)
- [ ] Testes de prioridade automática e não-duplicação de alertas
- [ ] Testes de resolução automática de alertas (via aditivo)
- [ ] Testes do EnviarNotificacaoAlertaJob (retry, backoff)
- [ ] Testes de bloqueio preventivo (aditivo retroativo sem justificativa)
