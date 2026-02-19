# Conhecimento — Módulo: Aditivos

> Extraído de `banco-de-conhecimento.md`. Carregar quando trabalhando no módulo de Aditivos.
> Inclui: Regras (RN-009 a RN-013, RN-088 a RN-117), Fluxos (4, 11), Fórmulas (percentual acumulado, critérios risco aditivos, indicadores dashboard).

---

## Regras de Negócio

### Módulo: Aditivos

| ID | Regra | Detalhamento |
|---|---|---|
| RN-009 | Um aditivo deve estar vinculado a um contrato vigente | Não se faz aditivo de contrato vencido ou cancelado |
| RN-010 | Aditivo de prazo deve informar nova data de fim | A nova data de fim deve ser posterior à data de fim atual |
| RN-011 | Aditivo de valor deve informar o valor do acréscimo ou supressão | Valor pode ser positivo (acréscimo) ou negativo (supressão) |
| RN-012 | Aditivo de prazo atualiza a data de vencimento do contrato pai | A data de fim do contrato é atualizada automaticamente |
| RN-013 | Aditivo de valor atualiza o valor global do contrato pai | O valor global é recalculado: valor_original + soma_acréscimos - soma_supressões |

### Módulo: Aditivos — Validação e Cadastro

| ID | Regra | Detalhamento |
|---|---|---|
| RN-088 | O tipo do aditivo é obrigatório | Não é possível salvar aditivo sem informar o tipo (TipoAditivo). Sistema bloqueia salvamento |
| RN-089 | A fundamentação legal é obrigatória em todos os aditivos | Campo `fundamentacao_legal` deve estar preenchido (art. 65 Lei 8.666 ou art. 125 Lei 14.133) |
| RN-090 | A justificativa técnica é obrigatória em todos os aditivos | Campo `justificativa_tecnica` deve descrever a necessidade técnica que motiva o aditivo |
| RN-091 | O número sequencial do aditivo é gerado automaticamente por contrato | Calculado como MAX(numero_sequencial) + 1 entre todos os aditivos do contrato |
| RN-092 | A data de início de vigência do aditivo deve ser igual ou posterior à data de assinatura | Campo `data_inicio_vigencia` obrigatório se o tipo alterar prazo ou valor |
| RN-093 | Aditivo de acréscimo de valor deve informar `valor_acrescimo` (positivo, maior que zero) | Campo obrigatório para tipos: valor, prazo_e_valor, misto, reequilibrio |
| RN-094 | Aditivo de supressão deve informar `valor_supressao` (positivo, maior que zero) | Campo obrigatório para tipos: supressao, misto |
| RN-095 | Aditivo de reequilíbrio exige campos específicos obrigatórios | Campos: motivo_reequilibrio, indice_utilizado (IPCA, INCC, IGPM etc.), valor_anterior_reequilibrio, valor_reajustado, documento comprobatório anexado |
| RN-096 | Parecer jurídico é obrigatório para aditivos com valor acrescido acima de 10% do valor atual | Campo `parecer_juridico_obrigatorio = true` automaticamente e documento do tipo `parecer_juridico` deve ser anexado |

### Módulo: Aditivos — Limites Legais

| ID | Regra | Detalhamento |
|---|---|---|
| RN-097 | O sistema controla o percentual acumulado de acréscimos em relação ao valor original | `percentual_acumulado = (SUM(valor_acrescimo de todos os aditivos vigentes) / valor_original_contrato) * 100` |
| RN-098 | Percentual limite para contratos de serviço/compra/locação é 25% do valor original | Configurável em `configuracoes_limite_aditivo` — padrão: 25% |
| RN-099 | Percentual limite para contratos de obra é 50% do valor original | Configurável em `configuracoes_limite_aditivo` — padrão: 50% |
| RN-100 | Ao ultrapassar o limite legal, o sistema emite alerta crítico visível no formulário | Exibe: percentual acumulado atual, limite configurado, diferença restante. Destaque vermelho |
| RN-101 | Se `is_bloqueante = true` na configuração do limite, o sistema impede o salvamento | O gestor não pode salvar o aditivo que ultrapassar o limite sem autorização adicional (configurável pelo admin) |
| RN-102 | Se `is_bloqueante = false`, o sistema exibe alerta mas permite continuar com justificativa obrigatória | Campo `justificativa_excesso_limite` obrigatório quando percentual ultrapassa o limite configurado |

### Módulo: Aditivos — Atualização Automática do Contrato Pai

| ID | Regra | Detalhamento |
|---|---|---|
| RN-103 | Ao salvar um aditivo, o contrato pai é atualizado automaticamente pelo AditivoService | Atualizações: valor_global recalculado, data_fim atualizada (se prazo), percentual_executado recalculado, score_risco recalculado, alertas reconfigurados |
| RN-104 | O valor anterior do contrato é registrado no aditivo antes da atualização | Campo `valor_anterior_contrato` = snapshot do valor_global do contrato no momento do aditivo |
| RN-105 | Todo aditivo salvo gera registro no historico_alteracoes do contrato pai | Audita: campo_alterado, valor_anterior, valor_novo, user_id, ip, data. Usa tabela `historico_alteracoes` existente (ADR-009) |

