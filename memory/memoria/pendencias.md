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
- [x] MFA obrigatório para AdminUser (TOTP) *(IMP-030)*

### Módulo: Segurança Expandida
- [x] Configurar hashing driver Argon2id (`config/hashing.php`) — ADR-044 *(IMP-012)*
- [x] Migration banco tenant: tabela `login_logs` (user_id, ip_address, user_agent, success, created_at) — ADR-048 *(IMP-013)*
- [x] Model: LoginLog (append-only, sem update/delete) *(IMP-013)*
- [x] Implementar MFA via TOTP (obrigatório admin_geral/controladoria, opcional demais perfis) — ADR-045/ADR-055 *(IMP-030)*
- [x] Implementar bloqueio de login após 5 tentativas com cooldown 15min — ADR-046 *(IMP-013)*
- [x] Implementar expiração de sessão configurável (SESSION_LIFETIME) — ADR-049 *(IMP-013)*
- [x] Middleware ForceHttps para produção *(IMP-037)*
- [x] Adicionar campo `hash_integridade` ao Model Documento — ADR-047 *(IMP-016 campo na migration, IMP-017 calculo no upload)*
- [x] Implementar cálculo de hash SHA-256 no upload de documento (DocumentoService) — RN-220 *(IMP-017)*
- [x] Implementar verificação de integridade (recalcular hash e comparar) — RN-221 *(IMP-034)*
- [x] Implementar relatório de logs exportável (PDF/CSV) — RN-222 *(IMP-029/IMP-033)*
- [x] Módulo Auditoria dedicado (trilha navegável + exportação PDF/CSV) *(IMP-033)*
- [x] Triggers MySQL de imutabilidade em tabelas append-only *(IMP-035)*
- [x] Verificação automática de integridade (Command + Job + Schedule semanal) *(IMP-034)*
- [x] Bloqueio de download quando integridade comprometida *(IMP-034)*
- [x] Criar seeders iniciais (AdminUserSeeder IMP-012, RoleSeeder IMP-014) — *SecretariaSeeder opcional para futuro*

### Módulo: Contratos (Cadastro Inteligente)
- [x] Migration da tabela contratos (campos expandidos: modalidade, score_risco, percentual_executado, etc.) *(IMP-016)*
- [x] Migration da tabela fiscais (com histórico de trocas) *(IMP-016)*
- [x] Migration da tabela aditivos *(IMP-020 — Fase 3c)*
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
- [x] Atualizar enum TipoAditivo (+ reequilibrio, alteracao_clausula, misto — 4→7 valores) *(IMP-020)*
- [x] Migration da tabela aditivos (campos: numero_sequencial, data_inicio_vigencia, valor_acrescimo, valor_supressao, percentual_acumulado, fundamentacao_legal, justificativa_tecnica, justificativa_excesso_limite, parecer_juridico_obrigatorio, campos de reequilíbrio, SoftDeletes) *(IMP-020)*
- [x] Migration da tabela configuracoes_limite_aditivo (tipo_contrato, percentual_limite, is_bloqueante, is_ativo) *(IMP-020)*
- [x] Migration da tabela workflow_aprovacoes (polimórfica, etapa, status, parecer, decided_at) *(IMP-020)*
- [x] Seeder: ConfiguracaoLimiteAditivoSeeder (servico=25%, obra=50%, compra=25%, locacao=25%) *(IMP-020)*
- [x] Seeder: AditivoSeeder (4 aditivos exemplo com workflows variados) *(IMP-020)*
- [x] Model: ConfiguracaoLimiteAditivo *(IMP-020)*
- [x] Model: Aditivo ($fillable, SoftDeletes, casts enums/dates/decimals, relationships, accessors workflow) *(IMP-020)*
- [x] Model: WorkflowAprovacao (append-only, booted imutabilidade, decided_at) *(IMP-020)*
- [x] StoreAditivoRequest (validações condicionais por tipo, limites legais, nova_data_fim after data_fim atual RN-010) *(IMP-020)*
- [x] AditivoService (geração numero_sequencial, cálculo percentual_acumulado, verificação limite legal, atualização contrato pai, cancelar, processarReequilibrio) *(IMP-020)*
- [x] WorkflowService (criarFluxo 5 etapas, aprovar sequencial, reprovar com parecer, decided_at) *(IMP-020)*
- [x] RiscoService expandido (novos critérios: RN-106, RN-107, RN-108) *(IMP-020)*
- [ ] Atualizar AditivoResource (novos campos) — *pendente para API*
- [x] Formulário de criação de aditivo com campos condicionais por tipo + Select2 + máscaras *(IMP-020)*
- [x] Exibição em tempo real de percentual acumulado e limite legal no formulário *(IMP-020)*
- [x] Alerta visual de limite legal ultrapassado (is_bloqueante e modo alerta) *(IMP-020)*
- [x] Página de detalhes/timeline do aditivo (aditivos/show.blade.php) com workflow stepper *(IMP-020)*
- [x] Dashboard de aditivos (indicadores anuais — RN-109 a RN-114) *(IMP-020)*
- [x] Cancelamento de aditivo (RN-116 — apenas admin) com recálculo do contrato *(IMP-020)*
- [x] AditivoFactory para testes *(IMP-028)*
- [x] Testes: AditivoServiceTest (limites legais, percentual, reequilíbrio) *(IMP-028)*
- [x] Testes: AditivoTest (fluxo completo Feature) *(IMP-041)*
- [x] Testes: LimiteLegalAditivoTest (bloqueio e alerta) *(IMP-041 — integrado em AditivosControllerTest)*
- [x] Índices em aditivos (contrato_id, composto contrato_id+data_assinatura) *(IMP-020)*

