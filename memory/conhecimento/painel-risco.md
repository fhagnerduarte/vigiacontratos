# Conhecimento — Módulo: Painel de Risco Administrativo

> Extraído de `banco-de-conhecimento.md`. Carregar quando trabalhando no Painel de Risco.
> Inclui: Regras (RN-136 a RN-155), Fluxos (13, 14), Fórmulas (score de risco expandido, indicadores painel risco, ranking, mapa por secretaria).

---

## Regras de Negócio

### Módulo: Painel de Risco Administrativo — Motor de Classificação (Módulo 6)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-136 | O score de risco é expandido com 5 categorias de risco | Cada contrato recebe um score 0-100 baseado em critérios de: vencimento, financeiro, documental, jurídico e operacional. O campo `score_risco` do Contrato é o mesmo existente, apenas com mais critérios (ADR-038) |
| RN-137 | Risco de Vencimento: contrato vencendo em <30 dias → +15 pontos | Subcritério: aditivo próximo do limite legal → +10 pontos adicional |
| RN-138 | Risco Financeiro: valor empenhado > valor contratado → +15 pontos | Subcritério: aditivos acumulados > limite legal → +10 pontos. Subcritério: falta de saldo orçamentário → +5 pontos |
| RN-139 | Risco Documental: falta de certidão atualizada → +5 pontos por tipo faltante | Subcritério: falta de publicação → +5; falta de termo de fiscalização → +5; falta de relatório de execução → +5; documento vencido → +10. Critérios mais granulares que o `sem_documento` original — substitui critério binário na implementação |
| RN-140 | Risco Jurídico: renovação automática irregular → +15 pontos | Subcritério: prazo superior ao permitido por lei → +10; aditivos sucessivos suspeitos (4+ em 12 meses) → +10; ausência de justificativa formal → +10 |
| RN-141 | Risco Operacional: contrato essencial vencendo em <60 dias → +20 pontos | Subcritério: serviço continuado sem renovação formal programada → +10 pontos |
| RN-142 | O score de risco expandido mantém as faixas existentes | 0-29 = Baixo (verde), 30-59 = Médio (amarelo), 60-100 = Alto (vermelho). Score capped em 100: MIN(100, score_calculado) |

### Módulo: Painel de Risco Administrativo — Dashboard Visual (Módulo 6)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-143 | O Painel de Risco é uma página dedicada acessível via menu lateral | Rota: `/painel-risco`. Acessível por todos os perfis com permissão `painel-risco.visualizar`. Dashboard Executivo mantém Bloco 2 como resumo com link "Ver detalhes" (ADR-039) |
| RN-144 | O dashboard de risco exibe 5 indicadores no topo | (1) Total contratos ativos, (2) % contratos com risco alto, (3) contratos vencendo em 30 dias, (4) aditivos acima de 20%, (5) contratos sem documentação obrigatória |
| RN-145 | Os indicadores usam semáforo de cores | Verde = regular (score 0-29), Amarelo = atenção (score 30-59), Vermelho = crítico (score 60+) |
| RN-146 | O ranking de risco é uma tabela automática ordenada por score DESC | Colunas: Contrato, Secretaria, Tipo(s) de Risco (categorias), Score, Urgência (cor) |
| RN-147 | O ranking exibe badge com a(s) categoria(s) de risco identificadas | Um contrato pode ter múltiplas categorias de risco simultâneas (ex: financeiro + documental) |

### Módulo: Painel de Risco Administrativo — Mapa por Secretaria (Módulo 6)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-148 | O mapa de risco por secretaria exibe total de contratos e contratos críticos por órgão | Formato: "Saúde → 12 contratos (3 críticos)". Permite ao prefeito identificar problemas estruturais |
| RN-149 | Secretarias são ordenadas por quantidade de contratos críticos (DESC) | Destaque visual para secretarias com mais de 30% dos contratos em risco |

### Módulo: Painel de Risco Administrativo — Relatório para TCE (Módulo 6)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-150 | O sistema gera relatório automático de risco exportável em PDF | Conteúdo: lista de contratos monitorados com score, justificativa de risco por categoria, plano de ação sugerido, histórico de alertas |
| RN-151 | O relatório inclui justificativa de risco por contrato | Para cada contrato em risco, o sistema descreve quais critérios foram ativados e a pontuação de cada um |
| RN-152 | O relatório serve como instrumento de defesa administrativa | Demonstra que o município monitora proativamente seus contratos — argumento para Controladoria, Jurídico e Prefeito |

### Módulo: Painel de Risco Administrativo — Alertas Preventivos Inteligentes (Módulo 6)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-153 | Os alertas preventivos geram mensagens contextualizadas | Exemplos: "Contrato 015/2023 ultrapassou 25% de aditivo", "Contrato essencial vencerá em 18 dias", "Certidão do fornecedor expira em 10 dias" |
| RN-154 | Alertas preventivos são disparados pelo motor de monitoramento existente (VerificarVencimentosCommand) | Integrado ao cron diário existente — não criar novo command separado |
| RN-155 | Notificação por WhatsApp institucional é funcionalidade de Fase 2 | V1: sistema + email. V2: WhatsApp Business API. Não implementar em V1 (ADR-041) |

