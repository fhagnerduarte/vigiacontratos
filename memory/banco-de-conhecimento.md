# Banco de Conhecimento — Domínio de Negócio

> Consultado pelo **Curador de Conhecimento** (Agente 03) para validar toda lógica de negócio.
> Contém o conhecimento do domínio: glossário, regras, fluxos, entidades e relacionamentos.
> **Nenhuma regra de negócio pode ser inventada** — toda lógica deve estar documentada aqui.

---

## Contexto da Plataforma

**vigiacontratos** é um sistema de gestão contratual municipal que centraliza o controle de todos os contratos da prefeitura em um único painel, garantindo que nenhum contrato vença sem o devido acompanhamento e evitando riscos jurídicos por falta de controle.

### O que o sistema FAZ:
- Cadastra e gerencia contratos municipais (serviços, obras, compras, locação)
- Emite alertas automáticos de vencimento com antecedência configurável
- Registra e controla aditivos contratuais (prazo, valor, supressão)
- Gerencia fornecedores vinculados aos contratos
- Armazena documentos digitalizados dos contratos (PDF) com versionamento
- Fornece dashboard com visão geral da situação contratual
- Gera relatórios gerenciais para tomada de decisão
- Calcula score de risco automático para cada contrato
- Registra e acompanha a execução financeira dos contratos
- Mantém auditoria completa de todas as alterações (log de auditoria)
- Gerencia fiscais de contrato com histórico de trocas

### O que o sistema NÃO FAZ:
- Não realiza licitações (apenas registra o número do processo licitatório)
- Não emite notas fiscais ou faz gestão financeira/pagamentos
- Não faz gestão de almoxarifado ou patrimônio
- Não substitui o sistema contábil da prefeitura
- Não é um portal de transparência pública (é sistema interno)

### Para quem:
- **Administradores** — Gestores de TI e secretários que configuram o sistema
- **Gestores de Contratos** — Servidores responsáveis pela gestão contratual diária
- **Consulta** — Auditores, procuradores e demais servidores que precisam consultar contratos

---

## Sistema Legado

Não existe sistema legado. O controle contratual era feito de forma informal (sem sistema padronizado). O vigiacontratos é uma solução nova construída do zero.

---

## Glossário do Domínio

### Termos do Negócio

| Termo | Definição | Exemplo de Uso |
|---|---|---|
| Contrato | Instrumento jurídico que formaliza acordo entre a prefeitura e um fornecedor | "Contrato nº 001/2026 de prestação de serviços de limpeza" |
| Vigência | Período de validade de um contrato (data início até data fim) | "Vigência: 01/01/2026 a 31/12/2026" |
| Aditivo | Alteração formal de um contrato existente (prazo, valor ou ambos) | "Aditivo de prazo por mais 12 meses" |
| Supressão | Redução do valor ou escopo de um contrato via aditivo | "Supressão de 25% do valor contratual" |
| Fornecedor | Empresa ou pessoa jurídica contratada pela prefeitura | "Fornecedor: Empresa XYZ Ltda, CNPJ 00.000.000/0001-00" |
| Secretaria | Órgão/departamento da prefeitura responsável pelo contrato | "Secretaria de Saúde", "Secretaria de Educação" |
| Unidade Gestora | Subdivisão da secretaria que acompanha o contrato | "Diretoria de Compras da Secretaria de Administração" |
| Gestor do Contrato | Servidor público designado para acompanhar a execução do contrato | "Gestor: João da Silva" |
| Fiscal do Contrato | Servidor público que fiscaliza a execução do contrato, com matrícula e cargo registrados | "Fiscal: Maria Souza — Mat. 12345" |
| Objeto | Descrição do que está sendo contratado | "Prestação de serviços de manutenção predial" |
| Processo Administrativo | Número do processo que originou o contrato | "Processo nº 2026/0001" |
| Modalidade de Contratação | Tipo de licitação ou procedimento que originou o contrato | "Pregão Eletrônico", "Dispensa de Licitação", "Inexigibilidade" |
| Fundamento Legal | Base legal que justifica a contratação (obrigatório em dispensas e inexigibilidades) | "Art. 75, II da Lei 14.133/2021" |
| Valor Global | Valor total do contrato considerando toda a vigência | "R$ 500.000,00" |
| Valor Mensal | Valor pago mensalmente ao fornecedor | "R$ 41.666,67" |
| Data de Vencimento | Data em que o contrato expira | "31/12/2026" |
| Dotação Orçamentária | Classificação orçamentária que indica de onde sai o recurso | "02.04.12.361.0008.2.026.3.3.90.39" |
| Fonte de Recurso | Origem do dinheiro para pagamento do contrato | "Recursos próprios", "Transferência federal" |
| Empenho | Reserva de recurso orçamentário para cobrir despesa do contrato | "Empenho nº 2026/000123" |
| Percentual Executado | Proporção do valor já pago em relação ao valor global do contrato | "60% executado" |
| Score de Risco | Pontuação calculada automaticamente que indica o nível de risco do contrato | "Score 40 → Risco Médio (🟡)" |
| Execução Financeira | Registro de cada pagamento/medição realizado no contrato | "Medição de R$ 50.000 em jan/2026" |
| Prorrogação Automática | Cláusula que permite renovação automática do contrato ao final da vigência | "Contrato com prorrogação automática por até 60 meses" |
| Responsável Técnico | Profissional habilitado responsável pela execução técnica (obrigatório em obras) | "Eng. Civil CREA 12345/SP" |