### Módulo: Alertas (Motor de Monitoramento)
- [x] Migration da tabela alertas (expandida: tipo_evento, dias_antecedencia_config, data_disparo, tentativas_envio) *(IMP-021)*
- [x] Migration da tabela configuracoes_alerta (dias_antecedencia, prioridade, is_ativo) *(IMP-021)*
- [x] Migration da tabela log_notificacoes (canal, destinatario, sucesso, resposta_gateway) *(IMP-021)*
- [x] Índice em contratos.data_fim + índice composto em alertas *(IMP-021)*
- [x] Enums: StatusAlerta (add enviado), CanalNotificacao, TipoEventoAlerta, PrioridadeAlerta *(IMP-021)*
- [x] Model: Alerta, ConfiguracaoAlerta, LogNotificacao *(IMP-021)*
- [x] Command: VerificarVencimentosCommand (alertas:verificar-vencimentos) *(IMP-022)*
- [x] Job: ProcessarAlertaJob (retry exponencial, max 3 tentativas, backoff [60s,300s,900s]) *(IMP-023)*
- [x] Notification: AlertaVencimentoNotification (canais: mail + database) *(IMP-023)*
- [x] Service: AlertaService (geração, resolução, prioridade automática, dedup) *(IMP-021)*
- [x] Service: NotificacaoService (orquestração de envio por canal) *(IMP-023)*
- [x] Seeder: ConfiguracaoAlertaSeeder (6 prazos: 120, 90, 60, 30, 15, 7 dias) *(IMP-021)*
- [x] Scheduler: registrar VerificarVencimentosCommand no schedule() *(IMP-024)*
- [x] Queue: configurar Redis como driver de filas *(IMP-023)*
- [x] Dashboard de alertas (indicadores: vencendo 120d/60d/30d, vencidos, secretarias com risco) *(IMP-024)*
- [x] Listagem de alertas com filtros (secretaria, criticidade, tipo contrato, faixa valor) *(IMP-024)*
- [x] Tela de configuração de prazos de alerta (admin) *(IMP-024)*
- [x] Notificação interna no sistema (sino/badge no navbar) *(IMP-024)*
- [x] Resolução automática de alertas ao registrar aditivo de prazo *(IMP-024)*
- [x] Contrato essencial → prioridade elevada nos alertas *(IMP-021)*
- [x] Email institucional: template de email para alertas de vencimento *(IMP-023)*
- [x] Bloqueio preventivo: contrato vencido → IRREGULAR, aditivo retroativo exige justificativa *(IMP-039)*
- [x] Relatório de efetividade mensal (contratos regularizados vs vencidos) *(IMP-040)*

