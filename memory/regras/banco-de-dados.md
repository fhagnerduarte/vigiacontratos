# Regras — Banco de Dados

> Extraído de `banco-de-regras.md`. Carregar ao criar migrations, definir colunas ou trabalhar com schema.
> Define tipos de dados obrigatórios, índices e lista de tabelas do sistema.

---

## Regras Gerais

- Migrations sempre com **rollback funcional** (`down()` implementado)
- Tabelas nomeadas em português plural snake_case
- Foreign keys com **cascade rules explícitas**
- Soft deletes **obrigatório** em: contratos, aditivos, fornecedores, documentos
- `timestamps()` sempre incluído
- Tabelas de auditoria (`historico_alteracoes`) são **imutáveis** — sem update/delete
- Tabela `log_notificacoes` é **append-only** — sem update/delete
- Tabela `log_acesso_documentos` é **append-only** — sem update/delete (RN-122, ADR-035)

## Índices Obrigatórios

- **Índice** em `contratos.data_fim` (performance do motor de monitoramento)
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

## Tipos de Dados

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

## Tabelas do Sistema

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
