# Regras — LAI (Lei de Acesso a Informacao 12.527/2011) e Transparencia

> Extraido de `banco-de-regras.md`. Carregar ao trabalhar com portal publico, classificacao de sigilo, SIC/e-SIC ou dados abertos.

---

## Base Legal

- **Lei 12.527/2011** (LAI) — Regula o acesso a informacoes publicas
- **Decreto 7.724/2012** — Regulamenta a LAI no ambito federal
- **Lei 13.709/2018** (LGPD) — Protecao de dados pessoais (intersecao com LAI)
- **Lei 14.133/2021** — Nova Lei de Licitacoes (publicacao obrigatoria)

---

## Regras de Classificacao de Sigilo (RN-400 a RN-409)

### RN-400: Niveis de Classificacao
Todo contrato e documento possui `classificacao_sigilo` com 4 niveis (LAI art. 24):
- **Publico** (default) — acessivel a qualquer cidadao
- **Reservado** — sigilo de ate 5 anos
- **Secreto** — sigilo de ate 15 anos
- **Ultrassecreto** — sigilo de ate 25 anos

### RN-401: Justificativa Obrigatoria
Classificacao diferente de `publico` EXIGE `justificativa_sigilo` preenchida. Sem justificativa, a classificacao e invalida.

### RN-402: Desclassificacao Temporal Automatica
O sistema deve verificar periodicamente (mensal) contratos/documentos cuja classificacao excedeu o prazo legal e desclassificar automaticamente para `publico`, registrando auditoria.

### RN-403: Autoridade Classificadora
Apenas usuarios com permissao `classificacao.classificar` podem alterar a classificacao de sigilo. O campo `classificado_por` registra quem classificou.

### RN-404: Auditoria de Classificacao
Toda alteracao de classificacao (classificar ou desclassificar) gera registro em `historico_alteracoes` via AuditoriaService.

### RN-405: Default Publico
Contratos e documentos nascem como `publico` por padrao (LAI art. 3 — principio da publicidade maxima).

---

## Regras de Transparencia Ativa — Portal Publico (RN-410 a RN-419)

### RN-410: Dados Minimos Obrigatorios
O portal publico DEVE exibir para cada contrato publico:
- Numero do contrato
- Objeto
- Fornecedor (razao social + CNPJ)
- Valor global
- Vigencia (data inicio e fim)
- Status
- Secretaria responsavel
- Modalidade de contratacao
- Numero do processo licitatorio

### RN-411: Filtragem por Classificacao
O portal publico NUNCA exibe contratos com `classificacao_sigilo != publico`. A query deve SEMPRE filtrar por `classificacao_sigilo = publico`.

### RN-412: Mascaramento de Dados Pessoais
No portal publico, dados pessoais devem ser mascarados:
- CPF de fiscais/servidores: exibir apenas 3 primeiros e 2 ultimos digitos
- Email pessoal: nao exibir
- Telefone pessoal: nao exibir
Usar LGPDService para mascaramento consistente.

### RN-413: Dados Abertos
O portal deve oferecer exportacao em formatos abertos:
- JSON (formato padrao dados.gov.br quando possivel)
- CSV com headers descritivos em pt-BR
Sem necessidade de autenticacao.

### RN-414: Publicacao no Portal
Campo `publicado_portal` controla visibilidade no portal. Contratos publicos com `data_publicacao` preenchida devem ser publicados automaticamente via command `lai:publicar-automatico`.

### RN-415: Resolucao de Tenant no Portal
Rotas publicas usam `/{slug}/portal/*` para resolver o tenant. Middleware `ResolveTenantPublic` configura a conexao sem exigir autenticacao. Tenant inativo retorna 404.

### RN-416: Layout Publico Independente
O portal publico usa layout proprio (sem sidebar autenticada), acessivel a qualquer cidadao sem login.

---

## Regras de Transparencia Passiva — SIC/e-SIC (RN-420 a RN-429)

### RN-420: Protocolo Automatico
Toda solicitacao LAI recebe protocolo unico no formato `LAI-{ANO}-{SEQUENCIAL}` (ex: LAI-2026-000042). O protocolo e informado ao cidadao imediatamente.

### RN-421: Prazo Legal de Resposta
O prazo legal para resposta e de **20 dias corridos** a partir da data de recebimento (LAI art. 11, §1o).

### RN-422: Prorrogacao Unica
O prazo pode ser prorrogado por **mais 10 dias corridos**, mediante justificativa expressa. Maximo de 1 prorrogacao por solicitacao (LAI art. 11, §2o).

### RN-423: Workflow de Status
```
recebida → em_analise → respondida
                     → prorrogada → respondida
                     → indeferida → recurso
```

### RN-424: Historico Imutavel
Toda mudanca de status gera registro em `historico_solicitacoes_lai` (append-only com trigger MySQL). Campos: status_anterior, status_novo, observacao, user_id, created_at.

### RN-425: Acesso Publico a Consulta
O cidadao pode consultar o status da solicitacao via protocolo + email, sem necessidade de autenticacao.

### RN-426: Dados do Solicitante Protegidos
O CPF do solicitante e armazenado com criptografia (`encrypted` cast). Dados pessoais do solicitante NAO sao visiveis no portal publico.

### RN-427: Classificacao da Resposta
Toda resposta deve ser classificada como: deferida, parcialmente_deferida ou indeferida. Indeferimento exige justificativa.

---

## Regras de Alertas LAI (RN-430 a RN-439)

### RN-430: Alerta Contrato Nao Publicado
Contratos com `classificacao_sigilo = publico` e `publicado_portal = false` e `data_publicacao` preenchida geram alerta `ContratoNaoPublicadoPortal`.

### RN-431: Alerta Solicitacao Vencendo
Solicitacao LAI com prazo restante <= 5 dias gera alerta `SolicitacaoLaiVencendo` (severidade alta).

### RN-432: Alerta Solicitacao Vencida
Solicitacao LAI com prazo expirado (sem resposta) gera alerta `SolicitacaoLaiVencida` (severidade critica — risco legal).

### RN-433: Alerta Classificacao Sem Justificativa
Contrato com `classificacao_sigilo != publico` e `justificativa_sigilo` vazia gera alerta `ClassificacaoSemJustificativa`.

### RN-434: Risco de Transparencia
RiscoService inclui categoria `transparencia` em calcularExpandido():
- Contrato nao publicado no portal: +5 pontos
- Classificacao sem justificativa: +10 pontos
- Solicitacao LAI vencida vinculada ao contrato: +10 pontos

---

## Permissoes LAI

### Grupo: Classificacao de Sigilo
| Permissao | Descricao |
|-----------|-----------|
| `classificacao.visualizar` | Visualizar classificacao de sigilo |
| `classificacao.classificar` | Classificar/alterar sigilo de contrato |
| `classificacao.desclassificar` | Desclassificar contrato para publico |
| `classificacao.justificar` | Editar justificativa de sigilo |

### Grupo: LAI (Solicitacoes)
| Permissao | Descricao |
|-----------|-----------|
| `lai.visualizar` | Visualizar solicitacoes LAI |
| `lai.analisar` | Marcar solicitacao como em analise |
| `lai.responder` | Registrar resposta a solicitacao |
| `lai.prorrogar` | Prorrogar prazo de solicitacao |
| `lai.indeferir` | Indeferir solicitacao |
| `lai.relatorio` | Gerar relatorio de transparencia |