### Termos do Sistema

| Termo | Definição | Exemplo de Uso |
|---|---|---|
| Alerta | Notificação automática gerada quando um contrato está próximo do vencimento | "Alerta urgente: contrato vence em 15 dias" |
| Prioridade do Alerta | Nível de urgência do alerta baseado na proximidade do vencimento | "Informativo (90 dias), Atenção (60 dias), Urgente (30 dias)" |
| Configuração de Alerta | Definição dos prazos de antecedência para cada prioridade | "Admin configurou: urgente = 15 dias" |
| Dashboard | Painel principal com visão geral dos contratos e indicadores | "Dashboard mostra 5 contratos vencendo este mês" |
| Histórico de Alterações | Log automático de toda modificação em dados de contrato | "Alteração: valor_global de R$ 100.000 para R$ 150.000 por João em 18/02/2026" |
| Cadastro Multi-etapa | Formulário de contrato dividido em passos (wizard) para garantir qualidade dos dados | "Etapas: Identificação → Fornecedor → Financeiro → Vigência → Fiscal → Documentos" |

---

## Tipos e Categorias

### StatusContrato

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `vigente` | Vigente | Contrato ativo dentro do prazo de vigência |
| `vencido` | Vencido | Contrato com data de vencimento ultrapassada |
| `cancelado` | Cancelado | Contrato cancelado/anulado antes do término |
| `suspenso` | Suspenso | Contrato temporariamente suspenso |
| `encerrado` | Encerrado | Contrato encerrado normalmente ao final da vigência |
| `rescindido` | Rescindido | Contrato rescindido unilateralmente (pela administração ou fornecedor) |

### TipoContrato

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `servico` | Serviço | Contrato de prestação de serviços |
| `obra` | Obra | Contrato de execução de obra |
| `compra` | Compra / Fornecimento | Contrato de aquisição de bens/materiais |
| `locacao` | Locação | Contrato de locação de imóvel ou equipamento |

### ModalidadeContratacao

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `pregao_eletronico` | Pregão Eletrônico | Licitação na modalidade pregão eletrônico |
| `pregao_presencial` | Pregão Presencial | Licitação na modalidade pregão presencial |
| `concorrencia` | Concorrência | Licitação na modalidade concorrência |
| `tomada_preco` | Tomada de Preço | Licitação na modalidade tomada de preço |
| `convite` | Convite | Licitação na modalidade convite |
| `leilao` | Leilão | Licitação na modalidade leilão |
| `dispensa` | Dispensa de Licitação | Contratação direta por dispensa |
| `inexigibilidade` | Inexigibilidade | Contratação direta por inexigibilidade |
| `adesao_ata` | Adesão a Ata | Adesão a ata de registro de preços |

