# Conhecimento — Módulo: Contratos

> Extraído de `banco-de-conhecimento.md`. Carregar quando trabalhando no módulo de Contratos.
> Inclui: Regras (RN-001 a RN-037), Fluxos (1, 2, 5, 6, 8), Fórmulas (dias para vencimento, valor global, prazo, percentual executado).

---

## Regras de Negócio

### Módulo: Contratos — Cadastro Inteligente

| ID | Regra | Detalhamento |
|---|---|---|
| RN-001 | Todo contrato deve ter um fornecedor vinculado | Não é possível cadastrar contrato sem fornecedor |
| RN-002 | Todo contrato deve ter uma secretaria responsável | A secretaria define o órgão que gerencia o contrato |
| RN-003 | A data de início deve ser anterior ou igual à data de fim | Validação obrigatória no cadastro. Bloqueio se data final for anterior à inicial |
| RN-004 | O valor global deve ser maior que zero | Contratos não podem ter valor zero ou negativo |
| RN-005 | O status inicial de um contrato é sempre "vigente" | Ao cadastrar, o status é definido automaticamente |
| RN-006 | Um contrato vencido não pode ser editado (apenas consultado) | Para alterar, criar aditivo ou novo contrato |
| RN-007 | O número do contrato deve ser único no sistema | Formato: NNN/AAAA (ex: 001/2026). Gerado automaticamente |
| RN-008 | O status muda automaticamente para "vencido" quando a data de vencimento é ultrapassada | Job/scheduler verifica diariamente |
| RN-023 | Contrato ativo sem número de processo administrativo não pode ser salvo | Validação obrigatória para status vigente |
| RN-024 | Contrato ativo sem fiscal designado não pode ser salvo | Todo contrato vigente precisa de pelo menos um fiscal com `is_atual = true` |
| RN-025 | Se modalidade for Dispensa → campo fundamento_legal é obrigatório | Exigência legal para contratações diretas por dispensa |
| RN-026 | Se modalidade for Inexigibilidade → justificativa e documento anexado são obrigatórios | Exigência legal para inexigibilidades |
| RN-027 | Se valor global ultrapassar limite de dispensa → sistema exibe alerta visual | Alerta informativo, não bloqueante — apenas sinaliza ao gestor |
| RN-028 | Se tipo de contrato for Obra → campo responsavel_tecnico é obrigatório | Obras exigem responsável técnico habilitado |
| RN-029 | Score de risco é calculado automaticamente ao cadastrar/atualizar contrato | Baseado em critérios objetivos (ver Fórmulas: Score de Risco) |
| RN-030 | O cadastro de contrato segue formulário multi-etapa (wizard) | 6 etapas: Identificação → Fornecedor → Financeiro → Vigência → Fiscal → Documentos |
| RN-031 | Prazo em meses é calculado automaticamente a partir das datas de início e fim | Campo derivado, não editável manualmente |

### Módulo: Contratos — Execução Financeira

| ID | Regra | Detalhamento |
|---|---|---|
| RN-032 | O percentual executado é calculado automaticamente | `(soma_execucoes / valor_global) * 100` — campo derivado |
| RN-033 | Alerta automático se valor executado ultrapassar valor contratado | Alerta visual + notificação ao gestor quando percentual > 100% |

### Módulo: Contratos — Fiscais

| ID | Regra | Detalhamento |
|---|---|---|
| RN-034 | Cada contrato deve ter exatamente um fiscal atual (`is_atual = true`) | Ao designar novo fiscal, o anterior é marcado como `is_atual = false` com data_fim preenchida |
| RN-035 | O histórico de trocas de fiscal é mantido automaticamente | Nunca deletar fiscal anterior — apenas desativar e registrar data_fim |

### Módulo: Contratos — Auditoria