### Módulo: Dashboard Executivo (Painel Estratégico)
- [x] Migration da tabela dashboard_agregados (dados pré-calculados) *(IMP-025)*
- [x] Model: DashboardAgregado *(IMP-025)*
- [x] Service: DashboardService (agregação, consulta, score de gestão) *(IMP-025)*
- [x] Command: AgregarDashboardCommand (dashboard:agregar — cron noturno) *(IMP-025)*
- [x] Scheduler: registrar AgregarDashboardCommand no schedule() *(IMP-025)*
- [x] Cache Redis para dados do dashboard (TTL 24h + invalidação manual) *(IMP-025)*
- [x] Bloco 1: Visão Geral Financeira (5 cards de indicadores) *(IMP-025)*
- [x] Bloco 2: Mapa de Risco Contratual (donut chart com ApexCharts) *(IMP-025)*
- [x] Bloco 3: Vencimentos por Janela de Tempo (gráfico/tabela 5 faixas) *(IMP-025)*
- [x] Bloco 4: Distribuição por Secretaria (ranking com tabela) *(IMP-025)*
- [x] Bloco 5: Contratos Essenciais (painel especial de alerta) *(IMP-025)*
- [x] Filtros inteligentes (secretaria, faixa valor, risco, tipo, modalidade, fonte recurso) *(IMP-025)*
- [x] Score de Gestão Contratual (nota 0-100 com classificação) *(IMP-025)*
- [x] Tendência Mensal (mini BI — últimos 12 meses) *(IMP-025)*
- [x] Ranking de Fornecedores (top 10 por volume, contratos, aditivos) *(IMP-025)*
- [x] Visão do Controlador (irregularidades, log recente, aditivos acima de limite) *(IMP-025)*
- [x] Botão "Atualizar Dados" (atualização manual sob demanda) *(IMP-025)*
- [x] JS do dashboard (assets/js/dashboard-charts.js com ApexCharts) *(IMP-025)*
- [x] Testes unitários: DashboardServiceTest *(IMP-028)*
- [x] Testes de feature: DashboardController (acesso, filtros, atualização) *(IMP-041)*
- [x] Testes de performance: dashboard carrega em <2 segundos *(IMP-043)*

### Módulo: Documentos (Central de Documentos — Módulo 5)

**Schema e Models:**
- [x] Atualizar enum TipoDocumentoContratual (7 → 12 valores) *(ja estava com 12 valores desde IMP-016)*
- [x] Novo enum: StatusCompletudeDocumental (completo, parcial, incompleto) *(IMP-017)*
- [x] Novo enum: AcaoLogDocumento (upload, download, substituicao, exclusao, visualizacao) *(IMP-017)*
- [x] Migration: alterar tabela `documentos` (adicionar: nome_arquivo, descricao, versao, is_versao_atual; renomear path→caminho, tamanho_bytes→tamanho; hash_integridade nullable) *(IMP-017)*
- [x] Migration: criar tabela `log_acesso_documentos` (documento_id, user_id, acao, ip_address, created_at — append-only) *(IMP-017)*
- [x] Índices em `documentos`: composto (documentable_type + documentable_id) *(IMP-016)*, tipo_documento, is_versao_atual *(IMP-017)*
- [x] Índices em `log_acesso_documentos`: documento_id, user_id *(IMP-017)*
- [x] Model: atualizar Documento ($fillable, SoftDeletes, is_versao_atual, nome_original, nome_arquivo, scopeVersaoAtual, relacionamento hasMany LogAcessoDocumento) *(IMP-017)*
- [x] Model: LogAcessoDocumento (novo — $fillable, belongsTo Documento e User, sem SoftDeletes, sem updated_at, append-only) *(IMP-017)*

