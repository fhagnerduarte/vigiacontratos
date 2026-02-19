# Memória — Pendências por Módulo

> Extraído de `banco-de-memoria.md`. Carregar ao planejar trabalho ou verificar o que falta implementar.
> Atualizado conforme pendências são concluídas ou criadas.

---

### Módulo: Infraestrutura
- [ ] Criar projeto Laravel 12 via Sail
- [ ] Configurar Docker (MySQL 8 + Redis)
- [ ] Integrar template WowDash (assets, layout, componentes)
- [ ] Configurar autenticação (login, logout, forgot password)
- [ ] Criar migrations base (users, secretarias, fornecedores)
- [ ] Configurar S3-compatible storage (MinIO para dev, AWS S3 para prod) — ADR-043

### Módulo: Multi-Tenant (Database-per-Tenant)
- [ ] Migration banco master: tabela `tenants` (nome, slug, database_name, database_host, is_ativo, plano)
- [ ] Migration banco master: tabela `tenant_users` (user_id, tenant_id, role)
- [ ] Model: Tenant (banco master)
- [ ] Middleware: SetTenantConnection (resolve tenant e configura connection MySQL)
- [ ] Comando artisan: `tenant:create` (provisionar novo tenant: criar banco, aplicar migrations, seeder admin)
- [ ] Comando artisan: `tenant:migrate` (aplicar migrations pendentes em todos os tenants ativos)
- [ ] Configuração dinâmica de connection MySQL em runtime
- [ ] Configuração de storage isolado por tenant (prefixo S3 por slug)
- [ ] Configuração de cache Redis com prefixo por tenant

### Módulo: Segurança Expandida
- [ ] Configurar hashing driver Argon2id (`config/hashing.php`) — ADR-044
- [ ] Migration banco tenant: tabela `login_logs` (user_id, ip_address, user_agent, success, created_at) — ADR-048
- [ ] Model: LoginLog (append-only, sem update/delete)
- [ ] Implementar MFA opcional via TOTP para admin/gestor — ADR-045
- [ ] Implementar bloqueio de login após 5 tentativas com cooldown 15min — ADR-046
- [ ] Implementar expiração de sessão configurável (SESSION_LIFETIME) — ADR-049
- [ ] Middleware ForceHttps para produção
- [ ] Adicionar campo `hash_integridade` ao Model Documento — ADR-047
- [ ] Implementar cálculo de hash SHA-256 no upload de documento (DocumentoService) — RN-220
- [ ] Implementar verificação de integridade (recalcular hash e comparar) — RN-221
- [ ] Implementar relatório de logs exportável (PDF/CSV) — RN-222
- [ ] Criar seeders iniciais (admin user, secretarias)

