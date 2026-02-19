# Conhecimento — Módulo: Dashboard Executivo

> Extraído de `banco-de-conhecimento.md`. Carregar quando trabalhando no Dashboard Executivo.
> Inclui: Regras (RN-058 a RN-087), Fluxos (9, 10), Fórmulas (indicadores financeiros, score de gestão, mapa de risco, vencimentos, ranking secretaria, índice aditivos fornecedor).

---

## Regras de Negócio

### Módulo: Dashboard Executivo — Visão Geral Financeira

| ID | Regra | Detalhamento |
|---|---|---|
| RN-058 | O Painel Executivo exibe 5 indicadores financeiros no topo | Total de contratos ativos, valor total contratado, valor total executado, saldo remanescente, ticket médio |
| RN-059 | O valor total contratado considera apenas contratos com status vigente | SUM(valor_global WHERE status = vigente) |
| RN-060 | O saldo remanescente é a diferença entre valor contratado e executado | saldo = valor_total_contratado - valor_total_executado |
| RN-061 | O ticket médio é calculado automaticamente | ticket_medio = valor_total_contratado / total_contratos_ativos |

### Módulo: Dashboard Executivo — Mapa de Risco

| ID | Regra | Detalhamento |
|---|---|---|
| RN-062 | O mapa de risco classifica contratos em 3 faixas: baixo, médio, alto | Visualização tipo donut chart com percentuais |
| RN-063 | Critérios de risco alto incluem: vencimento <60 dias, sem fiscal, sem documentos, vencido, valor acima de R$ 1M | Critérios combinados — qualquer critério ativo marca como risco |
| RN-064 | Aditivo acima de 25% do valor original eleva classificação de risco | Percentual configurável pelo admin (padrão: 25%) |
| RN-065 | O painel exibe total de contratos em risco alto, médio e percentual de regulares | Indicadores numéricos + gráfico donut |

### Módulo: Dashboard Executivo — Vencimentos por Janela

| ID | Regra | Detalhamento |
|---|---|---|
| RN-066 | Vencimentos são distribuídos em 5 faixas temporais | 0-30d, 31-60d, 61-90d, 91-120d, >120d |
| RN-067 | A distribuição considera apenas contratos com status vigente | Contratos encerrados/cancelados não entram |

### Módulo: Dashboard Executivo — Distribuição por Secretaria

| ID | Regra | Detalhamento |
|---|---|---|
| RN-068 | O ranking de secretarias exibe: total contratos, valor total, % em risco, vencimentos próximos | Ordenado por valor total (descendente) |
| RN-069 | O percentual em risco por secretaria considera contratos com nivel_risco médio ou alto | (contratos_risco_medio + contratos_risco_alto) / total_contratos_secretaria * 100 |

### Módulo: Dashboard Executivo — Contratos Essenciais

| ID | Regra | Detalhamento |
|---|---|---|
| RN-070 | Contratos com categoria=essencial possuem painel separado no dashboard | Destaque especial para serviços indispensáveis |
| RN-071 | O painel de essenciais exibe contratos vencendo em até 60 dias | Alerta visual de urgência para prefeito/secretário |
| RN-072 | Serviços essenciais incluem: merenda, transporte escolar, coleta de lixo, medicamentos, energia | Baseado em categoria_servico: alimentacao, transporte, limpeza, saude, educacao |

### Módulo: Dashboard Executivo — Filtros Inteligentes

| ID | Regra | Detalhamento |
|---|---|---|
| RN-073 | O dashboard permite filtros combinados | Por secretaria, faixa de valor, risco, tipo de contrato, modalidade, fonte de recurso |
| RN-074 | Filtros aplicam-se a todos os blocos simultaneamente | Ao filtrar por secretaria, todos os indicadores refletem a secretaria selecionada |

### Módulo: Dashboard Executivo — Score de Gestão

| ID | Regra | Detalhamento |
|---|---|---|
| RN-075 | O score de gestão é calculado como nota de 0 a 100 | score = 100 - (penalidades por critérios negativos) |
| RN-076 | Penalidades: % vencidos * 3, % sem fiscal * 2, % próximos do vencimento (30d) * 1 | Pesos configuráveis pelo admin |
| RN-077 | Classificação: 80-100 = Excelente, 60-79 = Controlada, 40-59 = Atenção, 0-39 = Crítica | Exibir com cor e rótulo |

### Módulo: Dashboard Executivo — Tendências e Rankings

