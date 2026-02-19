# Conhecimento — Módulo: Alertas

> Extraído de `banco-de-conhecimento.md`. Carregar quando trabalhando no módulo de Alertas.
> Inclui: Regras (RN-014 a RN-017, RN-043 a RN-057), Fluxos (3, 7), Fórmulas (prioridade alerta, indicadores dashboard alertas).

---

## Regras de Negócio

### Módulo: Alertas — Motor de Monitoramento

| ID | Regra | Detalhamento |
|---|---|---|
| RN-014 | Alertas são gerados automaticamente com base nos prazos configurados | Motor de monitoramento (cron job diário) verifica contratos vigentes e gera alertas |
| RN-015 | Os prazos de alerta são configuráveis pelo administrador | Configuração padrão V1: 120, 90, 60, 30, 15, 7 dias antes do vencimento |
| RN-016 | Um alerta não deve ser duplicado para o mesmo contrato, evento e prazo | Se já existe alerta pendente para aquele contrato + tipo_evento + dias_antecedencia, não gerar outro |
| RN-017 | Quando um contrato é renovado (aditivo de prazo), alertas pendentes são resolvidos automaticamente | Status muda para "resolvido" |

### Módulo: Alertas — Prioridade e Classificação

| ID | Regra | Detalhamento |
|---|---|---|
| RN-043 | Prioridade do alerta é determinada automaticamente pela proximidade do vencimento | >30 dias = informativo, ≤30 dias = atenção, ≤7 dias = urgente |
| RN-044 | Motor de monitoramento executa diariamente via scheduled command (cron) | Comando `alertas:verificar-vencimentos` agendado no Kernel do Laravel |
| RN-045 | Motor monitora 4 tipos de evento | vencimento_vigencia, termino_aditivo, prazo_garantia, prazo_execucao_fisica |
| RN-046 | Contrato vencido é marcado automaticamente como IRREGULAR no painel | Destaque visual vermelho no dashboard e na listagem |

### Módulo: Alertas — Destinatários e Notificação

| ID | Regra | Detalhamento |
|---|---|---|
| RN-047 | Destinatários do alerta são derivados do contrato | Fiscal atual, secretário da pasta (email da secretaria), controlador interno (se configurado) |
| RN-048 | V1 canais de notificação: email institucional + notificação interna | Email via SMTP + notificação visual no sistema (sino/badge no navbar) |
| RN-049 | Cada envio de notificação é registrado em log_notificacoes | canal, destinatario, data_envio, sucesso, resposta_gateway |
| RN-050 | Falha no envio de notificação é retentada com backoff exponencial | Máximo 3 tentativas. Registra cada tentativa no log |

### Módulo: Alertas — Contrato Essencial e Bloqueio Preventivo

| ID | Regra | Detalhamento |
|---|---|---|
| RN-051 | Contrato essencial (categoria=essencial) recebe alertas com prioridade elevada | Frequência de alertas aumentada, destaque no painel executivo |
| RN-052 | Contrato vencido impede cadastro de aditivo retroativo sem justificativa formal | Campo `justificativa_retroativa` obrigatório se contrato estiver vencido |
| RN-053 | Alerta só é resolvido quando há regularização efetiva | Aditivo de prazo registrado, nova vigência cadastrada ou contrato encerrado corretamente |
| RN-054 | Alerta mantém-se ativo (repetindo) até regularização | Sistema continua gerando notificações enquanto alerta não for resolvido |

### Módulo: Alertas — Dashboard e Indicadores

| ID | Regra | Detalhamento |
|---|---|---|
| RN-055 | Dashboard de alertas exibe indicadores por faixa de vencimento | Vencendo em 120d, 60d, 30d e já vencidos |
| RN-056 | Dashboard de alertas permite filtros combinados | Por secretaria, criticidade (prioridade), tipo de contrato, faixa de valor |
| RN-057 | Relatório mensal de efetividade dos alertas | Contratos regularizados antes do vencimento vs. vencidos, tempo médio de antecipação |

## Fluxos de Negócio

### Fluxo 3: Motor de Monitoramento de Alertas (Completo)

