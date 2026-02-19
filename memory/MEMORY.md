# vigiacontratos — Índice Rápido

## Status

| Campo | Valor |
|---|---|
| Tipo | Sistema de gestão contratual municipal (SaaS multi-tenant) |
| Modelo | SaaS com banco isolado por prefeitura (database-per-tenant) |
| Stack | Laravel 12, PHP 8.2+, MySQL 8, Redis, S3-compatible |
| Container | Docker / Laravel Sail |
| Fase Atual | Fase 0 — Setup Inicial |
| Última Atualização | 2026-02-18 |

---

## Módulos

| Módulo | Status | Observação |
|---|---|---|
| Multi-Tenant | Em Detalhamento | Banco isolado por prefeitura, banco master para gestão de tenants, middleware SetTenantConnection, storage e cache isolados por tenant |
| Dashboard Executivo | Em Detalhamento | Painel estratégico com 5 blocos (financeiro, risco, vencimentos, secretarias, essenciais), score de gestão 0-100, tendências, rankings, filtros inteligentes, agregação noturna, cache Redis |
| Contratos | Planejado | Cadastro inteligente multi-etapa, score de risco, fiscais, execução financeira, audit trail |
| Fornecedores | Planejado | Cadastro e gestão de empresas fornecedoras |
| Aditivos | Em Detalhamento | 7 tipos (prazo, valor, prazo_e_valor, supressao, reequilibrio, alteracao_clausula, misto), controle de limite legal 25%/50% configurável, percentual acumulado, score de risco integrado, numero sequencial automático, reequilíbrio econômico-financeiro, dashboard de aditivos, timeline de histórico |
| Alertas | Planejado | Motor de monitoramento diário, 6 prazos configuráveis, notificação multi-canal, dashboard de alertas, bloqueio preventivo |
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

## Decisões Recentes

