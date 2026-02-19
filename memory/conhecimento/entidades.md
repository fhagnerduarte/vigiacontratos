# Conhecimento — Entidades e Relacionamentos

> Extraido de `banco-de-conhecimento.md`. Carregar quando precisar de detalhes sobre entidades, campos e relacionamentos.
> Contem o diagrama de relacionamentos e o detalhamento completo de todas as entidades do sistema.

---

## Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
[User] N──1 [Role] (role_id — perfil ativo)
[Role] N──N [Permission] (via role_permissions)
[User] N──N [Permission] (via user_permissions — permissões individuais/temporárias)
[User] N──N [Secretaria] (via user_secretarias — escopo de acesso)

[Secretaria] 1──N [Contrato]

[Fornecedor] 1──N [Contrato]

[Contrato] 1──N [Aditivo]
[Contrato] 1──N [Documento] (polimórfico)
[Contrato] 1──N [Alerta]
[Contrato] 1──N [Fiscal]
[Contrato] 1──N [ExecucaoFinanceira]
[Contrato] 1──N [HistoricoAlteracao] (polimórfico)

[Aditivo] 1──N [Documento] (polimórfico)
[Aditivo] 1──N [WorkflowAprovacao] (polimórfico)

[WorkflowAprovacao] N──1 [Role] (role_responsavel_id)
[WorkflowAprovacao] N──1 [User] (user_id — quem aprovou)

[Documento] 1──N [LogAcessoDocumento]

[Alerta] 1──N [LogNotificacao]

[ConfiguracaoAlerta] (tabela de configuração — prazos de alerta)
[ConfiguracaoLimiteAditivo] (tabela de configuração — limites legais de aditivos)

[DashboardAgregado] (tabela de agregação — dados pré-calculados do painel executivo)

[User] 1──N [LoginLog] (log de acessos ao sistema)

--- Banco Master (Multi-Tenant) ---
[Tenant] 1──N [TenantUser]

