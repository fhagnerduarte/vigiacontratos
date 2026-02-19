# Banco de Memória — Estado do Projeto

> Consultado pelo **Gestor de Memória** (Agente 02) antes de cada ação.
> Atualizado ao final de cada implementação significativa (Etapa 7 do pipeline).
> Este é o registro vivo do projeto — a fonte da verdade sobre o que foi feito.

---

## Estado Atual

| Campo | Valor |
|---|---|
| Projeto | vigiacontratos |
| Tipo | Sistema de gestão contratual municipal |
| Fase Atual | Fase 0 — Setup Inicial |
| Última Atualização | 2026-02-18 |
| Próximo Passo | Fase 1 — Configurar projeto Laravel + banco + template WowDash |

### Cadeia de Fases

```
[Fase 0: Setup] → [Fase 1: Infraestrutura] → [Fase 2: Cadastros Base + RBAC] → [Fase 3: Contratos] → [Fase 4: Alertas] → [Fase 5: Dashboard + Painel de Risco + Relatórios] → [Fase 6: Refinamento]
     ▲ atual
```

**Detalhamento das Fases:**
- **Fase 0 — Setup Inicial:** Preencher bases de conhecimento, definir stack e convenções
- **Fase 1 — Infraestrutura:** Criar projeto Laravel, configurar Docker/Sail, MySQL, Redis, integrar template WowDash, autenticação, migrations base
- **Fase 2 — Cadastros Base + RBAC:** CRUD de Secretarias, Fornecedores + Módulo 7: Perfis de Usuário (RBAC com 8 roles, permissions, user_secretarias, workflow de aprovação)
- **Fase 3 — Contratos (Cadastro Inteligente):** Wizard multi-etapa, CRUD completo de Contratos + Aditivos + Fiscais + Central de Documentos (Módulo 5: pasta digital, versionamento, completude, log de acesso, busca, dashboard, relatório TCE) + Execução Financeira + Score de Risco + Audit Trail
- **Fase 4 — Alertas (Motor de Monitoramento):** Command agendado (cron diário) + Queue (Redis) + Notifications (mail + database) + Dashboard de alertas + Configuração de prazos + Log de notificação + Bloqueio preventivo + Resolução automática
- **Fase 5 — Dashboard Executivo, Painel de Risco e Relatórios:** Painel Executivo com 5 blocos estratégicos (financeiro, risco, vencimentos, secretarias, essenciais), score de gestão 0-100, tendências mensais, ranking de fornecedores, visão do controlador, agregação noturna, cache Redis + Painel de Risco Administrativo dedicado (score expandido com 5 categorias, ranking de risco, mapa por secretaria, relatório TCE de risco, alertas preventivos inteligentes) + relatórios gerenciais e exportação
- **Fase 6 — Refinamento:** Testes, ajustes de UX, performance, segurança final

---

## Registro de Implementações

