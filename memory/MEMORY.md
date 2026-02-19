# vigiacontratos — Índice Rápido

## Status

| Campo | Valor |
|---|---|
| Tipo | Sistema de gestão contratual municipal (SaaS multi-tenant) |
| Modelo | SaaS com banco isolado por prefeitura (database-per-tenant) |
| Stack | Laravel 12, PHP 8.2+, MySQL 8, Redis, S3-compatible |
| Container | Docker / Laravel Sail |
| Fase Atual | Fase 0 — Setup Inicial |
| Cadeia de Fases | 0 → 1a → 1b → 1c → 2 → 3a → 3b → 3c → 4 → 5 → 6 |
| Última Atualização | 2026-02-18 |

---

## Módulos

| Módulo | Status | Observação |
|---|---|---|
| Multi-Tenant | Em Detalhamento | Banco isolado por prefeitura, banco master para gestão de tenants, middleware SetTenantConnection, storage e cache isolados por tenant |
| Dashboard Executivo | Em Detalhamento | Painel estratégico com 5 blocos (financeiro, risco, vencimentos, secretarias, essenciais), score de gestão 0-100, tendências, rankings, filtros inteligentes, agregação noturna, cache Redis |
| Contratos | Em Detalhamento | Cadastro inteligente multi-etapa, score de risco, fiscais, execução financeira, audit trail |
| Fornecedores | Planejado | Cadastro e gestão de empresas fornecedoras |
| Aditivos | Em Detalhamento | 7 tipos (prazo, valor, prazo_e_valor, supressao, reequilibrio, alteracao_clausula, misto), controle de limite legal 25%/50% configurável, percentual acumulado, score de risco integrado, numero sequencial automático, reequilíbrio econômico-financeiro, dashboard de aditivos, timeline de histórico |
| Alertas | Em Detalhamento | Motor de monitoramento diário, 6 prazos configuráveis, notificação multi-canal, dashboard de alertas, bloqueio preventivo |
| Documentos | Em Detalhamento | Central de Documentos: pasta digital por contrato, 12 tipos de documento, versionamento automático não-destrutivo, log de acesso e auditoria, completude documental (completo/parcial/incompleto), validações automáticas por evento contratual, busca inteligente, dashboard de indicadores, relatório para Tribunal de Contas, hash de integridade SHA-256, controle de acesso por perfil |
| Painel de Risco | Em Detalhamento | Grande diferencial: score expandido com 5 categorias (vencimento, financeiro, documental, jurídico, operacional), dashboard dedicado com ranking e semáforo, mapa de risco por secretaria, relatório automatizado para TCE, alertas preventivos inteligentes, cache Redis |
| Segurança | Em Detalhamento | Argon2id, MFA opcional (TOTP), lockout 5 tentativas, logs de login, sessão com expiração, HTTPS/TLS 1.2+, hash de integridade, LGPD |
| Perfis de Usuário (RBAC) | Em Detalhamento | 8 perfis dinâmicos (tabela roles), permissões granulares por recurso.ação (matriz 12 recursos × 8 perfis), escopo por secretaria, permissões temporárias com expires_at, workflow de aprovação 5 etapas, logs de auditoria expandidos com exemplo concreto, objetivos estratégicos (segregação, rastreabilidade, antifraude, controle formal), segurança (MFA, Sanctum/JWT, lockout, TLS) |
| Relatórios | Planejado | Relatórios gerenciais e exportação |

---

## Entidades do Domínio

### Entidades Principais
- **User** — Usuários do sistema (com role_id → Role, escopo por secretaria)
- **Tenant** — Prefeitura-cliente do SaaS (banco master — nome, slug, database_name, is_ativo, plano)
- **Contrato** — Contratos municipais (cadastro inteligente com score de risco)
- **Fornecedor** — Empresas fornecedoras (com validação de CNPJ)
- **Secretaria** — Secretarias/órgãos da prefeitura
- **Fiscal** — Fiscais de contrato (com histórico de trocas)
- **Aditivo** — Aditivos contratuais
- **Documento** — Documentos anexados (PDF, com tipo, versionamento, is_versao_atual, nome padronizado, hash_integridade, soft delete)
- **LogAcessoDocumento** — Log de acesso a documentos (upload, download, substituição, exclusão, visualização — append-only)
- **LoginLog** — Log de acessos ao sistema (user_id, ip, user_agent, success — append-only)
- **ExecucaoFinanceira** — Registros de execução/medição financeira
- **HistoricoAlteracao** — Log de auditoria de alterações (imutável)
- **Alerta** — Alertas de vencimento (tipo_evento, prioridade auto, data_disparo, tentativas_envio)
- **ConfiguracaoAlerta** — Prazos configuráveis (6 prazos: 120, 90, 60, 30, 15, 7 dias)
- **LogNotificacao** — Log de envio de notificações (canal, destinatário, sucesso)
- **DashboardAgregado** — Dados pré-calculados do painel executivo (atualizado diariamente)
- **Role** — Perfis de usuário dinâmicos (tabela roles, 8 padrão + customizáveis pelo admin)
- **Permission** — Permissões granulares no formato recurso.ação (contrato.editar, aditivo.aprovar)
- **UserPermission** — Permissões individuais/temporárias (com expires_at para substituições)
- **WorkflowAprovacao** — Registro de etapas de aprovação (polimórfico — 5 etapas sequenciais)

