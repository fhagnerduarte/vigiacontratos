# Conhecimento — Modulo: Perfis de Usuario (RBAC)

> Extraido de `banco-de-conhecimento.md`. Carregar quando trabalhando no modulo de Perfis de Usuario / RBAC.
> Inclui: Regras (RN-300 a RN-342), Fluxo (15 — Workflow de Aprovacao).

---

## Regras de Negocio

### Módulo: Perfis de Usuário — Objetivos Estratégicos (Módulo 7)

O módulo de Perfis de Usuário é essencial para posicionar o sistema como **seguro e institucionalmente confiável**. Opera com RBAC (Role-Based Access Control) garantindo:

| Objetivo | Descrição |
|---|---|
| Separação de responsabilidades | Cada perfil tem escopo claro — quem opera não aprova, quem fiscaliza não altera valores |
| Rastreabilidade de ações | Toda ação registrada com usuário, perfil, data/hora, IP, valores anteriores e novos |
| Redução de risco de fraude | Segregação de função impede que uma pessoa tenha controle total sobre um fluxo |
| Controle administrativo formal | Fluxos de aprovação com registro formal em cada etapa — auditável pelo TCE |

### Módulo: Perfis de Usuário — RBAC (Módulo 7)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-300 | O sistema opera com RBAC (Role-Based Access Control) via tabela `roles` dinâmica | Permissões por perfil (role), por secretaria e por ação (visualizar, criar, editar, excluir, aprovar). Admin pode criar perfis customizados |
| RN-301 | Cada usuário possui exatamente um perfil (role) ativo | Relação belongsTo: user → role. Perfil é obrigatório |
| RN-302 | Permissões são granulares por recurso e ação | Formato: `{recurso}.{ação}`. Ex: `contrato.editar`, `aditivo.aprovar`, `financeiro.registrar_empenho` |
| RN-303 | Verificação de permissão: `$user->hasPermission('contrato.editar')` | Verifica via role_permissions + permissão individual do usuário (user_permissions) |
| RN-304 | O sistema fornece 8 perfis padrão via seeder (não deletáveis) | administrador_geral, controladoria, secretario, gestor_contrato, fiscal_contrato, financeiro, procuradoria, gabinete. Campo `is_padrao = true` |

### Módulo: Perfis de Usuário — Permissões por Perfil (Módulo 7)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-305 | Administrador Geral tem acesso total ao sistema | Criar/editar/desativar usuários, definir permissões, acessar todas secretarias, visualizar todos contratos, acessar logs de auditoria, configurar parâmetros de risco e alertas globais |
| RN-306 | Administrador Geral não pode alterar contratos sem registro de log | Toda ação gera auditoria, inclusive de admin. Nunca excluir histórico sem trilha |
| RN-307 | Controladoria Interna visualiza todos os contratos e painel de risco completo | Pode gerar relatórios TCE, inserir observações técnicas, registrar parecer interno |
| RN-308 | Controladoria Interna não pode alterar dados financeiros nem excluir documentos | Perfil estratégico — leitura + parecer. Essencial para credibilidade do sistema |
| RN-309 | Secretário Municipal tem acesso restrito à própria secretaria | Visualizar contratos da sua pasta, acompanhar risco, receber alertas, visualizar documentos |
| RN-310 | Secretário Municipal aprova solicitações de aditivo no workflow | Etapa 2 do workflow de aprovação |
| RN-311 | Secretário Municipal não pode ver contratos de outras secretarias nem alterar contratos homologados | Isolamento por secretaria |
| RN-312 | Gestor de Contrato é perfil operacional | Cadastrar contrato, atualizar informações, anexar documentos, solicitar aditivos, atualizar status de execução, inserir relatórios mensais |
| RN-313 | Gestor de Contrato não pode aprovar aditivo sozinho nem excluir contrato homologado | Aditivo segue workflow de aprovação obrigatório |
| RN-314 | Fiscal de Contrato registra relatórios de fiscalização, ocorrências, atrasos e inconformidades | Perfil técnico de acompanhamento. Pode anexar fotos e documentos |
| RN-315 | Fiscal de Contrato não pode alterar valores nem aprovar aditivos | Segregação: fiscal acompanha, não decide |
| RN-316 | Financeiro registra empenhos, saldo contratual, pagamentos e relatórios financeiros | Acesso restrito à parte financeira dos contratos |
| RN-317 | Financeiro não pode alterar dados jurídicos nem aprovar prorrogações | Segregação entre financeiro e jurídico |
| RN-318 | Procuradoria Jurídica visualiza contratos, analisa aditivos, emite parecer jurídico | Valida prorrogações, aprova juridicamente aditivos |
| RN-319 | Procuradoria Jurídica não pode alterar valores contratuais | Segregação: jurídico valida, não executa |
| RN-320 | Gabinete tem acesso executivo simplificado (somente leitura) | Dashboard executivo, contratos críticos, mapa de risco, relatório consolidado. Sem acesso operacional |