| ID | Data | Descrição | Arquivos Afetados | Status |
|---|---|---|---|---|
| IMP-001 | 2026-02-18 | Preenchimento das bases de conhecimento do projeto | CLAUDE.md, memory/*.md | Concluído |
| IMP-002 | 2026-02-18 | Detalhamento do Módulo 1 — Cadastro Inteligente de Contratos | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-003 | 2026-02-18 | Detalhamento do Módulo 2 — Alertas Automáticos de Vencimento | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-004 | 2026-02-18 | Detalhamento do Módulo 3 — Painel Executivo (Dashboard) | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-005 | 2026-02-18 | Detalhamento do Módulo 4 — Gestão de Aditivos (expansão completa: 7 tipos, limites legais, reequilíbrio, score de risco, timeline, dashboard) | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-006 | 2026-02-18 | Detalhamento do Módulo 5 — Central de Documentos (pasta digital por contrato, 12 tipos de documento, versionamento não-destrutivo, log de acesso, completude documental, validações automáticas, busca inteligente, dashboard de indicadores, relatório TCE, OCR como Fase 2) | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-007 | 2026-02-18 | Detalhamento do Módulo 6 — Painel de Risco Administrativo (grande diferencial estratégico: score expandido com 5 categorias de risco, dashboard dedicado com ranking e semáforo, mapa de risco por secretaria, relatório automatizado para TCE, alertas preventivos inteligentes, WhatsApp como Fase 2) | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-008 | 2026-02-18 | Detalhamento dos Requisitos Técnicos Estratégicos — SaaS multi-tenant com banco isolado por prefeitura, segurança expandida (Argon2id, MFA, lockout, logs de login), LGPD (base legal, retenção, anonimização), auditoria (hash SHA-256, logs imutáveis, relatórios exportáveis), performance (capacidade por tenant, paginação, disponibilidade 24/7), armazenamento S3-compatible, requisitos não-funcionais de UI | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |
| IMP-009 | 2026-02-18 | Detalhamento do Módulo 7 — Perfis de Usuário (RBAC): 8 perfis dinâmicos (tabela roles), permissões granulares por recurso.ação, escopo por secretaria, permissões temporárias com expires_at, workflow de aprovação 5 etapas para aditivos, logs de auditoria expandidos com perfil do usuário | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md | Concluído |
| IMP-010 | 2026-02-18 | Expansão do Módulo 7 — Perfis de Usuário: objetivos estratégicos do módulo (segregação, rastreabilidade, antifraude, controle formal), ocupantes típicos por perfil, matriz de permissões granulares completa (12 recursos × 8 perfis com indicação de escopo por secretaria), exemplo concreto de log de auditoria, regras de segurança de autenticação/sessão (MFA, JWT/Sanctum, lockout, TLS) | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md | Concluído |

### Como registrar:
1. Use ID sequencial (IMP-XXX)
2. Descreva o que foi feito (não como)
3. Liste os arquivos principais afetados
4. Status: `Concluído` | `Parcial` | `Revertido`

---

## Decisões Arquiteturais (ADRs)

| ID | Data | Decisão | Contexto | Alternativas Consideradas |
|---|---|---|---|---|
| ADR-001 | 2026-02-18 | Stack: Laravel 12 + PHP 8.2+ + MySQL 8 + Redis | Escolha da stack principal do projeto | Node.js/Express, Python/Django — Laravel escolhido pela produtividade e ecossistema maduro para admin panels |
| ADR-002 | 2026-02-18 | Template UI: WowDash (Bootstrap 5) | Escolha do template admin para o frontend | AdminLTE, Tabler, Tailwind puro — WowDash escolhido pois já disponível e com componentes ricos |
| ADR-003 | 2026-02-18 | Container: Docker / Laravel Sail | Ambiente de desenvolvimento padronizado | PHP local, Herd/Valet — Sail escolhido pela consistência entre ambientes |
| ADR-004 | 2026-02-18 | ~~3 perfis de usuário: Admin, Gestor, Consulta~~ **→ Substituída por ADR-050** | Definição dos níveis de acesso — substituída por RBAC com 8 perfis dinâmicos | ~~2 perfis (admin/operador), RBAC granular~~ — decisão revisada: RBAC completo necessário para segregação institucional |
| ADR-005 | 2026-02-18 | Alertas com prazos configuráveis pelo admin | Como gerenciar antecedência dos alertas de vencimento | Prazos fixos (30/60/90 dias) — configurável dá flexibilidade sem complexidade |
| ADR-006 | 2026-02-18 | Tipos de contrato: Serviços, Obras, Compras, Locação | Tipos iniciais de contrato municipal | Incluir Concessão/Convênio — mantidos os 4 mais comuns, expansível via Enum |
| ADR-007 | 2026-02-18 | Cadastro de contrato via formulário multi-etapa (wizard 6 passos) | UX do cadastro de contratos — garantir qualidade dos dados e reduzir erros | Formulário único longo — wizard escolhido para melhor UX e validação por etapa |
| ADR-008 | 2026-02-18 | Fiscal como entidade separada (tabela `fiscais`) com histórico de trocas | Rastreabilidade de fiscais — auditorias exigem saber quem fiscalizou em cada período | Campo texto simples no contrato — entidade separada permite histórico e validação |
| ADR-009 | 2026-02-18 | Audit trail completo via tabela `historico_alteracoes` (polimórfica, imutável) | Requisito de auditoria e Tribunal de Contas — toda alteração deve ser rastreável | Logs de aplicação, pacote terceiro (Spatie Activity Log) — implementação própria para controle total |
| ADR-010 | 2026-02-18 | Execução financeira como entidade separada (tabela `execucoes_financeiras`) | Acompanhamento do percentual executado e alertas de estouro | Campo percentual manual no contrato — entidade separada permite cálculo automático e histórico |
| ADR-011 | 2026-02-18 | Score de risco automático calculado por critérios objetivos | Diferencial competitivo — classificação de risco encanta controladores internos | Classificação manual — automática é mais consistente e escalável |
| ADR-012 | 2026-02-18 | Modalidade de contratação como Enum (9 valores) | Validações legais dependem da modalidade (dispensa exige fundamento, etc.) | Campo texto livre — Enum garante consistência e permite regras condicionais |
| ADR-013 | 2026-02-18 | Motor de monitoramento via Scheduled Command (cron diário) | Como verificar vencimentos automaticamente — precisa ser confiável, executar diariamente sem intervenção | Evento listener, Webhook externo — Command agendado é nativo do Laravel, confiável e auditável |
| ADR-014 | 2026-02-18 | 6 prazos de alerta configuráveis (120, 90, 60, 30, 15, 7 dias) | Cobertura adequada de prazos para ação preventiva | 3 prazos fixos (30/60/90), prazo único — 6 prazos dão cobertura ampla e são configuráveis pelo admin |
| ADR-015 | 2026-02-18 | Notificações assíncronas via Laravel Queue (Redis) | Envio de email não pode bloquear o motor de monitoramento | Envio síncrono — queue permite retry, backoff exponencial e tolerância a falhas |
| ADR-016 | 2026-02-18 | Log de notificação como tabela separada (log_notificacoes) | Rastreabilidade de cada envio, auditoria de quem foi notificado | Log em arquivo — tabela permite consulta, dashboard e auditoria |
| ADR-017 | 2026-02-18 | Bloqueio preventivo de contrato vencido (modo IRREGULAR) | Forçar ação administrativa — impedir que contratos fiquem vencidos sem tratamento | Apenas alerta visual — bloqueio de aditivo retroativo sem justificativa adiciona pressão operacional |
| ADR-018 | 2026-02-18 | Prioridade de alerta automática por proximidade (>30d/≤30d/≤7d) | Reduzir carga cognitiva do gestor — cores e prioridades devem ser automáticas | Prioridade manual — automática é consistente, sem dependência de ação humana |
| ADR-019 | 2026-02-18 | Dashboard com dados pré-agregados em tabela dedicada (dashboard_agregados) | Performance <2s exige dados pré-calculados, não queries em tempo real | Materialized views MySQL (não suporta nativamente), queries otimizadas com índices (lento em volume) |
| ADR-020 | 2026-02-18 | Cache Redis por município com TTL 24h para dados do dashboard | Reduzir carga no banco e garantir resposta rápida do painel executivo | Cache em arquivo (mais lento), sem cache com queries diretas (impacta performance) |
| ADR-021 | 2026-02-18 | Agregação noturna via Scheduled Command (AgregarDashboardCommand) | Processamento pesado fora do horário comercial para não impactar usuários | Queue job (menos previsível), trigger no banco (acoplamento excessivo) |
| ADR-022 | 2026-02-18 | Score de Gestão Contratual como nota 0-100 | Diferencial competitivo — permite dizer "município nota 82/100" em reuniões | Semáforo simples (menos granular), sem score (perde diferencial estratégico) |
| ADR-023 | 2026-02-18 | Painel de contratos essenciais separado no dashboard | Contratos de serviços vitais (merenda, transporte) precisam de destaque especial para o prefeito | Mesma lista com filtro (menos visibilidade), badge na listagem geral (perde impacto) |
| ADR-024 | 2026-02-18 | DashboardService centraliza toda lógica de agregação e consulta do painel | Lógica complexa de cálculos de indicadores não deve ficar no controller | Queries no controller (anti-pattern), repository pattern (overengineering neste caso) |
| ADR-025 | 2026-02-18 | TipoAditivo expandido de 4 para 7 valores (+ reequilibrio, alteracao_clausula, misto) | Necessidade de representar casos reais de aditamento municipal — reequilíbrio econômico-financeiro é comum em contratos de longa duração | Criar entidade separada para reequilíbrio — Enum mais simples e consistente com padrão do projeto |
| ADR-026 | 2026-02-18 | valor_acrescimo e valor_supressao como campos separados (substituindo valor_aditivo) | Tipos mistos precisam de ambos os valores; clareza no UI; cálculo de percentual acumulado exige separação | Manter valor_aditivo com sinal (positivo/negativo) — campos separados são mais expressivos e evitam confusão em aditivos tipo misto |
| ADR-027 | 2026-02-18 | Limites legais configuráveis em tabela `configuracoes_limite_aditivo` | Percentuais legais variam por tipo de contrato e podem mudar por decisão administrativa | Hardcoded no código — tabela permite configuração sem deploy; valores padrão via seeder |
| ADR-028 | 2026-02-18 | Critérios de risco de aditivos integrados ao score_risco existente (não score separado) | Aditivos frequentes ou com alto percentual acumulado aumentam o risco do contrato | Score separado para aditivos — integração ao score existente evita overengineering e mantém classificação única |
| ADR-029 | 2026-02-18 | Lógica de reequilíbrio mantida em AditivoService (método processarReequilibrio()) — sem ReequilibrioService | Reequilíbrio é um tipo de aditivo, não uma entidade distinta; a lógica é similar ao fluxo de aditivo de valor | ReequilibrioService separado — overengineering sem uso concreto imediato |
| ADR-030 | 2026-02-18 | historico_contrato (sugerida) descartada — usar historico_alteracoes existente (ADR-009) | Tabela polimórfica já cobre o caso de uso; criar tabela dedicada seria retrabalho e violaria ADR-009 | Tabela separada historico_contrato — viola princípio de não retrabalho |
| ADR-031 | 2026-02-18 | numero_sequencial calculado via MAX()+1 por contrato no momento da criação | Simplicidade de implementação sem dependência de sequence de banco — MySQL não suporta sequence nativo | Sequence de banco, UUID — MAX+1 é suficiente dado o volume esperado de aditivos por contrato |
| ADR-032 | 2026-02-18 | Limite de upload aumentado para 20MB por arquivo (de 10MB) | Documentos contratuais completos (contratos grandes, plantas de obras) frequentemente ultrapassam 10MB. A nova especificação de domínio define 20MB | Manter 10MB (insuficiente para casos reais) — 20MB cobre a maioria dos documentos sem risco de sobrecarga |
| ADR-033 | 2026-02-18 | Estrutura de storage isolada por contrato e tipo: `documentos/contratos/{contrato_id}/{tipo_documento}/` | Facilita listagem por tipo, isolamento entre contratos e eventual migração para S3 sem reorganização | Estrutura plana `documentos/{entidade}/{id}/{arquivo}` (anterior) — nova estrutura é mais granular e organizada |
| ADR-034 | 2026-02-18 | Versionamento por campo `versao` + `is_versao_atual` (sem tabela separada de versões) | Simplicidade — a entidade Documento já suporta múltiplos registros do mesmo tipo. Campo booleano `is_versao_atual` filtra a versão ativa | Tabela separada document_versions — overengineering; o modelo polimórfico já acomoda o histórico |
| ADR-035 | 2026-02-18 | Log de acesso a documentos em tabela separada `log_acesso_documentos` (não na historico_alteracoes) | Volume alto de eventos de acesso (download, visualização) poluiria o audit trail geral. Tabela dedicada permite consultas específicas e índices otimizados | Usar historico_alteracoes existente — mistura semântica inadequada; acesso a documento não é "alteração de entidade" |
| ADR-036 | 2026-02-18 | Completude documental como campo calculado no Model Contrato (accessor ou campo cacheado via Observer) | Dado derivado simples — pode ser calculado via accessor do Eloquent ou campo cacheado atualizado pelo DocumentoObserver. Sem necessidade de tabela extra | Tabela dedicada de completude — overengineering sem uso concreto adicional |
| ADR-037 | 2026-02-18 | OCR e busca full-text em PDF classificados como Fase 2 (não implementar em V1) | Tecnologias adicionais (Tesseract, ElasticSearch, microserviço) aumentariam significativamente a complexidade do stack sem valor imediato para a V1 | Implementar OCR em V1 — aumenta dependências e prazo sem uso concreto no lançamento |
| ADR-038 | 2026-02-18 | Score de risco expandido com 5 categorias (vencimento, financeiro, documental, jurídico, operacional) — campo único `score_risco` no Contrato | Módulo 6 exige classificação de risco mais granular com 5 categorias e subcritérios detalhados. Expandir o score_risco existente mantém campo único e evita duplicação. Critérios documentais granulares (RN-139) substituem o critério binário `sem_documento` existente | Score separado para o painel de risco — dois scores coexistiriam, complexidade desnecessária |
| ADR-039 | 2026-02-18 | Painel de Risco como página dedicada (`/painel-risco`) com resumo no Dashboard Executivo | Módulo 6 é o grande diferencial estratégico do sistema — merece visibilidade própria e destaque no menu lateral. Dashboard Executivo mantém Bloco 2 (resumo de risco) com link "Ver detalhes" para o Painel completo | Tudo dentro do Dashboard Executivo — perde destaque e sobrecarrega uma única página |
| ADR-040 | 2026-02-18 | PainelRiscoService dedicado (não expandir RiscoService) | RiscoService calcula o score por contrato individual. PainelRiscoService agrega indicadores do painel, ranking, mapa por secretaria e relatório TCE — responsabilidades distintas (SRP) | Expandir RiscoService — mistura cálculo unitário com agregação de indicadores, viola separação de responsabilidades |
| ADR-041 | 2026-02-18 | WhatsApp institucional como Fase 2 (não implementar em V1) | Similar ao OCR (ADR-037). API WhatsApp Business adiciona dependência externa e complexidade sem valor imediato para V1. V1 mantém canais email + sistema | Incluir WhatsApp em V1 — complexidade e custo de API sem necessidade imediata |
| ADR-042 | 2026-02-18 | Multi-tenant com banco isolado por prefeitura (database-per-tenant) | Segurança jurídica e isolamento político — dados de cada prefeitura em banco separado. Banco central/master para gestão de tenants. Estratégia pensada para SaaS nacional | tenant_id em tabela única — menos seguro juridicamente, risco de vazamento entre prefeituras |
| ADR-043 | 2026-02-18 | Armazenamento de arquivos em S3-compatible (MinIO dev / AWS S3 prod) | Nunca salvar arquivos no filesystem direto — escalabilidade e portabilidade. Bucket/pasta isolada por tenant | Storage local com links simbólicos — não escala, não permite multi-servidor |
| ADR-044 | 2026-02-18 | Senhas com Argon2id (driver padrão do Laravel) | Segurança de acesso — Argon2id é resistente a ataques de GPU e side-channel | bcrypt (padrão anterior) — Argon2id é mais moderno e recomendado |
| ADR-045 | 2026-02-18 | MFA opcional para usuários admin/gestor (TOTP) | Requisito de segurança para venda a prefeituras — reduz risco de acesso indevido | MFA obrigatório — impacta UX para municípios menores; sem MFA — insuficiente para vendas |
| ADR-046 | 2026-02-18 | Bloqueio de login após 5 tentativas com cooldown 15min | Proteção contra brute force — logs de login em tabela dedicada | Sem bloqueio — vulnerável; CAPTCHA — menos amigável |
| ADR-047 | 2026-02-18 | Hash SHA-256 de integridade para documentos contratuais | Proteção contra alegação de adulteração — hash gerado no upload e armazenado junto ao documento | MD5 — vulnerável a colisões; sem hash — sem prova de integridade |
| ADR-048 | 2026-02-18 | Logs de login em tabela dedicada `login_logs` | Rastreabilidade de acessos — requisito LGPD e segurança para venda a órgãos públicos | Log em arquivo — difícil consulta e auditoria |
| ADR-049 | 2026-02-18 | Sessão com expiração automática (SESSION_LIFETIME configurável, padrão 120min) | Segurança de acesso — sessões não podem permanecer ativas indefinidamente | Sem expiração — risco de sessões abandonadas em terminais públicos |
| ADR-050 | 2026-02-18 | RBAC com tabela `roles` dinâmica e 8 perfis padrão (substitui ADR-004) | Necessidade de segregação de função, controle interno, LGPD e aceitação institucional para venda a prefeituras. Admin pode criar perfis customizados | Enum PerfilUsuario fixo (não permite customização), manter 3 perfis antigos (insuficiente para segregação institucional) |
| ADR-051 | 2026-02-18 | Permissões granulares via tabela `permissions` com formato `recurso.acao` | Controle fino de acesso: `contrato.editar`, `aditivo.aprovar`. Necessário para auditoria e conformidade institucional | Middleware simples por perfil — não permite controle granular por ação e recurso |
| ADR-052 | 2026-02-18 | Workflow de aprovação 5 etapas para aditivos (V1) | Segregação de função obrigatória em aditivos: Gestor→Secretário→Jurídico→Controladoria→Homologação. Nenhum perfil aprova sozinho | Sem workflow — concentra decisão em perfil único, risco institucional de fraude |
| ADR-053 | 2026-02-18 | Permissões temporárias com `expires_at` na tabela `user_permissions` (V1) | Necessidade de substituições durante férias sem alterar perfil base. Job diário revoga automaticamente | Sem expiração — risco de acesso residual após fim da substituição |
| ADR-054 | 2026-02-18 | Permissão por secretaria via tabela `user_secretarias` | Secretário/Gestor/Fiscal acessam apenas contratos de secretarias vinculadas. Isolamento de acesso por órgão | Sem escopo por secretaria — todos veem tudo, viola segregação institucional |

### Como registrar:
1. Use ID sequencial (ADR-XXX)
2. Descreva a decisão de forma clara e objetiva
3. Contexto: por que essa decisão foi necessária?
4. Alternativas: o que mais foi considerado e por que não foi escolhido?

### Regras sobre ADRs:
- Uma vez registrada, uma ADR só pode ser alterada com justificativa explícita
- O Gestor de Memória (Agente 02) **bloqueia** ações que contradigam ADRs registradas
- Para reverter uma ADR, registre uma nova ADR que a substitua (nunca delete a original)

---

## Problemas Conhecidos

| ID | Descrição | Severidade | Módulo | Status |
|---|---|---|---|---|
| — | Nenhum problema registrado | — | — | — |

---

## Pendências

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

---

## Instruções de Manutenção

### Quando atualizar este arquivo?
- Após **cada implementação** aprovada pelo Auditor (Etapa 7)
- Quando uma **nova ADR** for tomada
- Quando um **problema** for descoberto ou resolvido
- Quando **pendências** forem concluídas ou criadas

### O que atualizar?
1. **Estado Atual**: atualizar fase e próximo passo
2. **Implementações**: adicionar novo registro IMP-XXX
3. **ADRs**: registrar se decisão arquitetural foi tomada
4. **Problemas**: adicionar/resolver bugs
5. **Pendências**: marcar concluídas ou adicionar novas
6. **MEMORY.md**: atualizar tabela de módulos se novo módulo criado