| ID | Regra | Detalhamento |
|---|---|---|
| RN-036 | Toda alteração em contrato gera registro de auditoria | Log contém: campo alterado, valor anterior, valor novo, usuário, data/hora, IP |
| RN-037 | Registros de auditoria são imutáveis | Nunca editar ou deletar registros de historico_alteracoes |

## Fluxos de Negócio

### Fluxo 1: Cadastro Inteligente de Contrato (Multi-etapa)

```
[1. Gestor clica em "Novo Contrato"]
       │
       ▼
[2. ETAPA 1 — IDENTIFICAÇÃO]
   Número (auto) / Ano / Processo administrativo
   Modalidade / Tipo / Secretaria / Unidade gestora
   Objeto (descrição)
       │
       ▼
[3. ETAPA 2 — FORNECEDOR]
   Selecionar fornecedor existente OU cadastrar novo
   (CNPJ validado automaticamente — RN-038)
       │
       ▼
[4. ETAPA 3 — FINANCEIRO]
   Valor global / Valor mensal / Tipo de pagamento
   Fonte de recurso / Dotação orçamentária / Empenho
   Categoria (essencial/não essencial)
   Categoria de serviço
       │
       ▼
[5. ETAPA 4 — VIGÊNCIA]
   Data início / Data fim
   Prazo em meses (calculado — RN-031)
   Prorrogação automática? (sim/não)
       │
       ▼
[6. ETAPA 5 — FISCAL]
   Nome / Matrícula / Cargo / Email institucional
   (Obrigatório para contrato ativo — RN-024)
       │
       ▼
[7. ETAPA 6 — DOCUMENTOS]
   Upload múltiplo (RN-039)
   Classificação por tipo (RN-040)
   Validações por modalidade (RN-025, RN-026)
       │
       ▼
[8. VALIDAÇÃO COMPLETA]
   Sistema valida todas as etapas
   (RN-001 a RN-005, RN-023 a RN-031)
       │
   ┌───┴───┐
   ▼       ▼
[OK]    [Erro]
   │       │
   ▼       ▼
[9. Score de risco     [Retorna à etapa
 calculado (RN-029)]    com erro]
   │
   ▼
[10. Contrato salvo com status vigente + score de risco]
   │
   ▼
[11. Alertas serão gerados automaticamente pelo job diário]
```

**Regras associadas:** RN-001 a RN-005, RN-007, RN-023 a RN-031, RN-038, RN-039, RN-040

### Fluxo 2: Score de Risco (calculado automaticamente)

```
[1. Contrato cadastrado ou atualizado]
       │
       ▼
[2. Sistema avalia critérios de risco]
       │
       ├── Sem fiscal designado?        → +20 pontos
       ├── Sem documento anexado?        → +20 pontos
       ├── Valor > R$ 1.000.000?         → +10 pontos
       ├── Modalidade sensível?          → +10 pontos
       │   (dispensa, inexigibilidade)
       ├── Sem fundamento legal?         → +10 pontos
       │   (quando dispensa/inexigibilidade)
       ├── Contrato sem processo?        → +10 pontos
       └── Vigência > 24 meses?          → +5 pontos
       │
       ▼
[3. Totaliza score]
       │
   ┌───┼───┐
   ▼   ▼   ▼
[0-29] [30-59] [60+]
  🟢     🟡     🔴
Baixo  Médio   Alto
```

**Regras associadas:** RN-029

### Fluxo 5: Troca de Fiscal

```
[1. Gestor acessa contrato → aba Fiscal]
       │
       ▼
[2. Clica em "Trocar Fiscal"]
       │
       ▼
[3. Preenche dados do novo fiscal]
   (nome, matrícula, cargo, email)
       │
       ▼
[4. Sistema valida dados]
       │
   ┌───┴───┐
   ▼       ▼
[OK]    [Erro]
   │       │
   ▼       ▼
[5. Fiscal anterior:    [Exibe erros]
 is_atual = false
 data_fim = agora]
   │
   ▼
[6. Novo fiscal:
 is_atual = true
 data_inicio = agora]
   │
   ▼
[7. Registro de auditoria gerado (RN-036)]
```