**Controller e Service:**
- [x] DocumentoService: método upload() com geração de nome padronizado (RN-121), versionamento automático (RN-120), registro em storage por contrato/tipo (ADR-033), hash SHA-256 (ADR-047), validação magic bytes PDF, log de acesso (RN-122) *(IMP-017)*
- [x] DocumentoService: método download() com registro de log de acesso *(IMP-017)*
- [x] DocumentoService: método calcularCompletude(Contrato) — retorna StatusCompletudeDocumental (RN-128) *(IMP-017 — via accessor no Model Contrato)*
- [x] DocumentoService: método verificarChecklist(Contrato) — retorna array de tipos pendentes do checklist (RN-129) *(IMP-017)*
- [x] DocumentoService: método gerarIndicadoresDashboard() — retorna os 4 indicadores (RN-132) *(IMP-017)*
- [x] DocumentosController: upload, download autenticado, soft delete *(IMP-017)*
- [x] DocumentosController: Central de Documentos com busca inteligente e filtros combinados (RN-131) *(IMP-017)*

**Autorização e Validação:**
- [ ] Novo: DocumentoPolicy (view, create, download, delete) — *autorização feita via middleware permission + hasPermission no controller; Policy formal pendente*
- [x] StoreDocumentoRequest: validação (mimes:pdf, max:20480 KB, tipo_documento Enum) *(IMP-017)*
- [ ] DocumentoResource: atualizar (incluir versao, is_versao_atual, nome_original, nome_arquivo, tipo_documento label) — *pendente para API*

**Observer:**
- [ ] DocumentoObserver (novo): ao criar/excluir documento → recalcular completude do contrato (ADR-036) — *completude calculada via accessor; Observer opcional para cache*

**Relatório:**
- [ ] RelatorioService: método gerarRelatorioTCEContrato(Contrato) — lista documentos com tipo, nome, versão, data upload, responsável, status (RN-133). Exportar em PDF

**Views:**
- [x] `documentos/index.blade.php`: Central de Documentos standalone (4 cards indicadores + busca + filtros + listagem com completude) *(IMP-017)*
- [x] Atualizar `contratos/show.blade.php` (aba Documentos): exibir completude, checklist obrigatório, lista agrupada por tipo com versões, botão download, botão excluir, modal upload *(IMP-017)*
- [ ] Atualizar wizard step 6 (contratos/create.blade.php): zona de upload com seleção de tipo, feedback de completude — *upload disponível após salvar na tela de detalhes*
- [ ] `documentos/dashboard.blade.php`: dashboard dedicado de documentos — *indicadores integrados na Central de Documentos*

**Testes:**
- [x] DocumentoFactory (novo) — para testes *(IMP-028)*
- [x] DocumentoServiceTest: upload, versionamento automático, cálculo de completude, nomes padronizados, log de acesso *(IMP-028)*
- [x] DocumentoTest: fluxo completo Feature upload → completude → score → log *(IMP-041)*
- [x] Teste de imutabilidade do log_acesso_documentos *(IMP-028)*
- [ ] Teste de autorização por perfil (DocumentoPolicy)
- [x] DocumentosRelatorioTest: relatório documentos contrato (PDF, permissão, vazio) *(IMP-043)*
- [ ] DocumentoRelatorioTest: relatório TCE completo (geração PDF) — *pendente: metodo gerarRelatorioTCEContrato() nao implementado*

### Módulo: Painel de Risco Administrativo (Módulo 6 — Grande Diferencial Estratégico)

**Schema e Enums:**
- [x] RiscoService: expandir critérios do score com 5 categorias de risco (RN-136 a RN-142) *(IMP-026)*

**Service e Controller:**
- [x] PainelRiscoService: método indicadores() — retorna os 5 indicadores do topo (RN-144) *(IMP-026)*
- [x] PainelRiscoService: método rankingRisco() — retorna tabela ordenada por score DESC com categorias (RN-146, RN-147) *(IMP-026)*
- [x] PainelRiscoService: método mapaRiscoPorSecretaria() — retorna total contratos e críticos por secretaria (RN-148, RN-149) *(IMP-026)*
- [x] PainelRiscoService: método dadosRelatorioTCE() — gera dados relatório TCE (RN-150 a RN-152) *(IMP-026)*
- [x] PainelRiscoController: index() — carrega painel de risco *(IMP-026)*
- [x] PainelRiscoController: exportarRelatorioTCE() — dispara geração e download do PDF *(IMP-026)*