### TipoPagamento

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `mensal` | Mensal | Pagamento recorrente mensal |
| `por_medicao` | Por Medição | Pagamento mediante medição de serviço executado |
| `parcelado` | Parcelado | Pagamento em parcelas predefinidas |
| `unico` | Parcela Única | Pagamento integral em parcela única |

### CategoriaContrato

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `essencial` | Essencial | Contrato essencial para funcionamento da prefeitura |
| `nao_essencial` | Não Essencial | Contrato de apoio ou complementar |

### CategoriaServico

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `transporte` | Transporte | Serviços de transporte e logística |
| `alimentacao` | Alimentação | Serviços de alimentação e merenda |
| `tecnologia` | Tecnologia da Informação | Serviços e fornecimentos de TI |
| `obras` | Obras e Engenharia | Construção, reforma e engenharia |
| `limpeza` | Limpeza e Conservação | Serviços de limpeza e zeladoria |
| `seguranca` | Segurança e Vigilância | Serviços de vigilância e segurança |
| `manutencao` | Manutenção | Manutenção predial, veicular ou de equipamentos |
| `saude` | Saúde | Serviços e fornecimentos para saúde |
| `educacao` | Educação | Serviços e fornecimentos para educação |
| `outros` | Outros | Categorias não listadas acima |

### NivelRisco

| Valor (Enum) | Nome Exibido | Descrição | Ícone |
|---|---|---|---|
| `baixo` | Baixo | Score de risco 0-29 — contrato bem documentado | 🟢 |
| `medio` | Médio | Score de risco 30-59 — requer atenção | 🟡 |
| `alto` | Alto | Score de risco 60+ — risco elevado, ação necessária | 🔴 |

### TipoDocumentoContratual

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `contrato_original` | Contrato Original | Documento original do contrato assinado |
| `termo_referencia` | Termo de Referência | TR que especifica o objeto da contratação |
| `publicacao_oficial` | Publicação Oficial | Extrato publicado no diário oficial |
| `parecer_juridico` | Parecer Jurídico | Parecer da procuradoria/assessoria jurídica |
| `aditivo_doc` | Documento de Aditivo | Termo aditivo assinado |
| `ordem_servico` | Ordem de Serviço | Ordem para início dos serviços |
| `outros` | Outros | Documentos complementares |

### TipoAditivo

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `prazo` | Prazo | Aditivo que altera apenas o prazo de vigência |
| `valor` | Valor | Aditivo que altera apenas o valor do contrato |
| `prazo_e_valor` | Prazo e Valor | Aditivo que altera prazo e valor simultaneamente |
| `supressao` | Supressão | Aditivo que reduz valor ou escopo do contrato |

### StatusAditivo

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `vigente` | Vigente | Aditivo ativo e em vigor |
| `vencido` | Vencido | Aditivo com prazo expirado |
| `cancelado` | Cancelado | Aditivo cancelado |

### TipoUsuario

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `admin` | Administrador | Acesso total ao sistema, incluindo configurações |
| `gestor` | Gestor de Contratos | CRUD de contratos, fornecedores, aditivos, documentos |
| `consulta` | Consulta | Apenas visualização de dados e relatórios |

### StatusAlerta

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `pendente` | Pendente | Alerta gerado e ainda não visualizado |
| `visualizado` | Visualizado | Alerta lido pelo usuário |
| `resolvido` | Resolvido | Alerta tratado (contrato renovado, encerrado, etc.) |

### PrioridadeAlerta

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `informativo` | Informativo | Vencimento distante, apenas para ciência |
| `atencao` | Atenção | Vencimento se aproximando, requer planejamento |
| `urgente` | Urgente | Vencimento iminente, ação imediata necessária |

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