**Regras associadas:** RN-024, RN-034, RN-035, RN-036

### Fluxo 6: Registro de Execução Financeira

```
[1. Gestor acessa contrato → aba Financeiro]
       │
       ▼
[2. Clica em "Registrar Execução"]
       │
       ▼
[3. Preenche: descrição, valor, data, nota fiscal]
       │
       ▼
[4. Sistema valida e salva]
       │
       ▼
[5. Percentual executado recalculado (RN-032)]
       │
   ┌───┴───┐
   ▼       ▼
[≤ 100%]  [> 100%]
   │         │
   ▼         ▼
[Normal]  [Alerta: valor executado
           ultrapassou contratado (RN-033)]
```

**Regras associadas:** RN-032, RN-033

### Fluxo 8: Bloqueio Preventivo (Contrato Vencido)

```
[1. Contrato atinge data_fim sem renovação]
       │
       ▼
[2. Job diário detecta: dias_restantes ≤ 0]
       │
       ▼
[3. Status do contrato → vencido (RN-008)]
       │
       ▼
[4. Contrato marcado como IRREGULAR (RN-046)]
   ├── Destaque vermelho no painel
   ├── Badge de status vermelho na listagem
   └── Alerta urgente gerado/mantido
       │
       ▼
[5. Bloqueios ativados:]
   ├── Edição do contrato bloqueada (RN-006)
   └── Aditivo retroativo exige justificativa (RN-052)
       │
       ▼
[6. Para regularizar:]
   └── Gestor deve registrar aditivo com justificativa_retroativa
       ou encerrar o contrato formalmente
```

**Regras associadas:** RN-006, RN-008, RN-046, RN-052

## Fórmulas e Cálculos

### Fórmula: Dias para Vencimento

```
dias_para_vencimento = data_fim (do contrato) - data_atual
```

| Variável | Descrição | Fonte |
|---|---|---|
| data_fim | Data de fim da vigência do contrato | Campo `data_fim` da tabela `contratos` |
| data_atual | Data do dia da verificação | `now()` |

### Fórmula: Valor Global Atualizado

```
valor_global_atualizado = valor_global_original + SUM(aditivos.valor_acrescimo) - SUM(aditivos.valor_supressao)
```

| Variável | Descrição | Fonte |
|---|---|---|
| valor_global_original | Valor original do contrato | Cadastro inicial |
| SUM(aditivos.valor_acrescimo) | Soma dos acréscimos de todos os aditivos vigentes | Tabela `aditivos` WHERE contrato_id = X AND status = vigente |
| SUM(aditivos.valor_supressao) | Soma das supressões de todos os aditivos vigentes | Tabela `aditivos` WHERE contrato_id = X AND status = vigente |

*Nota: campos `valor_acrescimo` e `valor_supressao` substituem o antigo `valor_aditivo` (ADR-026). Ambos são sempre positivos. Apenas aditivos com status vigente entram no cálculo.*

### Fórmula: Prazo em Meses

```
prazo_meses = DATEDIFF(MONTH, data_inicio, data_fim)
```

| Variável | Descrição | Fonte |
|---|---|---|
| data_inicio | Data de início da vigência | Campo `data_inicio` da tabela `contratos` |
| data_fim | Data de fim da vigência | Campo `data_fim` da tabela `contratos` |

*Nota: campo derivado, calculado automaticamente (RN-031).*

### Fórmula: Percentual Executado

```
percentual_executado = (SUM(execucoes_financeiras.valor) / valor_global) * 100
```

| Variável | Descrição | Fonte |
|---|---|---|
| execucoes_financeiras.valor | Soma de todas as execuções do contrato | Tabela `execucoes_financeiras` |
| valor_global | Valor global atualizado do contrato | Campo `valor_global` da tabela `contratos` |

*Nota: se percentual > 100%, gerar alerta automático (RN-033).*