| ID | Regra | Detalhamento |
|---|---|---|
| RN-078 | Tendência mensal exibe comparativo dos últimos 12 meses | Contratos ativos/mês, risco médio/mês, volume financeiro/mês |
| RN-079 | Ranking de fornecedores exibe top 10 por volume financeiro | Inclui: total contratos, valor total, índice de aditivos |
| RN-080 | Índice de aditivos do fornecedor = total_aditivos / total_contratos | Fornecedores com índice alto merecem atenção |

### Módulo: Dashboard Executivo — Visão do Controlador

| ID | Regra | Detalhamento |
|---|---|---|
| RN-081 | Perfil controlador exibe lista de irregularidades | Contratos vencidos, sem fiscal, sem documento, aditivos acima do limite |
| RN-082 | Log de alterações recentes mostra últimos 30 dias | Baseado na tabela historico_alteracoes |
| RN-083 | Aditivos acima de 25% do valor original são destacados | Sinalização visual de alerta para controladoria |

### Módulo: Dashboard Executivo — Performance e Cache

| ID | Regra | Detalhamento |
|---|---|---|
| RN-084 | Dados do dashboard são pré-calculados diariamente (processamento noturno) | Tabela dashboard_agregados atualizada via command agendado |
| RN-085 | O dashboard deve carregar em menos de 2 segundos | Dados vêm de tabela agregada, nunca de queries em tempo real |
| RN-086 | Atualização automática 1x/dia + atualização manual sob demanda | Botão "Atualizar dados" disponível para admin |
| RN-087 | Cache por município com invalidação ao atualizar agregados | Cache Redis com TTL de 24h |

## Fluxos de Negócio

### Fluxo 9: Carga do Painel Executivo

```
[1. Usuário acessa /dashboard]
       │
       ▼
[2. Sistema verifica cache Redis]
       │
   ┌───┴───┐
   ▼       ▼
[Cache    [Cache miss]
 hit]        │
   │         ▼
   │    [3. Busca dados na tabela dashboard_agregados]
   │         │
   │         ▼
   │    [4. Grava resultado no cache Redis (TTL 24h)]
   │         │
   ▼         ▼
[5. Monta resposta com os 5 blocos estratégicos]
   ├── Bloco 1: Visão Geral Financeira (5 cards)
   ├── Bloco 2: Mapa de Risco (donut chart)
   ├── Bloco 3: Vencimentos por Janela (5 faixas)
   ├── Bloco 4: Ranking por Secretaria (tabela)
   └── Bloco 5: Contratos Essenciais (painel especial)
       │
       ▼
[6. Aplica filtros inteligentes (se selecionados) (RN-073, RN-074)]
       │
       ▼
[7. Renderiza dashboard completo em <2 segundos (RN-085)]
```

**Regras associadas:** RN-058 a RN-074, RN-084, RN-085, RN-087

### Fluxo 10: Agregação Noturna de Dados do Dashboard

```
[1. Cron noturno executa AgregarDashboardCommand (RN-084)]
       │
       ▼
[2. Calcula indicadores financeiros (RN-058 a RN-061)]
   ├── Total contratos ativos
   ├── Valor total contratado (SUM vigentes)
   ├── Valor total executado
   ├── Saldo remanescente
   └── Ticket médio
       │
       ▼
[3. Calcula mapa de risco (RN-062 a RN-065)]
   ├── Contagem por faixa (baixo/médio/alto)
   └── Percentuais
       │
       ▼
[4. Calcula vencimentos por janela (RN-066, RN-067)]
   └── Contagem por faixa (0-30, 31-60, 61-90, 91-120, >120)
       │
       ▼
[5. Calcula ranking por secretaria (RN-068, RN-069)]
   └── Para cada secretaria: total, valor, % risco, vencendo
       │
       ▼
[6. Identifica contratos essenciais próximos do vencimento (RN-070 a RN-072)]
       │
       ▼
[7. Calcula score de gestão (RN-075 a RN-077)]
       │
       ▼
[8. Calcula tendências mensais — últimos 12 meses (RN-078)]
       │
       ▼
[9. Calcula ranking de fornecedores (RN-079, RN-080)]
       │
       ▼
[10. Grava tudo em tabela dashboard_agregados]
       │
       ▼
[11. Invalida cache Redis (RN-087)]
```

**Regras associadas:** RN-058 a RN-087

## Fórmulas e Cálculos