### Módulo: Aditivos

| ID | Regra | Detalhamento |
|---|---|---|
| RN-009 | Um aditivo deve estar vinculado a um contrato vigente | Não se faz aditivo de contrato vencido ou cancelado |
| RN-010 | Aditivo de prazo deve informar nova data de fim | A nova data de fim deve ser posterior à data de fim atual |
| RN-011 | Aditivo de valor deve informar o valor do acréscimo ou supressão | Valor pode ser positivo (acréscimo) ou negativo (supressão) |
| RN-012 | Aditivo de prazo atualiza a data de vencimento do contrato pai | A data de fim do contrato é atualizada automaticamente |
| RN-013 | Aditivo de valor atualiza o valor global do contrato pai | O valor global é recalculado: valor_original + soma_aditivos |

### Módulo: Alertas

| ID | Regra | Detalhamento |
|---|---|---|
| RN-014 | Alertas são gerados automaticamente com base nos prazos configurados | Um job diário verifica contratos vigentes e gera alertas |
| RN-015 | Os prazos de alerta são configuráveis pelo administrador | Cada prioridade (informativo, atenção, urgente) tem seu prazo em dias |
| RN-016 | Um alerta não deve ser duplicado para o mesmo contrato e prioridade | Se já existe alerta pendente para aquela prioridade, não gerar outro |
| RN-017 | Quando um contrato é renovado (aditivo de prazo), alertas pendentes são resolvidos automaticamente | Status muda para "resolvido" |

### Módulo: Fornecedores

| ID | Regra | Detalhamento |
|---|---|---|
| RN-018 | CNPJ do fornecedor deve ser único no sistema | Não permite cadastro duplicado |
| RN-019 | Fornecedor com contratos vigentes não pode ser excluído | Soft delete + validação antes de excluir |
| RN-038 | Validação automática de CNPJ com dígito verificador | Validar algoritmo do CNPJ no cadastro e edição |

### Módulo: Documentos

| ID | Regra | Detalhamento |
|---|---|---|
| RN-020 | Documentos são vinculados a um contrato ou aditivo | Relação polimórfica (documentable) |
| RN-021 | Apenas PDF é aceito para documentos contratuais | Validação de tipo MIME no upload |
| RN-022 | Tamanho máximo de upload: 10MB por arquivo | Validação no Form Request |
| RN-039 | Upload múltiplo de documentos é permitido | Vários arquivos podem ser enviados de uma vez |
| RN-040 | Todo documento deve ter tipo classificado | tipo_documento obrigatório (contrato_original, termo_referencia, etc.) |
| RN-041 | Documentos possuem versionamento | Ao reuplodar documento do mesmo tipo, versão é incrementada automaticamente |
| RN-042 | Registro automático de quem anexou o documento | uploaded_by + data/hora registrados automaticamente |

### Como documentar regras:
1. Use ID sequencial (RN-XXX)
2. A regra deve ser **clara e verificável** — sem ambiguidade
3. Inclua fórmulas quando houver cálculos
4. Documente exceções e casos especiais
5. Referencie entidades pelo nome do glossário

---

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

### Fluxo 3: Alerta de Vencimento

```
[1. Job diário executa (scheduled)]
       │
       ▼
[2. Consulta contratos vigentes]
       │
       ▼
[3. Para cada contrato, calcula dias até vencimento]
       │
       ▼
[4. Compara com prazos configurados (RN-015)]
       │
   ┌───┴───┐
   ▼       ▼
[Dentro    [Fora do
 do prazo]  prazo]
   │         │
   ▼         ▼
[5. Verifica se   [Nenhuma
 já existe alerta  ação]
 (RN-016)]
   │
   ┌───┴───┐
   ▼       ▼
[Não       [Sim]
 existe]     │
   │         ▼
   ▼       [Ignora]
[6. Cria alerta com
 prioridade adequada]
```

**Regras associadas:** RN-014, RN-015, RN-016