[User] 1──N [Documento] (uploaded_by)
[User] 1──N [ExecucaoFinanceira] (registrado_por)
[User] 1──N [HistoricoAlteracao] (user_id)
[User] 1──N [Alerta] (visualizado_por, resolvido_por)
```

### Detalhamento das Entidades

#### Entidade: User

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| name | varchar(255) | Sim | Nome completo |
| email | varchar(255) | Sim | Único, usado para login |
| password | varchar(255) | Sim | Hash Argon2id (ADR-044) |
| role_id | bigint | Sim | FK → roles (RN-301). Perfil ativo do usuário |
| is_ativo | boolean | Sim | Default: true |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- belongsTo: Role (role_id) — perfil ativo do usuário (RN-301)
- belongsToMany: Secretaria (via `user_secretarias`) — escopo de acesso (RN-325)
- belongsToMany: Permission (via `user_permissions`) — permissões individuais/temporárias (RN-303)
- hasMany: HistoricoAlteracao, Documento (uploaded_by), ExecucaoFinanceira (registrado_por), WorkflowAprovacao

#### Entidade: Contrato

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| numero | varchar(50) | Sim | Único. Formato: NNN/AAAA |
| ano | varchar(4) | Sim | Ano do contrato (ex: 2026) |
| objeto | text | Sim | Descrição do objeto contratado |
| tipo | enum(TipoContrato) | Sim | servico, obra, compra, locacao |
| status | enum(StatusContrato) | Sim | Default: vigente |
| modalidade_contratacao | enum(ModalidadeContratacao) | Sim | Modalidade da licitação/contratação |
| fornecedor_id | bigint | Sim | FK → fornecedores |
| secretaria_id | bigint | Sim | FK → secretarias |
| unidade_gestora | varchar(255) | Não | Subdivisão da secretaria |
| data_inicio | date | Sim | Início da vigência |
| data_fim | date | Sim | Fim da vigência (atualizada por aditivos) |
| prazo_meses | int | Sim | Calculado automaticamente (RN-031) |
| prorrogacao_automatica | boolean | Sim | Default: false |
| valor_global | decimal(15,2) | Sim | Valor total (atualizado por aditivos) |
| valor_mensal | decimal(15,2) | Não | Valor mensal, se aplicável |
| tipo_pagamento | enum(TipoPagamento) | Não | mensal, por_medicao, parcelado, unico |
| fonte_recurso | varchar(255) | Não | Origem do recurso |
| dotacao_orcamentaria | varchar(255) | Não | Classificação orçamentária |
| numero_empenho | varchar(50) | Não | Número do empenho |
| numero_processo | varchar(50) | Sim* | Número do processo administrativo (*obrigatório para contrato ativo — RN-023) |
| fundamento_legal | varchar(255) | Sim** | Base legal (**obrigatório para dispensa/inexigibilidade — RN-025) |
| categoria | enum(CategoriaContrato) | Não | essencial, nao_essencial |
| categoria_servico | enum(CategoriaServico) | Não | Classificação do tipo de serviço |
| responsavel_tecnico | varchar(255) | Sim*** | Profissional técnico (***obrigatório para obras — RN-028) |
| gestor_nome | varchar(255) | Não | Nome do gestor do contrato |
| score_risco | int | Sim | Calculado automaticamente (RN-029). Default: 0 |
| nivel_risco | enum(NivelRisco) | Sim | Derivado do score (baixo/medio/alto). Default: baixo |
| percentual_executado | decimal(5,2) | Sim | Calculado automaticamente (RN-032). Default: 0 |
| observacoes | text | Não | Observações gerais |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |
| deleted_at | datetime | Não | Soft delete |

**Relacionamentos:**
- belongsTo: Fornecedor
- belongsTo: Secretaria
- hasMany: Aditivo
- hasMany: Documento (polimórfico)
- hasMany: Alerta
- hasMany: Fiscal
- hasMany: ExecucaoFinanceira
- morphMany: HistoricoAlteracao

**Status possíveis:**
- vigente → vencido (automático por job)
- vigente → cancelado (manual)
- vigente → suspenso (manual)
- vigente → rescindido (manual)
- suspenso → vigente (manual)
- vigente → encerrado (manual, ao término normal)

#### Entidade: Fornecedor

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| razao_social | varchar(255) | Sim | Razão social da empresa |
| nome_fantasia | varchar(255) | Não | Nome fantasia |
| cnpj | varchar(18) | Sim | Único. Formato: 00.000.000/0001-00. Validação de dígito verificador (RN-038) |
| representante_legal | varchar(255) | Não | Nome do representante legal da empresa |
| email | varchar(255) | Não | Email de contato |
| telefone | varchar(20) | Não | Telefone de contato |
| endereco | varchar(255) | Não | Endereço completo |
| cidade | varchar(100) | Não | Cidade |
| uf | varchar(2) | Não | Estado (UF) |
| cep | varchar(10) | Não | CEP |
| observacoes | text | Não | Observações |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |
| deleted_at | datetime | Não | Soft delete |

**Relacionamentos:**
- hasMany: Contrato

#### Entidade: Secretaria

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| nome | varchar(255) | Sim | Nome da secretaria/órgão |
| sigla | varchar(20) | Não | Sigla (ex: SMS, SME) |
| responsavel | varchar(255) | Não | Nome do responsável |
| email | varchar(255) | Não | Email de contato |
| telefone | varchar(20) | Não | Telefone |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- hasMany: Contrato

#### Entidade: Fiscal

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| contrato_id | bigint | Sim | FK → contratos |
| nome | varchar(255) | Sim | Nome completo do fiscal |
| matricula | varchar(50) | Sim | Matrícula funcional do servidor |
| cargo | varchar(255) | Sim | Cargo do fiscal |
| email | varchar(255) | Não | Email institucional |
| data_inicio | date | Sim | Data em que assumiu a fiscalização |
| data_fim | date | Não | Data em que deixou a fiscalização (null = fiscal atual) |
| is_atual | boolean | Sim | Default: true. Apenas um fiscal atual por contrato (RN-034) |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- belongsTo: Contrato

**Regras:**
- Ao cadastrar novo fiscal, o anterior é desativado automaticamente (RN-034)
- Histórico nunca deletado (RN-035)

#### Entidade: Aditivo (Expandida — Módulo 4)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| contrato_id | bigint | Sim | FK → contratos |
| numero | varchar(50) | Sim | Número de identificação (ex: 1º Termo Aditivo) |
| numero_sequencial | int | Sim | Sequencial automático por contrato: 1, 2, 3... (RN-091) |
| tipo | enum(TipoAditivo) | Sim | prazo, valor, prazo_e_valor, supressao, reequilibrio, alteracao_clausula, misto (RN-088) |
| status | enum(StatusAditivo) | Sim | Default: vigente |
| data_assinatura | date | Sim | Data de assinatura do aditivo |
| data_inicio_vigencia | date | Não* | Data em que o aditivo entra em vigor (*obrigatório se alterar prazo ou valor — RN-092) |
| nova_data_fim | date | Não* | Nova data fim do contrato (*obrigatório se tipo alterar prazo — RN-010) |
| valor_anterior_contrato | decimal(15,2) | Não* | Snapshot do valor_global antes do aditivo (*preenchido automaticamente — RN-104) |
| valor_acrescimo | decimal(15,2) | Não* | Valor do acréscimo (sempre positivo — *obrigatório para tipos: valor, prazo_e_valor, misto, reequilibrio — RN-093) |
| valor_supressao | decimal(15,2) | Não* | Valor da supressão (sempre positivo — *obrigatório para tipos: supressao, misto — RN-094) |
| percentual_acumulado | decimal(5,2) | Sim | Percentual acumulado de acréscimos até este aditivo (RN-097). Calculado e armazenado como snapshot. Default: 0 |
| fundamentacao_legal | text | Sim | Base legal do aditivo (art. 65 Lei 8.666 ou art. 125 Lei 14.133 — RN-089) |
| justificativa | text | Sim | Justificativa geral do aditivo |
| justificativa_tecnica | text | Sim | Justificativa técnica detalhada (RN-090) |
| justificativa_excesso_limite | text | Não* | *Obrigatório se percentual ultrapassar limite e modo não-bloqueante (RN-102) |
| parecer_juridico_obrigatorio | boolean | Sim | Default: false. True automaticamente se acréscimo > 10% do valor atual (RN-096) |
| motivo_reequilibrio | text | Não* | *Obrigatório para tipo reequilibrio (RN-095) |
| indice_utilizado | varchar(50) | Não* | IPCA, INCC, IGPM, outro (*obrigatório para tipo reequilibrio — RN-095) |
| valor_anterior_reequilibrio | decimal(15,2) | Não* | Valor de referência antes do reequilíbrio (*obrigatório para tipo reequilibrio — RN-095) |
| valor_reajustado | decimal(15,2) | Não* | Valor após aplicação do índice (*obrigatório para tipo reequilibrio — RN-095) |
| observacoes | text | Não | Observações gerais |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |
| deleted_at | datetime | Não | Soft delete |

**Relacionamentos:**
- belongsTo: Contrato
- hasMany: Documento (polimórfico)
- morphMany: HistoricoAlteracao (auditoria via historico_alteracoes — ADR-009)

**Regras de imutabilidade:**
- Aditivo salvo não pode ser editado (apenas admin pode cancelar — RN-116)
- Toda operação gera registro de auditoria (RN-117)

#### Entidade: Documento (Expandida — Módulo 5)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| documentable_type | varchar(255) | Sim | Polimórfico (Contrato ou Aditivo) |
| documentable_id | bigint | Sim | ID da entidade pai |
| tipo_documento | enum(TipoDocumentoContratual) | Sim | Classificação (RN-040) — 12 valores |
| nome_original | varchar(255) | Sim | Nome original do arquivo enviado pelo usuário |
| nome_arquivo | varchar(255) | Sim | Nome padronizado gerado pelo sistema (RN-121) |
| descricao | varchar(255) | Não | Descrição opcional do documento |
| caminho | varchar(500) | Sim | Caminho no storage |
| tamanho | bigint | Sim | Tamanho em bytes |
| mime_type | varchar(100) | Sim | Tipo MIME (application/pdf) |
| versao | int | Sim | Versão do documento. Default: 1 (RN-120) |
| is_versao_atual | boolean | Sim | Default: true. False para versões anteriores (RN-120) |
| uploaded_by | bigint | Sim | FK → users (quem fez upload) (RN-042) |
| hash_integridade | varchar(64) | Não | Hash SHA-256 do arquivo, calculado no upload (ADR-047, RN-220/RN-221) |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |
| deleted_at | datetime | Não | Soft delete — exclusão lógica (RN-134) |

**Relacionamentos:**
- morphTo: documentable (Contrato ou Aditivo)
- belongsTo: User (uploaded_by)
- hasMany: LogAcessoDocumento

**Índices:**
- Composto em (documentable_type + documentable_id) — performance das consultas por contrato/aditivo
- Índice em tipo_documento — filtro por tipo
- Índice em is_versao_atual — listagem de versões atuais

#### Entidade: LogAcessoDocumento (Nova — Módulo 5)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| documento_id | bigint | Sim | FK → documentos |
| user_id | bigint | Sim | FK → users (quem realizou a ação) |
| acao | enum(AcaoLogDocumento) | Sim | upload, download, substituicao, exclusao, visualizacao (RN-122) |
| ip_address | varchar(45) | Não | IP do usuário no momento da ação |
| created_at | datetime | Sim | Automático (imutável — append-only) |

**Relacionamentos:**
- belongsTo: Documento
- belongsTo: User (user_id)

**Regras:**
- Tabela imutável (append-only) — nunca editar ou deletar (consistente com ADR-009)
- Todo acesso a documento gera registro (RN-122)

#### Entidade: ExecucaoFinanceira

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| contrato_id | bigint | Sim | FK → contratos |
| descricao | varchar(255) | Sim | Descrição da execução/medição |
| valor | decimal(15,2) | Sim | Valor executado |
| data_execucao | date | Sim | Data da execução/pagamento |
| numero_nota_fiscal | varchar(50) | Não | Número da nota fiscal |
| observacoes | text | Não | Observações |
| registrado_por | bigint | Sim | FK → users (quem registrou) |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- belongsTo: Contrato
- belongsTo: User (registrado_por)

#### Entidade: HistoricoAlteracao

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| auditable_type | varchar(255) | Sim | Polimórfico (Contrato, Fornecedor, etc.) |
| auditable_id | bigint | Sim | ID da entidade alterada |
| campo_alterado | varchar(255) | Sim | Nome do campo que foi modificado |
| valor_anterior | text | Não | Valor antes da alteração (null em criação) |
| valor_novo | text | Não | Valor após a alteração (null em exclusão) |
| user_id | bigint | Sim | FK → users (quem alterou) |
| role_nome | varchar(100) | Sim | Nome do perfil (role) do usuário no momento da ação (RN-340, RN-341). Snapshot imutável — não muda se o usuário mudar de perfil depois |
| ip_address | varchar(45) | Não | IP do usuário no momento da alteração |
| created_at | datetime | Sim | Automático (imutável — RN-037) |

**Relacionamentos:**
- morphTo: auditable (Contrato, Fornecedor, etc.)
- belongsTo: User (user_id)

**Regras:**
- Registros imutáveis — nunca editar ou deletar (RN-037)
- Campo `role_nome` é um snapshot do perfil no momento da ação — nunca atualizar retroativamente (RN-341)
- Toda inserção deve capturar `$user->role->nome` no momento do evento, não via FK (garante imutabilidade histórica)
- Usado para auditoria, Tribunal de Contas, segurança jurídica

#### Entidade: Alerta

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| contrato_id | bigint | Sim | FK → contratos |
| tipo_evento | enum(TipoEventoAlerta) | Sim | vencimento_vigencia, termino_aditivo, prazo_garantia, prazo_execucao_fisica |
| prioridade | enum(PrioridadeAlerta) | Sim | Determinada automaticamente (RN-043) |
| status | enum(StatusAlerta) | Sim | Default: pendente. Ciclo: pendente → enviado → visualizado → resolvido |
| dias_para_vencimento | int | Sim | Dias restantes no momento da geração |
| dias_antecedencia_config | int | Sim | Prazo configurado que disparou este alerta (ex: 120, 90, 60, 30, 15, 7) |
| data_vencimento | date | Sim | Data de vencimento do contrato/aditivo |
| data_disparo | datetime | Sim | Data/hora em que o alerta foi gerado pelo motor |
| mensagem | text | Sim | Mensagem descritiva do alerta |
| tentativas_envio | int | Sim | Default: 0. Contagem de tentativas de envio de notificação |
| visualizado_por | bigint | Não | FK → users (quem visualizou) |
| visualizado_em | datetime | Não | Data/hora da visualização |
| resolvido_por | bigint | Não | FK → users (quem resolveu) |
| resolvido_em | datetime | Não | Data/hora da resolução |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- belongsTo: Contrato
- belongsTo: User (visualizado_por)
- belongsTo: User (resolvido_por)
- hasMany: LogNotificacao

**Unique constraint:** contrato_id + tipo_evento + dias_antecedencia_config (RN-016)

#### Entidade: ConfiguracaoAlerta

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| dias_antecedencia | int | Sim | Quantos dias antes do vencimento para disparar alerta |
| prioridade | enum(PrioridadeAlerta) | Sim | Prioridade padrão associada a este prazo |
| is_ativo | boolean | Sim | Default: true. Permite desativar um prazo sem deletar |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- Nenhum (tabela de configuração)

**Valores padrão (seeder):**

| dias_antecedencia | prioridade |
|---|---|
| 120 | informativo |
| 90 | informativo |
| 60 | atencao |
| 30 | atencao |
| 15 | urgente |
| 7 | urgente |

#### Entidade: LogNotificacao

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| alerta_id | bigint | Sim | FK → alertas |
| canal | enum(CanalNotificacao) | Sim | email, sistema |
| destinatario | varchar(255) | Sim | Email ou identificação do destinatário |
| data_envio | datetime | Sim | Data/hora da tentativa de envio |
| sucesso | boolean | Sim | Se a notificação foi enviada com sucesso |
| resposta_gateway | text | Não | Resposta do gateway de envio (para debug) |
| created_at | datetime | Sim | Automático |

**Relacionamentos:**
- belongsTo: Alerta

**Regras:**
- Registra cada tentativa de envio (RN-049)
- Em caso de falha, retry com backoff exponencial — máximo 3 tentativas (RN-050)
- Nunca deletar logs de notificação (auditoria de envios)

#### Entidade: DashboardAgregado

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| data_referencia | date | Sim | Data de referência da agregação |
| tipo_metrica | varchar(100) | Sim | Tipo da métrica (financeiro, risco, vencimentos, secretaria, score, tendencia, fornecedor) |
| chave | varchar(255) | Não | Chave de agrupamento (ex: secretaria_id, fornecedor_id, mes) |
| dados | json | Sim | Dados agregados em formato JSON |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- Nenhum (tabela de agregação independente)

**Regras:**
- Atualizada diariamente via AgregarDashboardCommand (RN-084)
- Dados anteriores podem ser sobrescritos na mesma data_referencia + tipo_metrica + chave
- Usado exclusivamente pelo DashboardService para alimentar o Painel Executivo
- Índice composto em (data_referencia, tipo_metrica, chave)

#### Entidade: Role (Nova — Módulo 7)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| nome | varchar(100) | Sim | Identificador único: `administrador_geral`, `gestor_contrato`, etc. |
| descricao | varchar(255) | Sim | Nome amigável exibido na UI |
| is_padrao | boolean | Sim | Default: false. True = perfil padrão do sistema (não deletável). 8 perfis padrão via seeder |
| is_ativo | boolean | Sim | Default: true. Permite desativar um perfil sem deletar |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- hasMany: User
- belongsToMany: Permission (via `role_permissions`)

**Regras:**
- 8 perfis padrão criados via RoleSeeder (RN-304)
- Perfis com `is_padrao = true` não podem ser deletados
- Admin pode criar perfis customizados adicionais

#### Entidade: Permission (Nova — Módulo 7)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| nome | varchar(100) | Sim | Identificador único: `contrato.editar`, `aditivo.aprovar` (RN-302) |
| descricao | varchar(255) | Não | Descrição da permissão para UI |
| grupo | varchar(50) | Sim | Agrupamento: contrato, aditivo, financeiro, documento, usuario, configuracao, relatorio |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- belongsToMany: Role (via `role_permissions`)
- belongsToMany: User (via `user_permissions`)

**Regras:**
- Formato obrigatório: `{recurso}.{ação}` (RN-302)
- Permissões criadas via PermissionSeeder

#### Entidade: UserPermission (Nova — Módulo 7)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| user_id | bigint | Sim | FK → users |
| permission_id | bigint | Sim | FK → permissions |
| expires_at | datetime | Não | Null = permanente, data = temporária (RN-330) |
| concedido_por | bigint | Sim | FK → users (admin que concedeu) |
| created_at | datetime | Sim | Automático |

**Relacionamentos:**
- belongsTo: User (user_id)
- belongsTo: Permission
- belongsTo: User (concedido_por)

**Regras:**
- Permissões com `expires_at` < agora são revogadas automaticamente por job diário (RN-333)
- Toda concessão/revogação é registrada em auditoria (RN-332)

#### Entidade: UserSecretaria (Nova — Módulo 7, pivot)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| user_id | bigint | Sim | FK → users |
| secretaria_id | bigint | Sim | FK → secretarias |
| created_at | datetime | Sim | Automático |

**Relacionamentos:**
- belongsTo: User
- belongsTo: Secretaria

**Regras:**
- Define o escopo de acesso por secretaria (RN-325)
- Perfis estratégicos (administrador_geral, controladoria, gabinete) não usam esta tabela — acessam todas (RN-327)

#### Entidade: WorkflowAprovacao (Nova — Módulo 7)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| aprovavel_type | varchar(255) | Sim | Polimórfico (App\Models\Aditivo, etc.) |
| aprovavel_id | bigint | Sim | ID da entidade em aprovação |
| etapa | enum(EtapaWorkflow) | Sim | Etapa do fluxo (solicitacao, aprovacao_secretario, parecer_juridico, validacao_controladoria, homologacao) |
| etapa_ordem | int | Sim | Ordem numérica: 1, 2, 3, 4, 5 |
| role_responsavel_id | bigint | Sim | FK → roles (perfil que deve aprovar esta etapa) |
| user_id | bigint | Não | FK → users (quem aprovou/reprovou). Null = pendente |
| status | enum(StatusAprovacao) | Sim | Default: pendente (RN-336) |
| parecer | text | Não | Comentário/justificativa. Obrigatório em reprovação (RN-338) |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- morphTo: aprovavel (Aditivo, etc.)
- belongsTo: Role (role_responsavel_id)
- belongsTo: User (user_id)

**Regras:**
- 5 registros criados por aditivo solicitado (RN-335)
- Registros são imutáveis após aprovação/reprovação (append-only para integridade)
- Imutabilidade enforçada no nível de aplicação: WorkflowAprovacao não expõe métodos de update após status != pendente. Proteção adicional via DB trigger descrita em `memory/regras/auditoria-performance.md`
- Avanço sequencial obrigatório (RN-337)

#### Entidade: LoginLog (Nova — Segurança Expandida)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| user_id | bigint | Sim | FK → users |
| tenant_id | bigint | Sim | ID do tenant (referência ao banco master — RN-223, ADR-048). Armazenado como valor inteiro no banco tenant para correlação com o banco master |
| ip_address | varchar(45) | Sim | IP do usuário (IPv4 ou IPv6) |
| user_agent | varchar(500) | Não | User-Agent do navegador |
| success | boolean | Sim | Se o login foi bem-sucedido |
| created_at | datetime | Sim | Automático |

**Relacionamentos:**
- belongsTo: User

**Regras:**
- Tabela append-only — nunca editar ou deletar (ADR-048, RN-223)
- Sem `updated_at` (registros imutáveis)
- Campo `tenant_id` é armazenado como valor (não FK real) porque o banco tenant não tem FK para o banco master — padrão idêntico a TenantUser.user_id (ADR-042)
- Necessário para relatórios de auditoria cross-sessão e para correlação em investigações de segurança
- Registra toda tentativa de login (sucesso e falha)
- Usado para relatório de logs exportável (RN-222) e controle de lockout (ADR-046)

#### Entidade: ConfiguracaoLimiteAditivo (Nova — Módulo 4)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| tipo_contrato | enum(TipoContrato) | Sim | servico, obra, compra, locacao |
| percentual_limite | decimal(5,2) | Sim | Percentual máximo de acréscimo permitido (ex: 25.00, 50.00) |
| is_bloqueante | boolean | Sim | Default: true. Se true, bloqueia aditivo acima do limite. Se false, apenas alerta (RN-102) |
| is_ativo | boolean | Sim | Default: true |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- Nenhum (tabela de configuração)

**Valores padrão (seeder):**

| tipo_contrato | percentual_limite | is_bloqueante |
|---|---|---|
| servico | 25.00 | true |
| obra | 50.00 | true |
| compra | 25.00 | true |
| locacao | 25.00 | true |

**Regras:**
- Consultada pelo AditivoService ao calcular percentual acumulado (RN-097 a RN-103)
- Admin pode ajustar limites e modo bloqueante/alerta

#### Entidade: Tenant (Banco Master — Multi-Tenant)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| nome | varchar(100) | Sim | Nome da prefeitura-cliente |
| slug | varchar(50) | Sim | Único. Usado para identificação (ex: `prefeitura-abc`) |
| database_name | varchar(100) | Sim | Nome do banco de dados do tenant (ex: `vigiacontratos_prefeitura_abc`) |
| database_host | varchar(100) | Não | Host do banco (null = mesmo servidor). Default: null |
| is_ativo | boolean | Sim | Default: true. Permite suspender tenant sem deletar |
| plano | varchar(50) | Não | Plano contratado (ex: `basico`, `profissional`, `enterprise`) |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- hasMany: TenantUser

**Regras:**
- Reside no banco master (não no banco do tenant) — ADR-042, RN-200
- Slug é usado para resolver tenant via middleware SetTenantConnection (RN-201)
- Comando `tenant:create` provisiona novo tenant: cria banco, aplica migrations, seeder admin
- Comando `tenant:migrate` aplica migrations pendentes em todos os tenants ativos

#### Entidade: TenantUser (Banco Master — Multi-Tenant)

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| user_id | bigint (unsigned) | Sim | ID do usuário (referência lógica — não FK real, pois user está no banco do tenant) |
| tenant_id | bigint | Sim | FK → tenants |
| role | varchar(50) | Não | Role no contexto SaaS (ex: `owner`, `member`). Diferente do role RBAC interno |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- belongsTo: Tenant

**Regras:**
- Reside no banco master — associa usuários a tenants (ADR-042)
- user_id é referência lógica (não FK real), pois o User está no banco do tenant
- Um usuário pode pertencer a múltiplos tenants

---