### Módulo: Aditivos — Score de Risco (Critérios de Aditivos)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-106 | Percentual acumulado de acréscimos acima de 20% eleva o score de risco do contrato | Critério adicional: +10 pontos no score de risco |
| RN-107 | Três ou mais aditivos registrados em intervalo de 12 meses elevam o score de risco | Critério adicional: +10 pontos no score de risco |
| RN-108 | Aditivo registrado nos últimos 30 dias antes do vencimento do contrato eleva o score de risco | Critério adicional: +5 pontos no score de risco |

### Módulo: Aditivos — Dashboard e Indicadores

| ID | Regra | Detalhamento |
|---|---|---|
| RN-109 | Dashboard de aditivos exibe 5 indicadores anuais | Total de aditivos no ano, valor total acrescido, % médio de acréscimo por contrato, ranking de contratos mais alterados, ranking de secretarias com mais aditivos |
| RN-110 | Total de aditivos no ano = COUNT(aditivos WHERE YEAR(data_assinatura) = YEAR(agora)) | Inclui todos os tipos |
| RN-111 | Valor total acrescido no ano = SUM(valor_acrescimo WHERE YEAR(data_assinatura) = YEAR(agora)) | Apenas acréscimos (não supressões) |
| RN-112 | % médio de acréscimo = AVG(percentual_acumulado) de contratos com aditivos no ano | Média dos snapshots de percentual_acumulado |
| RN-113 | Ranking de contratos mais alterados = TOP 10 contratos por número de aditivos | Ordenado DESC por COUNT(aditivos.contrato_id) |
| RN-114 | Ranking de secretarias com mais aditivos = TOP 5 secretarias | Ordenado DESC por COUNT(aditivos) via contratos da secretaria |

### Módulo: Aditivos — Segurança e Auditoria

| ID | Regra | Detalhamento |
|---|---|---|
| RN-115 | Apenas usuários com permissão `aditivo.criar` podem registrar aditivos | Validação via AditivoPolicy + verificação de permissão RBAC (RN-302). Perfis padrão com esta permissão: administrador_geral, gestor_contrato. Aditivos seguem workflow de aprovação obrigatório (RN-335) |
| RN-116 | Registro de aditivo é imutável após confirmação | Aditivo salvo não pode ser editado ou excluído (apenas admin pode cancelar, mudando status para `cancelado`) |
| RN-117 | Log completo de auditoria para toda operação em aditivos | Criação, cancelamento e qualquer alteração registrada em `historico_alteracoes` com campo, valor anterior, valor novo, usuário, IP |

## Fluxos de Negócio

### Fluxo 4: Aditivo Contratual (Completo)

```
[1. Gestor acessa contrato vigente]
       │
       ▼
[2. Clica em "Adicionar Aditivo"]
       │
       ▼
[3. Sistema carrega dados atuais do contrato]
   ├── Valor global atual
   ├── Data de fim atual
   ├── Percentual acumulado de aditivos anteriores
   ├── Limite legal configurado para o tipo de contrato
   └── Número sequencial próximo (MAX + 1)
       │
       ▼
[4. Usuário preenche dados do aditivo]
   ├── Tipo (obrigatório — RN-088)
   ├── Data de assinatura + Data de início de vigência (RN-092)
   ├── Fundamentação legal (obrigatório — RN-089)
   ├── Justificativa técnica (obrigatório — RN-090)
   ├── Campos financeiros conforme tipo:
   │   ├── Acréscimo: valor_acrescimo (RN-093)
   │   ├── Supressão: valor_supressao (RN-094)
   │   └── Reequilíbrio: campos específicos (RN-095)
   ├── Nova data fim (se prazo — RN-010)
   └── Documento de aditivo (upload PDF)
       │
       ▼
[5. Sistema calcula impactos em tempo real]
   ├── Novo valor global projetado
   ├── Nova data fim projetada
   ├── Percentual acumulado atualizado (RN-097)
   └── Percentual restante até o limite legal
       │
       ▼
[6. Sistema valida regras e limites legais]
   ├── Validações básicas (RN-009, RN-010, RN-011, RN-088 a RN-096)
   └── Limites legais (RN-097 a RN-102)
       │
   ┌───┴──────────────┐
   ▼                  ▼
[Dentro do         [Acima do
 limite / OK]       limite]
   │                  │
   ▼                  ▼
[Continua]         [Alerta crítico exibido (RN-100)]
                    ├── is_bloqueante? → Impede salvamento (RN-101)
                    └── Não bloqueante → Exige justificativa extra (RN-102)
       │
       ▼
[7. Aditivo salvo]
   ├── numero_sequencial gerado (RN-091)
   ├── valor_anterior_contrato registrado como snapshot (RN-104)
   └── percentual_acumulado calculado e armazenado (RN-097)
       │
       ▼
[8. Contrato pai atualizado automaticamente (RN-103, RN-105)]
   ├── valor_global recalculado (RN-012, RN-013)
   ├── data_fim atualizada se prazo alterado (RN-012)
   ├── percentual_executado recalculado
   └── Registro em historico_alteracoes (RN-105)
       │
       ▼
[9. Score de risco do contrato recalculado (RN-029, RN-106, RN-107, RN-108)]
   └── Inclui critérios de aditivos (percentual acumulado, frequência, proximidade)
       │
       ▼
[10. Alertas recalculados]
    ├── Alertas de prazo resolvidos se data_fim mudou (RN-017)
    └── Novos alertas programados para nova data_fim
```