### Fluxo 4: Aditivo Contratual

```
[1. Gestor acessa contrato vigente]
       │
       ▼
[2. Clica em "Novo Aditivo"]
       │
       ▼
[3. Seleciona tipo (prazo, valor, prazo_e_valor, supressão)]
       │
       ▼
[4. Preenche dados conforme o tipo]
       │
       ▼
[5. Sistema valida (RN-009, RN-010, RN-011)]
       │
   ┌───┴───┐
   ▼       ▼
[OK]    [Erro]
   │       │
   ▼       ▼
[6. Aditivo salvo]  [Exibe erros]
   │
   ▼
[7. Contrato pai atualizado automaticamente (RN-012, RN-013)]
   │
   ▼
[8. Score de risco recalculado (RN-029)]
   │
   ▼
[9. Alertas pendentes resolvidos se prazo alterado (RN-017)]
```

**Regras associadas:** RN-009, RN-010, RN-011, RN-012, RN-013, RN-017, RN-029

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

---

## Entidades e Relacionamentos

### Diagrama de Relacionamentos

```
[User] N──1 [TipoUsuario (enum)]

[Secretaria] 1──N [Contrato]

[Fornecedor] 1──N [Contrato]

[Contrato] 1──N [Aditivo]
[Contrato] 1──N [Documento] (polimórfico)
[Contrato] 1──N [Alerta]
[Contrato] 1──N [Fiscal]
[Contrato] 1──N [ExecucaoFinanceira]
[Contrato] 1──N [HistoricoAlteracao] (polimórfico)

[Aditivo] 1──N [Documento] (polimórfico)

[User] 1──N [Documento] (uploaded_by)
[User] 1──N [ExecucaoFinanceira] (registrado_por)
[User] 1──N [HistoricoAlteracao] (user_id)
```

### Detalhamento das Entidades

#### Entidade: User

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| name | varchar(255) | Sim | Nome completo |
| email | varchar(255) | Sim | Único, usado para login |
| password | varchar(255) | Sim | Hash bcrypt |
| tipo | enum(TipoUsuario) | Sim | admin, gestor, consulta |
| is_ativo | boolean | Sim | Default: true |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- Pertence a um tipo (via enum TipoUsuario)

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

#### Entidade: Aditivo

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| contrato_id | bigint | Sim | FK → contratos |
| numero | varchar(50) | Sim | Número sequencial do aditivo |
| tipo | enum(TipoAditivo) | Sim | prazo, valor, prazo_e_valor, supressao |
| status | enum(StatusAditivo) | Sim | Default: vigente |
| data_assinatura | date | Sim | Data de assinatura do aditivo |
| nova_data_fim | date | Não | Nova data fim (se aditivo de prazo) |
| valor_aditivo | decimal(15,2) | Não | Valor do acréscimo/supressão (se aditivo de valor) |
| justificativa | text | Sim | Justificativa do aditivo |
| observacoes | text | Não | Observações |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |
| deleted_at | datetime | Não | Soft delete |

**Relacionamentos:**
- belongsTo: Contrato
- hasMany: Documento (polimórfico)

#### Entidade: Documento

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| documentable_type | varchar(255) | Sim | Polimórfico (Contrato ou Aditivo) |
| documentable_id | bigint | Sim | ID da entidade pai |
| tipo_documento | enum(TipoDocumentoContratual) | Sim | Classificação do documento (RN-040) |
| nome | varchar(255) | Sim | Nome original do arquivo |
| descricao | varchar(255) | Não | Descrição do documento |
| caminho | varchar(500) | Sim | Caminho no storage |
| tamanho | bigint | Sim | Tamanho em bytes |
| mime_type | varchar(100) | Sim | Tipo MIME (application/pdf) |
| versao | int | Sim | Versão do documento. Default: 1 (RN-041) |
| uploaded_by | bigint | Sim | FK → users (quem fez upload) (RN-042) |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- morphTo: documentable (Contrato ou Aditivo)
- belongsTo: User (uploaded_by)

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
| ip_address | varchar(45) | Não | IP do usuário no momento da alteração |
| created_at | datetime | Sim | Automático (imutável — RN-037) |