## Fluxos de Negócio

### Fluxo 13: Carga do Painel de Risco Administrativo (Módulo 6)

```
[1. Usuário acessa /painel-risco]
       │
       ▼
[2. Sistema verifica cache Redis (chave: painel_risco)]
       │
   ┌───┴───┐
   ▼       ▼
[Cache    [Cache miss]
 hit]        │
   │         ▼
   │    [3. PainelRiscoService consulta dados]
   │       ├── Indicadores do topo (RN-144)
   │       ├── Ranking de risco (RN-146)
   │       ├── Mapa por secretaria (RN-148)
   │       └── Categorias de risco por contrato (RN-147)
   │         │
   │         ▼
   │    [4. Grava resultado no cache Redis (TTL 24h)]
   │         │
   ▼         ▼
[5. Monta resposta com 3 seções]
   ├── Seção 1: Cards de indicadores (5 cards com semáforo — RN-144, RN-145)
   ├── Seção 2: Ranking de risco (tabela ordenada por score DESC — RN-146)
   └── Seção 3: Mapa de risco por secretaria (RN-148)
       │
       ▼
[6. Renderiza painel em <2 segundos (dados pré-agregados)]
```

**Regras associadas:** RN-136 a RN-149

### Fluxo 14: Geração de Relatório de Risco para TCE (Módulo 6)

```
[1. Usuário acessa Painel de Risco → clica "Exportar Relatório TCE"]
       │
       ▼
[2. PainelRiscoService.gerarRelatorioRiscoTCE() é chamado]
       │
       ▼
[3. Coleta dados]
   ├── Lista de contratos monitorados (todos com score > 0)
   ├── Para cada contrato: critérios de risco ativados com pontuação
   ├── Histórico de alertas do contrato
   └── Plano de ação sugerido (baseado na categoria de risco)
       │
       ▼
[4. Gera PDF via DomPDF/Snappy]
   ├── Cabeçalho: município, data de geração, período
   ├── Resumo: total contratos, distribuição por risco (baixo/médio/alto)
   ├── Tabela detalhada: contrato, score, categorias, justificativa
   └── Rodapé: "Gerado automaticamente pelo sistema vigiacontratos"
       │
       ▼
[5. Download do PDF pelo usuário]
```

**Regras associadas:** RN-150 a RN-152

## Fórmulas e Cálculos

### Fórmula: Score de Risco (Expandido — Módulo 6)

```
score_risco = 0
// Critérios base (Módulos 1-5)
+ (sem_fiscal ? 20 : 0)
+ (sem_documento ? 20 : 0)                     // substituído por critérios granulares RN-139 quando Módulo 6 implementado
+ (valor_global > 1_000_000 ? 10 : 0)
+ (modalidade_sensivel ? 10 : 0)
+ (sem_fundamento_legal_quando_obrigatorio ? 10 : 0)
+ (sem_processo ? 10 : 0)
+ (vigencia_meses > 24 ? 5 : 0)
+ (percentual_acumulado_aditivos > 20 ? 10 : 0)
+ (aditivos_ultimos_12_meses >= 3 ? 10 : 0)
+ (aditivo_proximo_vencimento ? 5 : 0)
// Módulo 6 — Risco de Vencimento (RN-137)
+ (vencendo_30_dias ? 15 : 0)
+ (aditivo_proximo_limite_legal ? 10 : 0)
// Módulo 6 — Risco Financeiro (RN-138)
+ (valor_empenhado_excede_contratado ? 15 : 0)
+ (aditivos_acima_limite_legal ? 10 : 0)
+ (falta_saldo_orcamentario ? 5 : 0)
// Módulo 6 — Risco Documental granular (RN-139) — substitui sem_documento binário
+ (falta_certidao ? 5 : 0)
+ (falta_publicacao ? 5 : 0)
+ (falta_termo_fiscalizacao ? 5 : 0)
+ (falta_relatorio_execucao ? 5 : 0)
+ (documento_vencido ? 10 : 0)
// Módulo 6 — Risco Jurídico (RN-140)
+ (renovacao_automatica_irregular ? 15 : 0)
+ (prazo_superior_permitido ? 10 : 0)
+ (aditivos_sucessivos_suspeitos ? 10 : 0)
+ (ausencia_justificativa_formal ? 10 : 0)
// Módulo 6 — Risco Operacional (RN-141)
+ (essencial_vencendo_60_dias ? 20 : 0)
+ (servico_continuado_sem_renovacao ? 10 : 0)

score_risco = MIN(100, score_risco)  // capped em 100
```

