# Regras — Classes Esperadas e Enums

> Extraído de `banco-de-regras.md`. Carregar ao criar novos Controllers, Services, Jobs, Commands, Resources, Enums ou Policies.
> Lista completa de todas as classes planejadas por tipo.

---

## Classes Esperadas por Tipo

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