### Fórmula: Indicadores Financeiros do Painel Executivo

```
total_contratos_ativos = COUNT(contratos WHERE status = vigente)
valor_total_contratado = SUM(valor_global WHERE status = vigente)
valor_total_executado  = SUM(percentual_executado / 100 * valor_global WHERE status = vigente)
saldo_total            = valor_total_contratado - valor_total_executado
ticket_medio           = valor_total_contratado / total_contratos_ativos
```

| Variável | Descrição | Fonte |
|---|---|---|
| total_contratos_ativos | Quantidade de contratos com status vigente | Tabela `contratos` WHERE status = vigente |
| valor_total_contratado | Soma dos valores globais de contratos vigentes | Campo `valor_global` da tabela `contratos` |
| valor_total_executado | Soma dos valores executados de contratos vigentes | Calculado via `percentual_executado` e `valor_global` |
| saldo_total | Diferença entre contratado e executado | Derivado |
| ticket_medio | Valor médio por contrato ativo | Derivado |

*Nota: RN-058 a RN-061.*

### Fórmula: Score de Gestão Contratual

```
pct_vencidos    = COUNT(contratos WHERE status = vencido) / COUNT(contratos WHERE status IN (vigente, vencido)) * 100
pct_sem_fiscal  = COUNT(contratos WHERE status = vigente AND sem fiscal atual) / total_contratos_ativos * 100
pct_vencendo_30 = COUNT(contratos WHERE status = vigente AND data_fim BETWEEN hoje AND hoje + 30) / total_contratos_ativos * 100

score_gestao = 100 - (pct_vencidos * 3) - (pct_sem_fiscal * 2) - (pct_vencendo_30 * 1)
score_gestao = MAX(0, MIN(100, score_gestao))  // limita entre 0 e 100
```

| Faixa | Classificação | Cor |
|---|---|---|
| 80-100 | Excelente | Verde |
| 60-79 | Controlada | Azul |
| 40-59 | Atenção | Amarelo |
| 0-39 | Crítica | Vermelho |

*Nota: pesos configuráveis pelo admin (RN-076). Score arredondado para inteiro.*

### Fórmula: Mapa de Risco Contratual (Dashboard)

```
total_risco_baixo = COUNT(contratos WHERE status = vigente AND nivel_risco = baixo)
total_risco_medio = COUNT(contratos WHERE status = vigente AND nivel_risco = medio)
total_risco_alto  = COUNT(contratos WHERE status = vigente AND nivel_risco = alto)

pct_regular  = total_risco_baixo / total_contratos_ativos * 100
pct_atencao  = total_risco_medio / total_contratos_ativos * 100
pct_critico  = total_risco_alto  / total_contratos_ativos * 100
```

*Nota: RN-062 a RN-065. Visualização tipo donut chart.*

### Fórmula: Distribuição de Vencimentos por Janela

```
vencendo_0_30   = COUNT(contratos WHERE status = vigente AND data_fim BETWEEN hoje AND hoje + 30)
vencendo_31_60  = COUNT(contratos WHERE status = vigente AND data_fim BETWEEN hoje + 31 AND hoje + 60)
vencendo_61_90  = COUNT(contratos WHERE status = vigente AND data_fim BETWEEN hoje + 61 AND hoje + 90)
vencendo_91_120 = COUNT(contratos WHERE status = vigente AND data_fim BETWEEN hoje + 91 AND hoje + 120)
vencendo_120p   = COUNT(contratos WHERE status = vigente AND data_fim > hoje + 120)
```

*Nota: RN-066, RN-067.*

### Fórmula: Percentual de Risco por Secretaria

```
pct_risco_secretaria = (
    COUNT(contratos WHERE secretaria_id = X AND nivel_risco IN (medio, alto) AND status = vigente)
    / COUNT(contratos WHERE secretaria_id = X AND status = vigente)
) * 100
```

*Nota: RN-068, RN-069. Aplicado para cada secretaria no ranking.*

### Fórmula: Índice de Aditivos do Fornecedor

```
indice_aditivos = COUNT(aditivos WHERE contrato.fornecedor_id = X)
                  / COUNT(contratos WHERE fornecedor_id = X AND status IN (vigente, encerrado))
```

| Faixa | Interpretação |
|---|---|
| 0 - 0.5 | Normal |
| 0.5 - 1.0 | Acima da média |
| > 1.0 | Alto — requer atenção |

*Nota: RN-079, RN-080.*