### Módulo: Perfis de Usuário — Permissão por Secretaria (Módulo 7)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-325 | Usuários podem ter acesso vinculado a uma ou mais secretarias | Relação N:N entre User e Secretaria via tabela `user_secretarias` |
| RN-326 | Secretário, Gestor e Fiscal só acessam contratos das secretarias vinculadas | Filtro automático em todas as queries (Eloquent Global Scope por secretaria) |
| RN-327 | Administrador Geral, Controladoria e Gabinete acessam todas as secretarias | Sem restrição de secretaria para perfis estratégicos |

### Módulo: Perfis de Usuário — Permissão Temporária (Módulo 7)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-330 | Permissões temporárias possuem data de expiração (`expires_at`) | Após a data, permissão é revogada automaticamente por job diário |
| RN-331 | Admin pode designar substituto com acesso temporário | Permissão individual na tabela `user_permissions` com `expires_at` + `concedido_por` |
| RN-332 | Expiração registrada em log de auditoria | Sistema registra revogação automática em `historico_alteracoes` |
| RN-333 | Job diário verifica e revoga permissões expiradas | Command `permissoes:verificar-expiradas` integrado ao scheduler |

### Módulo: Perfis de Usuário — Workflow de Aprovação (Módulo 7)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-335 | Aditivos seguem fluxo de aprovação sequencial obrigatório | Gestor → Secretário → Jurídico → Controladoria → Homologação (5 etapas) |
| RN-336 | Cada etapa registra: responsável, data/hora, parecer e status | Tabela `workflow_aprovacoes` com registro formal (polimórfica) |
| RN-337 | Uma etapa só avança se a anterior foi aprovada | Bloqueio sequencial — cada perfil vê apenas itens pendentes para sua etapa |
| RN-338 | Reprovação retorna ao solicitante com motivo obrigatório | Gestor recebe notificação de retorno com justificativa |
| RN-339 | O workflow é configurável por tipo de operação | V1: obrigatório para aditivos. Extensível para outros fluxos |

### Módulo: Perfis de Usuário — Logs de Auditoria Expandidos (Módulo 7)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-340 | Cada ação registra: usuário, perfil, data/hora, IP, ação, valor anterior, valor novo | Expandir `historico_alteracoes` existente com campo `role_nome` |
| RN-341 | Log inclui o perfil (role) do usuário no momento da ação | Campo `role_nome` no registro de `historico_alteracoes` para rastreabilidade do papel exercido |
| RN-342 | Logs são imutáveis (append-only) | Consistente com ADR-009 e RN-037 |

**Exemplo concreto de log de auditoria (protege o prefeito):**

```
Usuário: João Silva
Perfil: Gestor de Contrato
Ação: Alteração de valor contratual
Campo: valor_global
Antes: R$ 500.000,00
Depois: R$ 550.000,00
Data: 18/02/2026 14:35
IP: 10.0.0.15
```

### Módulo: Perfis de Usuário — Matriz de Permissões Granulares (Módulo 7)

Cada ação no sistema é controlada individualmente por recurso. Formato: `{recurso}.{ação}`.