**Relacionamentos:**
- morphTo: auditable (Contrato, Fornecedor, etc.)
- belongsTo: User (user_id)

**Regras:**
- Registros imutáveis — nunca editar ou deletar (RN-037)
- Usado para auditoria, Tribunal de Contas, segurança jurídica

#### Entidade: Alerta

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| contrato_id | bigint | Sim | FK → contratos |
| prioridade | enum(PrioridadeAlerta) | Sim | informativo, atencao, urgente |
| status | enum(StatusAlerta) | Sim | Default: pendente |
| dias_para_vencimento | int | Sim | Dias restantes no momento da geração |
| data_vencimento | date | Sim | Data de vencimento do contrato |
| mensagem | text | Sim | Mensagem descritiva do alerta |
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

#### Entidade: ConfiguracaoAlerta

| Campo | Tipo | Obrigatório | Observação |
|---|---|---|---|
| id | bigint (auto) | Sim | PK |
| prioridade | enum(PrioridadeAlerta) | Sim | Único por prioridade |
| dias_antecedencia | int | Sim | Quantos dias antes do vencimento |
| created_at | datetime | Sim | Automático |
| updated_at | datetime | Sim | Automático |

**Relacionamentos:**
- Nenhum (tabela de configuração)

**Valores padrão sugeridos:**
- informativo: 90 dias
- atencao: 60 dias
- urgente: 30 dias

---

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
valor_global_atualizado = valor_global_original + SUM(aditivos.valor_aditivo)
```

| Variável | Descrição | Fonte |
|---|---|---|
| valor_global_original | Valor original do contrato | Cadastro inicial |
| aditivos.valor_aditivo | Soma dos valores de todos os aditivos | Tabela `aditivos` com status vigente |

*Nota: supressões têm valor_aditivo negativo.*

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

### Fórmula: Score de Risco

```
score_risco = 0
+ (sem_fiscal ? 20 : 0)
+ (sem_documento ? 20 : 0)
+ (valor_global > 1_000_000 ? 10 : 0)
+ (modalidade_sensivel ? 10 : 0)
+ (sem_fundamento_legal_quando_obrigatorio ? 10 : 0)
+ (sem_processo ? 10 : 0)
+ (vigencia_meses > 24 ? 5 : 0)
```

| Critério | Pontos | Condição |
|---|---|---|
| Sem fiscal designado | +20 | Nenhum fiscal com `is_atual = true` |
| Sem documento anexado | +20 | Zero documentos vinculados ao contrato |
| Valor > R$ 1.000.000 | +10 | `valor_global > 1000000` |
| Modalidade sensível | +10 | `modalidade_contratacao IN (dispensa, inexigibilidade)` |
| Sem fundamento legal | +10 | Dispensa/inexigibilidade sem `fundamento_legal` preenchido |
| Sem processo administrativo | +10 | `numero_processo` vazio |
| Vigência longa | +5 | `prazo_meses > 24` |

**Classificação:**
- 0-29 → `baixo` (🟢)
- 30-59 → `medio` (🟡)
- 60+ → `alto` (🔴)

---

## Instruções de Manutenção

### Quando atualizar este arquivo?
- Quando uma **nova regra de negócio** for descoberta ou validada com o usuário
- Quando um **novo termo** do domínio for identificado
- Quando um **novo fluxo** for mapeado
- Quando uma **regra existente** precisar ser corrigida ou detalhada

### Regras sobre este banco:
- **Nunca inventar** regras — sempre validar com o usuário ou documentação oficial
- **Nunca deletar** regras — se uma regra for invalidada, marque como `[OBSOLETA]` com justificativa
- Manter numeração sequencial sem gaps (não reutilizar IDs deletados)
- Referenciar este banco em toda implementação que envolva lógica de negócio