**Regras associadas:** RN-009 a RN-013, RN-017, RN-029, RN-088 a RN-108, RN-115 a RN-117

### Fluxo 11: Reequilíbrio Econômico-Financeiro

```
[1. Gestor acessa contrato vigente]
       │
       ▼
[2. Clica em "Adicionar Aditivo" → seleciona tipo "Reequilíbrio"]
       │
       ▼
[3. Sistema exibe formulário específico de reequilíbrio (RN-095)]
   ├── Motivo do reequilíbrio (campo texto obrigatório)
   ├── Índice utilizado (IPCA, INCC, IGPM, outro — seleção)
   ├── Valor anterior ao reequilíbrio (snapshot automático)
   ├── Valor reajustado após aplicação do índice
   └── Documento comprobatório (upload obrigatório)
       │
       ▼
[4. Sistema calcula automaticamente]
   ├── valor_acrescimo = valor_reajustado - valor_anterior_reequilibrio
   └── Percentual de impacto = (valor_acrescimo / valor_anterior) * 100
       │
       ▼
[5. Sistema verifica percentual acumulado e limites legais (RN-097 a RN-102)]
       │
       ▼
[6. Fundamentação legal obrigatória (RN-089)]
   └── Ex: "Art. 65, II, 'd', da Lei 8.666/93 c/c Planilha de reajuste IPCA"
       │
       ▼
[7. Se acréscimo > 10% do valor atual → parecer jurídico obrigatório (RN-096)]
       │
       ▼
[8. Salvar aditivo de reequilíbrio (segue Fluxo 4 a partir do passo 7)]
```

**Regras associadas:** RN-088 a RN-108, especialmente RN-095

## Fórmulas e Cálculos

### Fórmula: Percentual Acumulado de Acréscimos

```
percentual_acumulado = (SUM(aditivos.valor_acrescimo) / valor_original_contrato) * 100
```

| Variável | Descrição | Fonte |
|---|---|---|
| SUM(aditivos.valor_acrescimo) | Soma de todos os acréscimos vigentes do contrato | Tabela `aditivos` WHERE contrato_id = X AND status = vigente |
| valor_original_contrato | Valor global do contrato no momento da assinatura original | Campo snapshot ou valor_global antes do primeiro aditivo |

*Nota: supressões (valor_supressao) NÃO entram no cálculo do percentual acumulado para efeitos do limite legal — os limites de 25%/50% se referem exclusivamente a acréscimos. RN-097 a RN-099.*

### Fórmula: Critérios de Risco Relacionados a Aditivos

```
score_risco_aditivos = 0
+ (percentual_acumulado > 20 ? 10 : 0)
+ (aditivos_ultimos_12_meses >= 3 ? 10 : 0)
+ (aditivo_proximo_vencimento ? 5 : 0)
```

| Critério | Pontos | Condição |
|---|---|---|
| Percentual acumulado > 20% | +10 | SUM(valor_acrescimo) / valor_original > 0.20 (RN-106) |
| 3+ aditivos em 12 meses | +10 | COUNT(aditivos WHERE data_assinatura > hoje - 365) >= 3 (RN-107) |
| Aditivo recente próximo ao vencimento | +5 | Último aditivo registrado quando data_fim estava a ≤30 dias (RN-108) |

*Nota: estes pontos se somam ao score_risco existente (Fórmula: Score de Risco). O score total permanece classificado pelas mesmas faixas: 0-29 baixo, 30-59 médio, 60+ alto.*

### Fórmula: Indicadores do Dashboard de Aditivos

```
total_aditivos_ano      = COUNT(aditivos WHERE YEAR(data_assinatura) = YEAR(agora))
valor_total_acrescido   = SUM(valor_acrescimo WHERE YEAR(data_assinatura) = YEAR(agora))
pct_medio_acrescimo     = AVG(percentual_acumulado) dos contratos com aditivos no ano
```

*Nota: RN-109 a RN-114.*