| Recurso | Visualizar | Criar | Editar | Excluir | Aprovar |
|---|---|---|---|---|---|
| Contrato | `contrato.visualizar` | `contrato.criar` | `contrato.editar` | `contrato.excluir` | — |
| Aditivo | `aditivo.visualizar` | `aditivo.criar` | `aditivo.editar` | `aditivo.excluir` | `aditivo.aprovar` |
| Fornecedor | `fornecedor.visualizar` | `fornecedor.criar` | `fornecedor.editar` | `fornecedor.excluir` | — |
| Documento | `documento.visualizar` | `documento.criar` | `documento.editar` | `documento.excluir` | — |
| Financeiro | `financeiro.visualizar` | `financeiro.registrar_empenho` | `financeiro.editar` | — | — |
| Fiscal | `fiscal.visualizar` | `fiscal.criar` | `fiscal.editar` | — | — |
| Relatório | `relatorio.visualizar` | `relatorio.gerar` | — | — | — |
| Usuário | `usuario.visualizar` | `usuario.criar` | `usuario.editar` | `usuario.desativar` | — |
| Configuração | `configuracao.visualizar` | — | `configuracao.editar` | — | — |
| Auditoria | `auditoria.visualizar` | — | — | — | — |
| Parecer | `parecer.visualizar` | `parecer.emitir` | — | — | — |
| Workflow | `workflow.visualizar` | — | — | — | `workflow.aprovar` |

**Matriz Perfil × Recurso (permissões padrão via RolePermissionSeeder):**

| Recurso.Ação | Admin | Controladoria | Secretário | Gestor | Fiscal | Financeiro | Procuradoria | Gabinete |
|---|---|---|---|---|---|---|---|---|
| contrato.visualizar | X | X | X* | X* | X* | X* | X | X |
| contrato.criar | X | — | — | X* | — | — | — | — |
| contrato.editar | X | — | — | X* | — | — | — | — |
| contrato.excluir | X | — | — | — | — | — | — | — |
| aditivo.visualizar | X | X | X* | X* | X* | — | X | — |
| aditivo.criar | X | — | — | X* | — | — | — | — |
| aditivo.aprovar | X | X | X* | — | — | — | X | — |
| documento.criar | X | — | — | X* | X* | — | — | — |
| documento.excluir | X | — | — | — | — | — | — | — |
| financeiro.visualizar | X | X | X* | X* | — | X* | — | X |
| financeiro.registrar_empenho | X | — | — | — | — | X* | — | — |
| fiscal.criar | X | — | — | X* | — | — | — | — |
| relatorio.gerar | X | X | — | — | — | X | — | — |
| parecer.emitir | X | X | — | — | — | — | X | — |
| usuario.criar | X | — | — | — | — | — | — | — |
| configuracao.editar | X | — | — | — | — | — | — | — |
| auditoria.visualizar | X | X | — | — | — | — | — | — |

`X` = Acesso total | `X*` = Restrito às secretarias vinculadas (via `user_secretarias`) | `—` = Sem acesso

### Como documentar regras:
1. Use ID sequencial (RN-XXX)
2. A regra deve ser **clara e verificável** — sem ambiguidade
3. Inclua fórmulas quando houver cálculos
4. Documente exceções e casos especiais
5. Referencie entidades pelo nome do glossário

---

## Fluxos de Negocio

### Fluxo 15: Workflow de Aprovação de Aditivo (Módulo 7)

```
[1. Gestor de Contrato solicita aditivo]
   Preenche dados do aditivo + justificativa
   Sistema cria 5 registros de WorkflowAprovacao (etapas 1-5)
   Etapa 1 (solicitacao) = status aprovado (auto, solicitante)
       │
       ▼
[2. ETAPA 2 — Aprovação do Secretário]
   Secretário da pasta recebe notificação
   Visualiza aditivo + parecer do gestor
   Aprova (avança) ou Reprova (retorna ao gestor com motivo)
       │
       ▼
[3. ETAPA 3 — Parecer Jurídico]
   Procuradoria recebe notificação
   Analisa legalidade do aditivo
   Emite parecer: aprovado ou reprovado com fundamentação
       │
       ▼
[4. ETAPA 4 — Validação da Controladoria]
   Controladoria recebe notificação
   Valida conformidade orçamentária e administrativa
   Aprova ou reprova com justificativa
       │
       ▼
[5. ETAPA 5 — Homologação]
   Administrador Geral recebe notificação
   Homologa formalmente o aditivo
   Aditivo muda status para vigente
       │
       ▼
[6. Aditivo aprovado e registrado]
   Valores do contrato atualizados
   Histórico de aprovações registrado (imutável)
   Notificação ao gestor: aditivo homologado
```

**Regra de reprovação:** Em qualquer etapa, reprovação retorna ao gestor (etapa 1) com motivo obrigatório. Gestor pode corrigir e reenviar, gerando novo ciclo de aprovação.

**Regras associadas:** RN-335 a RN-339

---