```
[1. Cron diário executa VerificarVencimentosCommand (RN-044)]
       │
       ▼
[2. Consulta contratos vigentes (índice em data_fim)]
       │
       ▼
[3. Para cada contrato, calcula dias_restantes = data_fim - hoje]
       │
       ▼
[4. Verifica eventos monitorados (RN-045)]
   ├── Vencimento da vigência (contrato.data_fim)
   ├── Término de aditivo (aditivo.nova_data_fim)
   ├── Prazo de garantia (se aplicável)
   └── Prazo de execução física (obras)
       │
       ▼
[5. Compara com prazos configurados: 120, 90, 60, 30, 15, 7 dias (RN-015)]
       │
   ┌───┴───┐
   ▼       ▼
[Dentro    [Fora do
 do prazo]  prazo]
   │         │
   ▼         ▼
[6. Verifica se   [Nenhuma
 já existe alerta  ação]
 (RN-016)]
   │
   ┌───┴───┐
   ▼       ▼
[Não       [Sim — já
 existe]    existe]
   │         │
   ▼         ▼
[7. Determina       [Se alerta não resolvido
 prioridade          E prazo mudou →
 (RN-043)]           atualiza prioridade]
   │
   ├── >30d → informativo
   ├── ≤30d → atenção
   └── ≤7d  → urgente
       │
       ▼
[8. Contrato essencial? (RN-051)]
   ├── Sim → eleva prioridade + destaque
   └── Não → mantém prioridade normal
       │
       ▼
[9. Gera registro em tabela alertas]
       │
       ▼
[10. Identifica destinatários (RN-047)]
   ├── Fiscal do contrato (is_atual = true)
   ├── Secretário da pasta (email da secretaria)
   ├── Controlador interno (se configurado)
   └── Admin geral (se contrato essencial)
       │
       ▼
[11. Dispara notificações via queue (RN-048)]
   ├── Email institucional → EnviarNotificacaoAlertaJob
   └── Notificação interna → marca no sistema (sino/badge)
       │
       ▼
[12. Registra log de notificação (RN-049)]
       │
   ┌───┴───┐
   ▼       ▼
[Sucesso]  [Falha]
   │         │
   ▼         ▼
[OK]      [Retry com backoff
           exponencial (RN-050)
           max 3 tentativas]
       │
       ▼
[13. Atualiza status do alerta para 'enviado']
       │
       ▼
[14. Mostra no dashboard de alertas (RN-055)]
       │
       ▼
[15. Alerta mantém-se ativo até regularização (RN-054)]
```

**Regras associadas:** RN-014 a RN-017, RN-043 a RN-057

### Fluxo 7: Regularização de Alerta

```
[1. Contrato possui alertas pendentes/enviados]
       │
       ▼
[2. Gestor realiza ação de regularização]
   ├── Registra aditivo de prazo (→ nova data_fim)
   ├── Cadastra nova vigência
   └── Encerra contrato corretamente
       │
       ▼
[3. Sistema detecta regularização (RN-053)]
       │
       ▼
[4. Todos os alertas pendentes/enviados do contrato
    são resolvidos automaticamente]
   ├── status → resolvido
   ├── resolvido_por → user_id
   └── resolvido_em → agora
       │
       ▼
[5. Se regularização via aditivo de prazo:]
   ├── data_fim do contrato atualizada (RN-012)
   ├── Score de risco recalculado (RN-029)
   └── Novos alertas serão gerados para a nova data_fim
       │
       ▼
[6. Dashboard de alertas atualizado]
```

**Regras associadas:** RN-017, RN-053, RN-054

## Fórmulas e Cálculos

### Fórmula: Prioridade Automática do Alerta

```
SE dias_para_vencimento <= 7  → prioridade = urgente
SE dias_para_vencimento <= 30 → prioridade = atencao
SE dias_para_vencimento > 30  → prioridade = informativo
```

| Faixa (dias) | Prioridade | Cor | Contexto |
|---|---|---|---|
| ≤ 7 dias | Urgente | 🔴 Vermelho | Ação imediata, contrato prestes a vencer |
| 8 – 30 dias | Atenção | 🟡 Amarelo | Prazo curto, requer planejamento urgente |
| > 30 dias | Informativo | 🔵 Azul | Ciência prévia, tempo hábil para agir |

*Nota: para contratos essenciais (categoria=essencial), a prioridade é elevada em um nível (informativo → atenção, atenção → urgente). RN-051.*

### Fórmula: Indicadores do Dashboard de Alertas

```
vencendo_120d = COUNT(contratos WHERE status = vigente AND data_fim BETWEEN hoje AND hoje + 120)
vencendo_60d  = COUNT(contratos WHERE status = vigente AND data_fim BETWEEN hoje AND hoje + 60)
vencendo_30d  = COUNT(contratos WHERE status = vigente AND data_fim BETWEEN hoje AND hoje + 30)
vencidos      = COUNT(contratos WHERE status = vencido)
```

*Nota: secretarias com maior risco = TOP 5 secretarias com mais contratos vencendo em 30 dias (RN-055).*