| Critério | Categoria | Pontos | Condição |
|---|---|---|---|
| Sem fiscal designado | Base | +20 | Nenhum fiscal com `is_atual = true` |
| Sem documento anexado | Base | +20 | Zero documentos vinculados (substituído por critérios granulares RN-139 quando M6 implementado) |
| Valor > R$ 1.000.000 | Base | +10 | `valor_global > 1000000` |
| Modalidade sensível | Base | +10 | `modalidade_contratacao IN (dispensa, inexigibilidade)` |
| Sem fundamento legal | Base | +10 | Dispensa/inexigibilidade sem `fundamento_legal` preenchido |
| Sem processo administrativo | Base | +10 | `numero_processo` vazio |
| Vigência longa | Base | +5 | `prazo_meses > 24` |
| Percentual acumulado aditivos > 20% | Base | +10 | `SUM(valor_acrescimo) / valor_original > 0.20` (RN-106) |
| 3+ aditivos em 12 meses | Base | +10 | `COUNT(aditivos WHERE data_assinatura > hoje - 365) >= 3` (RN-107) |
| Aditivo próximo do vencimento | Base | +5 | Último aditivo com `data_fim` ≤30 dias (RN-108) |
| Vencendo em <30 dias | Vencimento | +15 | `data_fim - hoje < 30` (RN-137) |
| Aditivo próximo do limite legal | Vencimento | +10 | `percentual_acumulado > (limite_legal - 5)` (RN-137) |
| Valor empenhado > contratado | Financeiro | +15 | `valor_empenhado > valor_global` (RN-138) |
| Aditivos acima do limite legal | Financeiro | +10 | `percentual_acumulado > limite_legal` (RN-138) |
| Falta de saldo orçamentário | Financeiro | +5 | Saldo insuficiente para cobrir contrato (RN-138) |
| Falta de certidão | Documental | +5 | Sem documento tipo certidão atualizada (RN-139) |
| Falta de publicação | Documental | +5 | Sem documento tipo publicacao_oficial (RN-139) |
| Falta de termo de fiscalização | Documental | +5 | Sem documento tipo relatorio_fiscalizacao (RN-139) |
| Falta de relatório de execução | Documental | +5 | Sem documento tipo relatorio_medicao (RN-139) |
| Documento vencido | Documental | +10 | Documento com prazo de validade expirado (RN-139) |
| Renovação automática irregular | Jurídico | +15 | Prorrogação automática sem amparo legal (RN-140) |
| Prazo superior ao permitido | Jurídico | +10 | Vigência total > limite legal da modalidade (RN-140) |
| Aditivos sucessivos suspeitos | Jurídico | +10 | 4+ aditivos em 12 meses (RN-140) |
| Ausência de justificativa formal | Jurídico | +10 | Aditivo sem justificativa_tecnica (RN-140) |
| Essencial vencendo em <60 dias | Operacional | +20 | Contrato essencial com `data_fim - hoje < 60` (RN-141) |
| Serviço continuado sem renovação | Operacional | +10 | Contrato continuado sem aditivo de prazo programado (RN-141) |

**Classificação:**
- 0-29 → `baixo` (verde)
- 30-59 → `medio` (amarelo)
- 60-100 → `alto` (vermelho)

### Fórmula: Indicadores do Painel de Risco (Módulo 6)

```
total_contratos_ativos    = COUNT(contratos WHERE status = vigente)
pct_risco_alto            = COUNT(contratos WHERE status = vigente AND nivel_risco = alto) / total_contratos_ativos * 100
vencendo_30_dias          = COUNT(contratos WHERE status = vigente AND data_fim BETWEEN hoje AND hoje + 30)
aditivos_acima_20pct      = COUNT(DISTINCT contratos WHERE EXISTS(aditivos com percentual_acumulado > 20))
sem_doc_obrigatoria       = COUNT(contratos WHERE status = vigente AND status_completude != completo)
```

*Nota: RN-144. Exibidos como 5 cards no topo do Painel de Risco.*

### Fórmula: Ranking de Risco (Módulo 6)

```
ranking_risco = SELECT contratos.*,
    score_risco,
    nivel_risco,
    categorias_risco_identificadas  // array de CategoriaRisco ativas
FROM contratos
WHERE status = vigente AND score_risco > 0
ORDER BY score_risco DESC
```

*Nota: RN-146, RN-147. Cada contrato pode ter múltiplas categorias de risco (ex: financeiro + documental).*

### Fórmula: Mapa de Risco por Secretaria (Módulo 6)

```
mapa_risco_secretaria = SELECT
    secretaria.nome,
    COUNT(contratos) AS total_contratos,
    COUNT(contratos WHERE nivel_risco = alto) AS contratos_criticos,
    (contratos_criticos / total_contratos * 100) AS pct_criticos
FROM secretarias
JOIN contratos ON contratos.secretaria_id = secretarias.id
WHERE contratos.status = vigente
GROUP BY secretarias.id
ORDER BY contratos_criticos DESC
```

*Nota: RN-148, RN-149. Destaque visual para secretarias com pct_criticos > 30%.*