**Integração com motor de monitoramento:**
- [x] VerificarVencimentosCommand com alertas preventivos contextualizados *(IMP-022)*
- [x] AgregarDashboardCommand integrado com dados do Painel de Risco *(IMP-025)*

**Views:**
- [x] `painel-risco/index.blade.php`: 5 cards indicadores + ranking de risco + mapa por secretaria + botão "Exportar Relatório TCE" *(IMP-026)*
- [x] `painel-risco/relatorio-tce.blade.php`: relatório PDF para TCE *(IMP-026)*
- [x] Sidebar: item "Painel de Risco" com ícone *(IMP-026)*

**Permissões:**
- [x] painel-risco.visualizar + painel-risco.exportar no PermissionSeeder *(IMP-026)*

**Testes:**
- [x] PainelRiscoServiceTest: indicadores, ranking, mapa secretaria *(IMP-028)*
- [x] PainelRiscoTest: fluxo completo Feature (acesso, exportação PDF) *(IMP-041)*
- [x] Testar performance do painel (<2 segundos) *(IMP-043)*

### Módulo: Perfis de Usuário (RBAC — Módulo 7)

**Schema e Models:**
- [x] Migration: criar tabela `roles` (nome, descricao, is_padrao, is_ativo) *(IMP-014)*
- [x] Migration: criar tabela `permissions` (nome, descricao, grupo) *(IMP-015)*
- [x] Migration: criar tabela `role_permissions` (role_id, permission_id — pivot) *(IMP-015)*
- [x] Migration: criar tabela `user_permissions` (user_id, permission_id, expires_at, concedido_por) *(IMP-015)*
- [x] Migration: criar tabela `user_secretarias` (user_id, secretaria_id — pivot) *(IMP-015)*
- [x] Migration: criar tabela `workflow_aprovacoes` (aprovavel_type/id, etapa, etapa_ordem, role_responsavel_id, user_id, status, parecer, decided_at) *(IMP-020 — Fase 3c)*
- [x] Migration: alterar tabela `users` (adicionar FK constraint `role_id` → roles com nullOnDelete) *(IMP-014)*
- [x] Novo enum: StatusAprovacao (pendente, aprovado, reprovado) *(IMP-020 — Fase 3c)*
- [x] Novo enum: EtapaWorkflow (solicitacao, aprovacao_secretario, parecer_juridico, validacao_controladoria, homologacao) *(IMP-020 — Fase 3c)*
- [x] Model: Role ($fillable, hasMany User, belongsToMany Permission) *(IMP-014 + IMP-015)*
- [x] Model: Permission ($fillable, belongsToMany Role, belongsToMany User) *(IMP-015)*
- [x] Model: WorkflowAprovacao ($fillable, morphTo aprovavel, belongsTo Role, belongsTo User, booted imutabilidade) *(IMP-020 — Fase 3c)*
- [x] Atualizar Model User (belongsTo Role, belongsToMany Secretaria/Permission, hasPermission(), hasRole(), isPerfilEstrategico()) *(IMP-014 + IMP-015)*
- [x] Seeder: RoleSeeder (8 perfis padrao com is_padrao=true, integrado em TenantService e TenantCreateCommand) *(IMP-014)*
- [x] Seeder: PermissionSeeder (36 permissões granulares em 13 grupos) *(IMP-015)*
- [x] Seeder: RolePermissionSeeder (associação padrão role ↔ permissions, matriz 8 perfis) *(IMP-015)*
- [x] Índices: roles.nome (unique), permissions.nome (unique), user_secretarias (user_id + secretaria_id primary) *(IMP-014 + IMP-015)*

**Middleware e Autorização:**
- [x] Middleware: EnsureUserHasPermission (route parameter `permission:recurso.acao`) *(IMP-015)*
- [ ] Atualizar todas as Policies para verificar role + permission + secretaria
- [x] Helper $user->hasPermission('recurso.acao') no Model User (verificacao real-time expires_at) *(IMP-015)*
- [x] Scope global por secretaria (Eloquent Global Scope para queries filtradas — RN-326) *(IMP-038)*