### Módulo: Contratos (Cadastro Inteligente)
- [ ] Migration da tabela contratos (campos expandidos: modalidade, score_risco, percentual_executado, etc.)
- [ ] Migration da tabela fiscais (com histórico de trocas)
- [ ] Migration da tabela aditivos
- [ ] Migration da tabela documentos (com tipo_documento e versao)
- [ ] Migration da tabela execucoes_financeiras
- [ ] Migration da tabela historico_alteracoes (polimórfica, imutável)
- [ ] Enums: ModalidadeContratacao, TipoPagamento, CategoriaContrato, CategoriaServico, NivelRisco, TipoDocumentoContratual
- [ ] Models: Fiscal, ExecucaoFinanceira, HistoricoAlteracao
- [ ] Services: RiscoService (cálculo score), AuditoriaService (audit trail), FiscalService, ExecucaoFinanceiraService
- [ ] Observer: ContratoObserver (audit trail + recálculo de score)
- [ ] Formulário multi-etapa (wizard) para cadastro de contrato
- [ ] Tela de detalhes do contrato com abas (dados, fiscal, financeiro, documentos, auditoria)
- [ ] CRUD de fiscais (com troca e histórico)
- [ ] Registro de execuções financeiras
- [ ] Upload múltiplo de documentos com classificação por tipo
- [ ] Versionamento de documentos
- [ ] Validação de CNPJ (dígito verificador)
- [ ] Validações condicionais por modalidade (dispensa → fundamento legal, obra → resp. técnico)
- [ ] Cálculo automático de score de risco
- [ ] Cálculo automático de percentual executado
- [ ] Filtros inteligentes na listagem (secretaria, vencimento, risco, fornecedor, número)

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
- [ ] Novo: DocumentoPolicy (view, create, download, delete — por perfil: admin, gestor, consulta — RN-130)
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
- [ ] Migration: criar tabela `roles` (nome, descricao, is_padrao, is_ativo)
- [ ] Migration: criar tabela `permissions` (nome, descricao, grupo)
- [ ] Migration: criar tabela `role_permissions` (role_id, permission_id — pivot)
- [ ] Migration: criar tabela `user_permissions` (user_id, permission_id, expires_at, concedido_por)
- [ ] Migration: criar tabela `user_secretarias` (user_id, secretaria_id — pivot)
- [ ] Migration: criar tabela `workflow_aprovacoes` (aprovavel_type/id, etapa, etapa_ordem, role_responsavel_id, user_id, status, parecer)
- [ ] Migration: alterar tabela `users` (remover coluna `tipo`, adicionar `role_id` FK → roles)
- [ ] Novo enum: StatusAprovacao (pendente, aprovado, reprovado)
- [ ] Novo enum: EtapaWorkflow (solicitacao, aprovacao_secretario, parecer_juridico, validacao_controladoria, homologacao)
- [ ] Model: Role ($fillable, hasMany User, belongsToMany Permission)
- [ ] Model: Permission ($fillable, belongsToMany Role, belongsToMany User)
- [ ] Model: UserPermission ($fillable, belongsTo User, belongsTo Permission)
- [ ] Model: WorkflowAprovacao ($fillable, morphTo aprovavel, belongsTo Role, belongsTo User)
- [ ] Atualizar Model User (remover tipo, adicionar role_id, belongsTo Role, belongsToMany Secretaria, belongsToMany Permission, hasPermission())
- [ ] Seeder: RoleSeeder (8 perfis padrão com is_padrao=true)
- [ ] Seeder: PermissionSeeder (permissões granulares por grupo)
- [ ] Seeder: RolePermissionSeeder (associação padrão role ↔ permissions)
- [ ] Índices: roles.nome (unique), permissions.nome (unique), user_secretarias (user_id + secretaria_id unique), workflow_aprovacoes (aprovavel_type + aprovavel_id + etapa unique)

**Middleware e Autorização:**
- [ ] Middleware: EnsureUserHasPermission (substitui EnsureUserIsAdmin + EnsureUserIsGestor)
- [ ] Atualizar todas as Policies para verificar role + permission + secretaria
- [ ] Helper $user->hasPermission('recurso.acao') no Model User
- [ ] Scope global por secretaria (Eloquent Global Scope para queries filtradas — RN-326)

**Service e Controller:**
- [ ] PermissaoService: verificação, atribuição, revogação, verificação de expiração
- [ ] WorkflowService: criação de fluxo, avanço de etapas, reprovação, notificações
- [ ] RolesController: CRUD de perfis (index, create, store, edit, update)
- [ ] PermissoesController: gestão de permissões por role
- [ ] Atualizar UsersController: atribuição de role + secretarias + permissões individuais
- [ ] VerificarPermissoesExpiradasCommand (`permissoes:verificar-expiradas` — cron diário)
- [ ] Scheduler: registrar VerificarPermissoesExpiradasCommand no schedule()

**Views:**
- [ ] roles/index.blade.php, create.blade.php, edit.blade.php
- [ ] permissoes/index.blade.php (gestão por role — tabela de checkboxes por permissão)
- [ ] Atualizar users/create.blade.php e edit.blade.php (seleção de role + secretarias)
- [ ] Atualizar sidebar (menu dinâmico por permissão do usuário logado)

**Testes:**
- [ ] PermissaoServiceTest (verificação por role, por user, expiração automática)
- [ ] WorkflowServiceTest (criação de fluxo, avanço, reprovação, notificação)
- [ ] PerfilUsuarioTest (acesso por perfil a recursos protegidos)
- [ ] PermissaoTemporariaTest (concessão com expires_at, revogação automática por job)
- [ ] WorkflowAprovacaoTest (fluxo completo de aditivo com 5 etapas)
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