| Data | Decisão | Contexto |
|---|---|---|
| 2026-02-18 | Stack: Laravel 12 + MySQL 8 + Redis + Docker/Sail | Escolha inicial da stack do projeto |
| 2026-02-18 | Template UI: WowDash (Bootstrap 5) | Template admin com dashboard, componentes e dark mode |
| 2026-02-18 | ~~Perfis: Admin, Gestor, Consulta~~ **→ Substituído por RBAC (ADR-050)** | 8 perfis dinâmicos via tabela roles + permissões granulares |
| 2026-02-18 | Alertas com prazos configuráveis | Admin define dias de antecedência para alertas |
| 2026-02-18 | Cadastro inteligente de contratos (wizard 6 etapas) | Formulário multi-etapa com validação por passo |
| 2026-02-18 | Fiscal como entidade com histórico | Rastreabilidade de trocas de fiscal |
| 2026-02-18 | Audit trail completo (historico_alteracoes) | Toda alteração logada com campo, valor, usuário, IP |
| 2026-02-18 | Score de risco automático | Classificação baixo/médio/alto por critérios objetivos |
| 2026-02-18 | Execução financeira como entidade | Acompanhamento de pagamentos e percentual executado |
| 2026-02-18 | Motor de monitoramento via Scheduled Command (cron diário) | Verificação automática de vencimentos de contratos |
| 2026-02-18 | 6 prazos de alerta configuráveis (120, 90, 60, 30, 15, 7 dias) | Cobertura ampla para ação preventiva |
| 2026-02-18 | Notificações assíncronas via Queue (Redis) | Envio de email e notificação interna sem bloquear sistema |
| 2026-02-18 | Log de notificação como tabela (log_notificacoes) | Rastreabilidade de cada envio de alerta |
| 2026-02-18 | Bloqueio preventivo de contrato vencido (modo IRREGULAR) | Impedir aditivo retroativo sem justificativa |
| 2026-02-18 | Prioridade automática por proximidade (>30d/≤30d/≤7d) | Classificação de urgência sem intervenção manual |
| 2026-02-18 | Dados do dashboard pré-agregados em tabela dedicada (dashboard_agregados) | Performance <2s, cron noturno |
| 2026-02-18 | Cache Redis para dashboard (TTL 24h) | Reduzir carga no banco |
| 2026-02-18 | Score de gestão contratual (0-100) | Diferencial competitivo |
| 2026-02-18 | Painel separado para contratos essenciais | Destaque para serviços vitais |
| 2026-02-18 | DashboardService para lógica de agregação | Separação de responsabilidades |
| 2026-02-18 | Agregação noturna via AgregarDashboardCommand | Processamento fora do horário comercial |
| 2026-02-18 | TipoAditivo expandido para 7 valores (+ reequilibrio, alteracao_clausula, misto) | Cobertura completa dos tipos reais de aditamento municipal |
| 2026-02-18 | Campos valor_acrescimo e valor_supressao substituem valor_aditivo | Clareza para tipos mistos e cálculo de percentual acumulado |
| 2026-02-18 | Limites legais configuráveis em tabela configuracoes_limite_aditivo | Padrão 25% (serviços) e 50% (obras) — configurável pelo admin |
| 2026-02-18 | Critérios de risco de aditivos integrados ao score_risco (não score separado) | RN-106, RN-107, RN-108 adicionados ao cálculo existente |
| 2026-02-18 | Lógica de reequilíbrio em AditivoService.processarReequilibrio() | Sem novo Service — evita overengineering |
| 2026-02-18 | historico_contrato descartada — usar historico_alteracoes existente | Consistente com ADR-009; sem retrabalho |
| 2026-02-18 | TipoDocumentoContratual expandido de 7 para 12 valores | Cobertura completa dos tipos reais de documentação contratual municipal |
| 2026-02-18 | Detalhamento do Módulo 5 — Central de Documentos | Pasta digital por contrato, versionamento, log de acesso, completude documental, validações automáticas, busca inteligente, dashboard, relatório TCE |
| 2026-02-18 | Limite de upload aumentado de 10MB para 20MB | Documentos contratuais completos frequentemente ultrapassam 10MB |
| 2026-02-18 | OCR e busca full-text classificados como Fase 2 | Não implementar em V1 — reduz complexidade do stack inicial |
| 2026-02-18 | numero_sequencial gerado via MAX+1 por contrato na criação | Simples, sem dependência de sequence de banco |
| 2026-02-18 | Score de risco expandido com 5 categorias — campo único no Contrato | Vencimento, financeiro, documental, jurídico, operacional — integrado ao score_risco existente |
| 2026-02-18 | Painel de Risco como página dedicada `/painel-risco` + resumo no Dashboard | Grande diferencial estratégico merece visibilidade própria |
| 2026-02-18 | PainelRiscoService dedicado (separado do RiscoService) | RiscoService calcula score unitário, PainelRiscoService agrega indicadores (SRP) |
| 2026-02-18 | WhatsApp institucional como Fase 2 (não V1) | Similar ao OCR — reduz complexidade sem valor imediato |
| 2026-02-18 | Detalhamento do Módulo 6 — Painel de Risco Administrativo | Score expandido, dashboard dedicado, mapa por secretaria, relatório TCE de risco, alertas inteligentes |
| 2026-02-18 | **SaaS multi-tenant com banco isolado por prefeitura (ADR-042)** | Isolamento total de dados — cada prefeitura tem banco MySQL próprio. Banco master para gestão de tenants |
| 2026-02-18 | **Armazenamento S3-compatible: MinIO dev / AWS S3 prod (ADR-043)** | Nunca filesystem direto. Bucket/pasta isolada por tenant |
| 2026-02-18 | **Segurança: Argon2id + MFA opcional + lockout + logs de login (ADR-044 a ADR-049)** | Argon2id para senhas, TOTP para admin/gestor, bloqueio após 5 tentativas, tabela login_logs, sessão com expiração 120min |
| 2026-02-18 | **Hash de integridade SHA-256 para documentos (ADR-047, RN-220)** | Hash calculado no upload, armazenado em hash_integridade, verificável a qualquer momento |
| 2026-02-18 | **LGPD: base legal, retenção, anonimização (RN-210 a RN-213)** | Registro de base legal por tratamento, política de retenção configurável por tenant, anonimização sob solicitação |
| 2026-02-18 | **Capacidade por tenant: 5k-20k contratos, 50k+ docs, 100 users** | Requisitos de performance e escalabilidade definidos |
| 2026-02-18 | **Requisitos não-funcionais: responsivo, <2s, 24/7** | Interface simples, responsiva, disponível 24/7, manual online (Fase 2) |
| 2026-02-18 | **RBAC com tabela `roles` dinâmica e 8 perfis padrão (ADR-050, substitui ADR-004)** | Segregação de função, controle interno, LGPD. Admin pode criar perfis customizados |
| 2026-02-18 | **Permissões granulares via tabela `permissions` (ADR-051)** | Formato `recurso.acao`: contrato.editar, aditivo.aprovar |
| 2026-02-18 | **Workflow de aprovação 5 etapas para aditivos (ADR-052)** | Gestor → Secretário → Jurídico → Controladoria → Homologação |
| 2026-02-18 | **Permissões temporárias com `expires_at` (ADR-053)** | Substituições durante férias, revogação automática por job diário |
| 2026-02-18 | **Permissão por secretaria via `user_secretarias` (ADR-054)** | Secretário/Gestor/Fiscal acessam apenas contratos de secretarias vinculadas |

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

## Bases Detalhadas

| Base | Arquivo |
|------|---------|
| Governança Técnica | `memory/banco-de-regras.md` |
| Estado do Projeto | `memory/banco-de-memoria.md` |
| Domínio de Negócio | `memory/banco-de-conhecimento.md` |
| Tema Visual / UI | `memory/banco-de-tema.md` |