### Tipos/Categorias (Enums)
- **StatusContrato** — vigente, vencido, cancelado, suspenso, encerrado, rescindido
- **TipoContrato** — servico, obra, compra, locacao
- **ModalidadeContratacao** — pregao_eletronico, pregao_presencial, concorrencia, tomada_preco, convite, leilao, dispensa, inexigibilidade, adesao_ata
- **TipoPagamento** — mensal, por_medicao, parcelado, unico
- **CategoriaContrato** — essencial, nao_essencial
- **CategoriaServico** — transporte, alimentacao, tecnologia, obras, limpeza, seguranca, manutencao, saude, educacao, outros
- **NivelRisco** — baixo, medio, alto
- **TipoDocumentoContratual** — contrato_original, termo_referencia, publicacao_oficial, parecer_juridico, aditivo_doc, nota_empenho, nota_fiscal, ordem_servico, relatorio_medicao, relatorio_fiscalizacao, justificativa, documento_complementar
- **StatusCompletudeDocumental** — completo, parcial, incompleto
- **AcaoLogDocumento** — upload, download, substituicao, exclusao, visualizacao
- **TipoAditivo** — prazo, valor, prazo_e_valor, supressao, reequilibrio, alteracao_clausula, misto
- **StatusAditivo** — vigente, vencido, cancelado
- **StatusAprovacao** — pendente, aprovado, reprovado
- **EtapaWorkflow** — solicitacao, aprovacao_secretario, parecer_juridico, validacao_controladoria, homologacao
- **StatusAlerta** — pendente, enviado, visualizado, resolvido
- **PrioridadeAlerta** — informativo, atencao, urgente
- **CanalNotificacao** — email, sistema
- **TipoEventoAlerta** — vencimento_vigencia, termino_aditivo, prazo_garantia, prazo_execucao_fisica
- **CategoriaRisco** — vencimento, financeiro, documental, juridico, operacional

---

## Agentes

| # | Agente | Arquivo |
|---|--------|---------|
| 1 | Guardião de Regras | `memory/agents/01-guardiao-de-regras.md` |
| 2 | Gestor de Memória | `memory/agents/02-gestor-de-memoria.md` |
| 3 | Curador de Conhecimento | `memory/agents/03-curador-de-conhecimento.md` |
| 4 | Arquiteto | `memory/agents/04-arquiteto.md` |
| 5 | Engenheiro Executor | `memory/agents/05-engenheiro-executor.md` |
| 6 | Auditor Técnico | `memory/agents/06-auditor-tecnico.md` |

## Bases Detalhadas (Índices)

| Base | Índice | Pasta com arquivos |
|------|--------|--------------------|
| Governança Técnica | `memory/banco-de-regras.md` | `memory/regras/` (10 arquivos) |
| Estado do Projeto | `memory/banco-de-memoria.md` | `memory/memoria/` (4 arquivos) |
| Domínio de Negócio | `memory/banco-de-conhecimento.md` | `memory/conhecimento/` (11 arquivos) |
| Tema Visual / UI | `memory/banco-de-tema.md` | `memory/tema/` (9 arquivos) |

### Como carregar por contexto

| Tarefa | Arquivos a carregar |
|--------|---------------------|
| **Qualquer implementação** | `regras/_core.md` + `memoria/_core.md` + `conhecimento/_core.md` |
| **UI/Frontend** | + `tema/_core.md` + `tema/padroes-{modulo}.md` |
| **Migration/Schema** | + `regras/banco-de-dados.md` + `conhecimento/entidades.md` |
| **Novo módulo** | + `regras/classes-esperadas.md` + `regras/estrutura-diretorios.md` + `conhecimento/{modulo}.md` |
| **Decisão arquitetural** | + `memoria/adrs.md` + `regras/arquitetura.md` |
| **RBAC/Permissões** | + `regras/rbac.md` + `conhecimento/rbac.md` |
| **Segurança/LGPD** | + `regras/seguranca.md` + `conhecimento/transversal.md` |
| **Multi-tenant** | + `regras/multi-tenant.md` + `conhecimento/transversal.md` |
| **Planejar trabalho** | + `memoria/pendencias.md` |