**Service e Controller:**
- [x] PermissaoService: verificação, atribuição, revogação, sincronização *(IMP-015)*
- [x] WorkflowService: criação de fluxo 5 etapas, avanço sequencial, reprovação com parecer obrigatório *(IMP-020 — Fase 3c)*
- [x] RolesController: CRUD de perfis (index, create, store, edit, update, destroy) *(IMP-015)*
- [x] PermissoesController: gestão de permissões por role (index, update) *(IMP-015)*
- [x] UsersController: CRUD com atribuição de role + secretarias *(IMP-015)*
- [x] VerificarPermissoesExpiradasCommand (`permissoes:verificar-expiradas`) *(IMP-015)*
- [x] Scheduler: registrar VerificarPermissoesExpiradasCommand no schedule() *(IMP-024)*

**Views:**
- [x] roles/index.blade.php, create.blade.php, edit.blade.php *(IMP-015)*
- [x] permissoes/index.blade.php (gestão por role — cards de checkboxes agrupados por grupo) *(IMP-015)*
- [x] users/index.blade.php, create.blade.php, edit.blade.php (com role + secretarias) *(IMP-015)*
- [x] Sidebar dinâmico (menu condicional por permissão do usuário logado) *(IMP-015)*

**Testes:**
- [x] PermissaoServiceTest (verificação por role, por user, expiração automática) *(IMP-028)*
- [x] WorkflowServiceTest (criação de fluxo, avanço, reprovação) *(IMP-028)*
- [x] PerfilUsuarioTest Feature (acesso por perfil a recursos protegidos) *(IMP-042)*
- [x] PermissaoTemporariaTest (concessão com expires_at, revogação automática por job) *(IMP-042)*
- [x] WorkflowAprovacaoTest (imutabilidade) *(IMP-028)*
- [x] Testar perfis padrão não deletáveis (is_padrao = true) *(IMP-042)*
- [x] Scope global por secretaria (Eloquent Global Scope para queries filtradas — RN-326) *(IMP-038)*

### Módulo: Relatórios
- [x] Relatórios gerenciais (exportação PDF/Excel) *(IMP-029)*
- [x] RelatorioService com exportação CSV/PDF (auditoria, conformidade documental) *(IMP-029)*
- [x] Excel exports para contratos, alertas, fornecedores *(IMP-029)*
- [x] RateLimiter (10/min) para exports *(IMP-029)*
- [x] 9 rotas de relatórios + sidebar link *(IMP-029)*

### Geral
- [x] Testes unitários Services: ContratoService, AlertaService, RiscoService, AuditoriaService, FiscalService, ExecucaoFinanceiraService, DocumentoService, PermissaoService, WorkflowService, FornecedorService, DashboardService, PainelRiscoService, MfaService *(IMP-027/028/030)*
- [x] Testes unitários validação de CNPJ + CPF *(IMP-028)*
- [x] Testes unitários Enums: StatusContrato, TipoAditivo, ModalidadeContratacao, EtapaWorkflow, PrioridadeAlerta, StatusCompletudeDocumental *(IMP-028)*
- [x] Testes unitários Models: Contrato, Aditivo, Alerta, User, WorkflowAprovacao, HistoricoAlteracao, LogAcessoDocumento, LogNotificacao *(IMP-028)*
- [x] Testes de imutabilidade do audit trail (HistoricoAlteracaoTest, LogAcessoDocumentoTest, LogNotificacaoTest, WorkflowAprovacaoTest) *(IMP-028)*
- [x] Testes de prioridade automática e não-duplicação de alertas (AlertaServiceTest) *(IMP-028)*
- [x] Testes MFA: 38 testes (MfaServiceTest + MfaMiddlewareTest + UserMfaTest) *(IMP-030)*
- [x] Testes Feature: Controllers CRUD (contratos, fornecedores, secretarias, servidores, usuarios, aditivos, documentos, alertas) *(IMP-031 base + IMP-041 expansão)*
- [x] Testes Feature: DashboardController + PainelRiscoController *(IMP-041)*
- [x] Testes de integração: fluxos end-to-end (contrato lifecycle, aditivo workflow, alerta flow) *(IMP-043)*
- [x] Testes do ProcessarAlertaJob (retry, backoff) *(IMP-041)*
- [x] Testes de performance: dashboard/painel carrega em <2 segundos *(IMP-043)*
