# Regras — Estrutura de Diretórios

> Extraído de `banco-de-regras.md`. Carregar ao criar novos arquivos ou verificar onde colocar código.
> Mapa completo da estrutura de diretórios planejada do projeto.

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
