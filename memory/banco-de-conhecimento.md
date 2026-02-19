# Banco de Conhecimento — Domínio de Negócio

> Consultado pelo **Curador de Conhecimento** (Agente 03) para validar toda lógica de negócio.
> Contém o conhecimento do domínio: glossário, regras, fluxos, entidades e relacionamentos.
> **Nenhuma regra de negócio pode ser inventada** — toda lógica deve estar documentada aqui.

---

## Contexto da Plataforma

**vigiacontratos** é um sistema de gestão contratual municipal que centraliza o controle de todos os contratos da prefeitura em um único painel, garantindo que nenhum contrato vença sem o devido acompanhamento e evitando riscos jurídicos por falta de controle.

**Modelo de negócio:** SaaS (Software as a Service) com banco isolado por prefeitura (database-per-tenant), projetado para venda como produto nacional para múltiplos municípios com total isolamento de dados, segurança jurídica e conformidade com LGPD (ADR-042).

### O que o sistema FAZ:
- Cadastra e gerencia contratos municipais (serviços, obras, compras, locação)
- Emite alertas automáticos de vencimento com antecedência configurável
- Registra e controla aditivos contratuais (prazo, valor, supressão)
- Gerencia fornecedores vinculados aos contratos
- Armazena documentos digitalizados dos contratos (PDF) com versionamento e hash de integridade
- Fornece dashboard com visão geral da situação contratual
- Gera relatórios gerenciais para tomada de decisão
- Calcula score de risco automático para cada contrato
- Registra e acompanha a execução financeira dos contratos
- Mantém auditoria completa de todas as alterações (log de auditoria)
- Gerencia fiscais de contrato com histórico de trocas
- Atende múltiplas prefeituras com isolamento total de dados (multi-tenant)

### O que o sistema NÃO FAZ:
- Não realiza licitações (apenas registra o número do processo licitatório)
- Não emite notas fiscais ou faz gestão financeira/pagamentos
- Não faz gestão de almoxarifado ou patrimônio
- Não substitui o sistema contábil da prefeitura
- Não é um portal de transparência pública (é sistema interno)

### Para quem:
- **Admin SaaS** — Gestor da plataforma que gerencia tenants (prefeituras-clientes), provisiona novos municípios e monitora a saúde do sistema
- **Administrador Geral** — TI / Controladoria Central — configura o sistema, gerencia usuários e permissões, acessa todas as secretarias
- **Controladoria Interna** — Visualização total, painel de risco, relatórios TCE, pareceres internos
- **Secretário Municipal** — Acesso restrito à própria secretaria, aprovação de aditivos no workflow
- **Gestor de Contrato** — Perfil operacional: cadastro, atualização, documentos, solicitação de aditivos
- **Fiscal de Contrato** — Perfil técnico: relatórios de fiscalização, ocorrências, inconformidades
- **Financeiro / Contabilidade** — Empenhos, saldo contratual, pagamentos, relatórios financeiros
- **Procuradoria Jurídica** — Análise de aditivos, pareceres jurídicos, validação de prorrogações
- **Gabinete / Prefeito** — Visão estratégica: painéis executivos, contratos críticos, mapa de risco

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
| Reequilíbrio Econômico-Financeiro | Restabelecimento da relação entre encargos do contratado e remuneração da Administração, quando eventos imprevistos alteram os custos iniciais | "Aditivo de reequilíbrio por aumento do IPCA acima do previsto" |
| Percentual Acumulado | Soma percentual de todos os acréscimos sobre o valor original do contrato, usada para controle do limite legal | "Percentual acumulado de 18% sobre o valor original" |
| Limite Legal de Aditamento | Percentual máximo que o valor do contrato pode ser acrescido via aditivos (25% para serviços/compras, 50% para obras — art. 65 Lei 8.666 e art. 125 Lei 14.133) | "Limite legal para serviços: 25% do valor original" |
| Fundamentação Legal do Aditivo | Dispositivo legal que autoriza o aditamento (art. 65 da Lei 8.666/93 ou art. 125 da Lei 14.133/21) | "Art. 65, II, 'd', da Lei 8.666/93" |
| Número Sequencial do Aditivo | Ordem cronológica do aditivo em relação ao contrato (1º, 2º, 3º...) | "3º Termo Aditivo ao Contrato 001/2026" |
| Parecer Jurídico de Aditivo | Documento da procuradoria/assessoria jurídica que analisa a legalidade e cabimento do aditivo | "Parecer Jurídico nº 45/2026 — aprovado pela PGM" |
| Pasta Digital do Contrato | Conjunto organizado de documentos vinculados a um contrato, agrupados por tipo e com estrutura hierárquica padrão | "Pasta do Contrato 001/2026 contém: contrato original, publicação, parecer, empenhos, notas fiscais" |
| Completude Documental | Grau de conformidade do acervo de documentos de um contrato em relação ao checklist de documentos obrigatórios | "Contrato com completude Completo (verde) possui todos os documentos obrigatórios" |
| Log de Acesso a Documento | Registro imutável de toda ação sobre um documento: quem acessou, quando, de qual IP e qual ação foi realizada | "Log registra: fiscal João baixou o contrato original em 18/02/2026 às 14h30 via IP 192.168.1.1" |
| Versão do Documento | Número sequencial que identifica cada geração de um documento do mesmo tipo no mesmo contrato | "Contrato original — v1 (original), v2 (reimpressão com correção)" |
| Estrutura Documental Padrão | Conjunto de tipos de documentos esperados por padrão em qualquer contrato municipal (checklist-base) | "Toda pasta deve ter: contrato original, publicação oficial, parecer jurídico, nota de empenho" |
| Relatório para Tribunal de Contas | Relatório gerado automaticamente pelo sistema listando todos os documentos de um contrato com datas de upload, responsável e status de completude | "Relatório TCE gerado em PDF para o Contrato 001/2026 com 12 documentos listados" |
| Painel de Risco Administrativo | Dashboard estratégico dedicado à análise e classificação de riscos contratuais, com indicadores visuais para Controladoria, Jurídico e Prefeito | "Painel de Risco mostra 8 contratos críticos na Secretaria de Obras" |
| Categoria de Risco | Classificação do tipo de risco identificado em um contrato: vencimento, financeiro, documental, jurídico ou operacional | "Contrato com categorias de risco: financeiro + documental" |
| Score de Risco Expandido | Pontuação 0-100 calculada automaticamente com base em 5 categorias de risco (vencimento, financeiro, documental, jurídico, operacional) — expansão do score_risco existente | "Score expandido: 72 (alto) — critérios: vencimento +15, documental +20, jurídico +15, operacional +20" |
| Mapa de Risco por Secretaria | Visão agregada de risco por secretaria, mostrando total de contratos e quantidade de críticos por órgão | "Saúde → 12 contratos (3 críticos), Obras → 25 contratos (8 críticos)" |
| Relatório de Risco para TCE | Relatório automatizado em PDF com lista de contratos monitorados, justificativa de risco, plano de ação e histórico de alertas — instrumento de defesa administrativa | "Relatório de Risco TCE gerado com 15 contratos monitorados e justificativas detalhadas" |
| Alerta Preventivo Inteligente | Alerta contextualizado que descreve especificamente o risco identificado com mensagem acionável | "Contrato 015/2023 ultrapassou 25% de aditivo — ação necessária" |
| Contrato em Risco | Contrato que possui score de risco acima de 30 (médio ou alto), sinalizando necessidade de ação administrativa | "3 contratos em risco na Secretaria de Educação" |

### Termos do Sistema

| Termo | Definição | Exemplo de Uso |
|---|---|---|
| Alerta | Notificação automática gerada quando um contrato está próximo do vencimento | "Alerta urgente: contrato vence em 15 dias" |
| Prioridade do Alerta | Nível de urgência do alerta baseado na proximidade do vencimento | "Informativo (>30d), Atenção (≤30d), Urgente (≤7d)" |
| Configuração de Alerta | Definição dos prazos de antecedência para disparo de alertas | "Admin configurou alertas em 120, 90, 60, 30, 15 e 7 dias antes" |
| Motor de Monitoramento | Processo automatizado (cron job diário) que verifica todos os contratos ativos e gera alertas | "Motor executou às 06:00 e identificou 12 contratos na janela de alerta" |
| Janela de Alerta | Período em que um contrato se encontra dentro de algum prazo configurado de antecedência | "Contrato entrou na janela de 90 dias antes do vencimento" |
| Contrato Crítico / Essencial | Contrato classificado como essencial para o funcionamento do município (merenda, transporte, coleta) | "Contrato de transporte escolar é essencial — alertas com frequência aumentada" |
| Bloqueio Preventivo / Modo Irregular | Status visual de destaque vermelho aplicado a contratos vencidos, impedindo ações irregulares | "Contrato vencido marcado como IRREGULAR — aditivo retroativo bloqueado" |
| Regularização de Alerta | Ação que resolve um alerta: registro de aditivo, nova vigência ou encerramento formal | "Alerta resolvido após aditivo de prazo registrado" |
| Canal de Notificação | Meio pelo qual a notificação é enviada ao destinatário | "V1: email institucional e notificação interna no sistema" |
| Log de Notificação | Registro de cada tentativa de envio de notificação, com status de sucesso/falha | "Email enviado com sucesso para fiscal@prefeitura.gov.br" |
| Destinatário de Alerta | Pessoa que recebe a notificação de um alerta (fiscal, secretário, controlador, admin) | "Alerta enviado ao fiscal Maria Souza e ao secretário João" |
| Dashboard de Alertas | Painel específico com indicadores de contratos por faixa de vencimento e filtros | "Dashboard mostra 5 contratos vencendo em 30 dias" |
| Dashboard | Painel principal com visão geral dos contratos e indicadores | "Dashboard mostra 5 contratos vencendo este mês" |
| Painel Executivo | Dashboard estratégico com visão geral financeira, mapa de risco, vencimentos e ranking por secretaria | "Painel Executivo mostra R$ 28 milhões sob gestão contratual" |
| Score de Gestão | Nota de 0 a 100 que avalia a saúde da gestão contratual do município | "Score 82/100 — Gestão Controlada" |
| Mapa de Risco Contratual | Classificação visual dos contratos por criticidade (verde/amarelo/vermelho) no dashboard executivo | "70% regular, 20% atenção, 10% crítico" |
| Ticket Médio | Valor médio por contrato ativo (valor_total_contratado / total_contratos_ativos) | "Ticket médio: R$ 224.000" |
| Dados Agregados | Métricas pré-calculadas diariamente para performance do dashboard executivo | "Dashboard carrega em <2s com dados agregados" |
| Visão do Controlador | Perfil especial do dashboard com foco em irregularidades e log de alterações | "Controlador vê contratos alterados nos últimos 30 dias" |
| Tendência Mensal | Comparativo mensal de indicadores (contratos ativos, risco médio, volume financeiro) | "Tendência: risco médio caiu 5% no último mês" |
| Histórico de Alterações | Log automático de toda modificação em dados de contrato | "Alteração: valor_global de R$ 100.000 para R$ 150.000 por João em 18/02/2026" |
| Cadastro Multi-etapa | Formulário de contrato dividido em passos (wizard) para garantir qualidade dos dados | "Etapas: Identificação → Fornecedor → Financeiro → Vigência → Fiscal → Documentos" |
| Painel de Risco | Página dedicada com visão completa de riscos contratuais: indicadores semáforo, ranking por score e mapa por secretaria | "Painel de Risco mostra 5 contratos críticos vencendo em 30 dias" |
| Ranking de Risco | Tabela automática ordenada por score de risco (maior para menor) com categorias de risco identificadas | "Ranking exibe Contrato 015/2023 com score 82 (financeiro + jurídico)" |
| Relatório de Risco TCE | Documento PDF gerado automaticamente com justificativa de riscos por contrato, plano de ação e histórico de alertas | "Relatório TCE exportado com 15 contratos monitorados" |
| Tenant / Prefeitura-Cliente | Município inscrito na plataforma SaaS, com banco de dados isolado e storage próprio | "Tenant: Prefeitura de São Paulo — banco vigiacontratos_pref_sao_paulo" |
| Banco Central / Master | Banco de dados principal do SaaS que armazena informações de tenants, autenticação inicial e configurações globais | "Banco master contém tabela tenants com 15 prefeituras ativas" |
| Hash de Integridade | Código SHA-256 gerado a partir do conteúdo de um documento no momento do upload, usado para provar que o arquivo não foi alterado | "Hash: a3f2b8c9... — documento verificado, integridade confirmada" |
| MFA (Autenticação Multi-Fator) | Mecanismo de segurança opcional que exige segundo fator (TOTP via app autenticador) além da senha para login | "Admin ativou MFA — login exige senha + código do Google Authenticator" |
| Base Legal (LGPD) | Fundamento jurídico que autoriza o tratamento de dados pessoais (consentimento, execução contratual, obrigação legal, etc.) | "Base legal para CNPJ de fornecedores: execução contratual" |
| Política de Retenção | Regra que define por quanto tempo dados pessoais e documentos devem ser mantidos antes de serem anonimizados ou excluídos | "Política de retenção: logs de acesso mantidos por 5 anos" |
| Admin SaaS | Administrador da plataforma com acesso ao banco central/master, responsável por gerenciar prefeituras-clientes (tenants) | "Admin SaaS provisionou novo tenant para Prefeitura de Campinas" |
| Log de Login | Registro de cada tentativa de acesso ao sistema (sucesso ou falha), com IP, user-agent e timestamp | "Log: login falho de IP 187.x.x.x — 3ª tentativa, conta bloqueada" |
| RBAC (Role-Based Access Control) | Sistema de controle de acesso baseado em papéis (roles) atribuídos a usuários, com permissões granulares por recurso e ação | "Sistema opera com RBAC — permissões por role, secretaria e ação" |
| Perfil de Usuário (Role) | Papel funcional dinâmico (tabela `roles`) que define permissões e restrições de acesso no sistema | "Usuário com perfil Gestor de Contrato — acesso operacional" |
| Permissão Granular | Controle de acesso por ação específica no formato `{recurso}.{ação}` | "$user->hasPermission('contrato.editar')" |
| Permissão por Secretaria | Restrição de acesso a contratos/dados de secretarias específicas vinculadas ao usuário | "Gestor acessa apenas contratos da Secretaria de Obras e Transporte" |
| Permissão Temporária | Acesso com data de expiração (`expires_at`) para substituições durante férias | "Acesso temporário válido até 30/03/2026" |
| Workflow de Aprovação | Fluxo sequencial de aprovações por perfis distintos com registro formal de cada etapa | "Aditivo segue workflow: Gestor → Secretário → Jurídico → Controladoria → Homologação" |
| Segregação de Função | Princípio de separação de responsabilidades entre perfis para evitar concentração de poder e risco de fraude | "Gestor cadastra, Secretário aprova, Fiscal fiscaliza — ninguém faz tudo" |
| Homologação | Etapa final do workflow de aprovação que formaliza a aprovação institucional de uma solicitação | "Aditivo homologado pelo Administrador Geral em 18/02/2026" |

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
| `nota_empenho` | Nota de Empenho | Documento de reserva de recurso orçamentário |
| `nota_fiscal` | Nota Fiscal | Nota fiscal emitida pelo fornecedor |
| `ordem_servico` | Ordem de Serviço | Ordem para início dos serviços |
| `relatorio_medicao` | Relatório de Medição | Relatório de medição de serviços executados |
| `relatorio_fiscalizacao` | Relatório de Fiscalização | Relatório emitido pelo fiscal do contrato |
| `justificativa` | Justificativa | Documento de justificativa administrativa |
| `documento_complementar` | Documento Complementar | Documentos complementares não classificados acima |

### CategoriaRisco

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `vencimento` | Risco de Vencimento | Contrato ou aditivo próximo do vencimento, empenho insuficiente |
| `financeiro` | Risco Financeiro | Valor empenhado excede contratado, aditivos acima de limite, falta de saldo |
| `documental` | Risco Documental | Falta de certidões, publicação, termo de fiscalização, relatório de execução ou documento vencido |
| `juridico` | Risco Jurídico | Renovação irregular, prazo superior ao permitido, aditivos suspeitos, ausência de justificativa |
| `operacional` | Risco Operacional | Contrato essencial vencendo, serviço continuado sem renovação formal |

### TipoAditivo

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `prazo` | Prorrogação de Prazo | Aditivo que altera apenas o prazo de vigência |
| `valor` | Acréscimo de Valor | Aditivo que altera apenas o valor do contrato (acréscimo) |
| `prazo_e_valor` | Prazo e Valor | Aditivo que altera prazo e valor simultaneamente (acréscimo simples, sem reequilíbrio) |
| `supressao` | Supressão de Valor | Aditivo que reduz valor ou escopo do contrato |
| `reequilibrio` | Reequilíbrio Econômico-Financeiro | Aditivo que recompõe o equilíbrio econômico-financeiro do contrato por variação de insumos/índices |
| `alteracao_clausula` | Alteração de Cláusula | Aditivo que altera cláusulas contratuais não financeiras e não temporais |
| `misto` | Misto | Aditivo que combina múltiplos tipos de alteração (ex: prazo + supressão + acréscimo simultâneos) |

### StatusAditivo

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `vigente` | Vigente | Aditivo ativo e em vigor |
| `vencido` | Vencido | Aditivo com prazo expirado |
| `cancelado` | Cancelado | Aditivo cancelado |

### StatusCompletudeDocumental

| Valor (Enum) | Nome Exibido | Descrição | Indicador |
|---|---|---|---|
| `completo` | Completo | Contrato possui todos os documentos obrigatórios do checklist | Verde |
| `parcial` | Parcial | Contrato possui alguns documentos obrigatórios, mas faltam itens | Amarelo |
| `incompleto` | Incompleto | Contrato não possui nenhum ou quase nenhum documento obrigatório | Vermelho |

### AcaoLogDocumento

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `upload` | Upload | Documento foi enviado ao sistema |
| `download` | Download | Documento foi baixado por um usuário |
| `substituicao` | Substituição | Nova versão foi carregada, substituindo a anterior |
| `exclusao` | Exclusão | Documento foi marcado como excluído (soft delete) |
| `visualizacao` | Visualização | Documento foi visualizado/aberto no sistema |

### Perfis de Usuário (tabela `roles` — dinâmica)

> Nota: O antigo enum `TipoUsuario (admin, gestor, consulta)` foi substituído por tabela `roles` dinâmica (ADR-050). Os 8 perfis abaixo são criados via seeder com `is_padrao = true`. O admin pode criar perfis customizados adicionais.

| Identificador (nome) | Nome Exibido | Descrição | Ocupantes Típicos | Pode | Não Pode |
|---|---|---|---|---|---|
| `administrador_geral` | Administrador Geral | TI / Controladoria Central | Controlador Interno, TI Municipal, Procuradoria | Criar usuários, definir permissões, acessar todas secretarias, visualizar todos contratos, acessar logs de auditoria, configurar parâmetros de risco e alertas globais | Alterar contratos sem registro de log, excluir histórico sem trilha |
| `controladoria` | Controladoria Interna | Perfil estratégico — essencial para vender o sistema | Controlador Interno, auditores internos | Visualizar todos os contratos, painel de risco completo, gerar relatórios TCE, inserir observações técnicas, registrar parecer interno | Alterar dados financeiros, excluir documentos |
| `secretario` | Secretário Municipal | Acesso restrito à própria secretaria | Secretários de pasta | Visualizar contratos da sua pasta, acompanhar risco, receber alertas, aprovar aditivos (workflow), visualizar documentos | Ver contratos de outras secretarias, alterar contratos homologados |
| `gestor_contrato` | Gestor de Contrato | Perfil operacional | Servidores do setor de contratos | Cadastrar contrato, atualizar informações, anexar documentos, solicitar aditivos, atualizar execução, inserir relatórios mensais | Aprovar aditivo sozinho, excluir contrato homologado |
| `fiscal_contrato` | Fiscal de Contrato | Perfil técnico de acompanhamento — muito valorizado em auditorias | Servidores designados como fiscais (portaria) | Inserir relatório de fiscalização, registrar ocorrência/atraso/inconformidade, anexar fotos/documentos | Alterar valores, aprovar aditivos |
| `financeiro` | Financeiro / Contabilidade | Perfil financeiro | Contadores, servidores do setor financeiro | Registrar empenhos, atualizar saldo contratual, registrar pagamentos, emitir relatórios financeiros | Alterar dados jurídicos, aprovar prorrogações |
| `procuradoria` | Procuradoria Jurídica | Perfil jurídico | Procuradores municipais, assessores jurídicos | Visualizar contratos, analisar aditivos, emitir parecer jurídico, validar prorrogações, aprovar juridicamente aditivos | Alterar valores contratuais |
| `gabinete` | Gabinete / Prefeito | Visão estratégica executiva — perfil ótimo para vender o sistema | Prefeito, vice-prefeito, chefe de gabinete | Visualizar painel executivo, contratos críticos, mapa de risco, baixar relatório consolidado | Acesso operacional (não cria, não edita, não exclui) |

### StatusAprovacao

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `pendente` | Pendente | Aguardando análise do responsável da etapa |
| `aprovado` | Aprovado | Aprovado pelo responsável da etapa |
| `reprovado` | Reprovado | Reprovado com justificativa obrigatória |

### EtapaWorkflow

| Valor (Enum) | Nome Exibido | Descrição | Perfil Responsável |
|---|---|---|---|
| `solicitacao` | Solicitação | Gestor solicita o aditivo | gestor_contrato |
| `aprovacao_secretario` | Aprovação do Secretário | Secretário da pasta analisa | secretario |
| `parecer_juridico` | Parecer Jurídico | Procuradoria emite parecer | procuradoria |
| `validacao_controladoria` | Validação da Controladoria | Controladoria valida conformidade | controladoria |
| `homologacao` | Homologação | Aprovação final | administrador_geral |

### StatusAlerta

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `pendente` | Pendente | Alerta gerado, notificação ainda não enviada |
| `enviado` | Enviado | Notificação enviada ao(s) destinatário(s) |
| `visualizado` | Visualizado | Alerta lido pelo usuário no sistema |
| `resolvido` | Resolvido | Alerta tratado (contrato renovado, encerrado, etc.) |

### PrioridadeAlerta

| Valor (Enum) | Nome Exibido | Descrição | Condição |
|---|---|---|---|
| `informativo` | Informativo | Vencimento distante, apenas para ciência | Mais de 30 dias para vencimento |
| `atencao` | Atenção | Vencimento se aproximando, requer planejamento | 30 dias ou menos |
| `urgente` | Urgente | Vencimento iminente, ação imediata necessária | 7 dias ou menos |

### CanalNotificacao

| Valor (Enum) | Nome Exibido | Descrição | Disponível |
|---|---|---|---|
| `email` | Email | Notificação via email institucional | V1 |
| `sistema` | Sistema | Notificação interna no sistema (sino/badge) | V1 |

### TipoEventoAlerta

| Valor (Enum) | Nome Exibido | Descrição |
|---|---|---|
| `vencimento_vigencia` | Vencimento de Vigência | Contrato próximo da data de fim |
| `termino_aditivo` | Término de Aditivo | Aditivo próximo do vencimento |
| `prazo_garantia` | Prazo de Garantia | Prazo de garantia próximo do vencimento (se aplicável) |
| `prazo_execucao_fisica` | Prazo de Execução Física | Prazo de execução de obra próximo do fim |

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
| RN-022 | Tamanho máximo de upload: 20MB por arquivo | Validação no Form Request (ADR-032 — ampliado de 10MB para 20MB) |
| RN-039 | Upload múltiplo de documentos é permitido | Vários arquivos podem ser enviados de uma vez |
| RN-040 | Todo documento deve ter tipo classificado | tipo_documento obrigatório (contrato_original, termo_referencia, etc.) |
| RN-041 | Documentos possuem versionamento | Ao reuplodar documento do mesmo tipo, versão é incrementada automaticamente |
| RN-042 | Registro automático de quem anexou o documento | uploaded_by + data/hora registrados automaticamente |

### Módulo: Documentos — Central de Documentos (Módulo 5)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-118 | Cada contrato possui uma pasta digital própria com estrutura hierárquica padrão | A pasta é identificada por `contrato_id` e organizada por tipo de documento. Os tipos obrigatórios padrão são: contrato_original, publicacao_oficial, parecer_juridico, nota_empenho |
| RN-119 | O limite de tamanho por arquivo é 20MB | Validação no StoreDocumentoRequest. Aplica-se a todos os tipos de documento (ADR-032) |
| RN-120 | O versionamento é automático e não-destrutivo | Ao fazer upload de documento do mesmo tipo no mesmo contrato, o sistema cria nova versão. A versão anterior não é deletada — apenas `is_versao_atual` é setado para false |
| RN-121 | O nome do arquivo é padronizado automaticamente pelo sistema | Formato: `contrato_{numero_contrato}_{tipo_documento}_v{versao}.pdf`. Nome original do usuário é preservado no campo `nome_original` |
| RN-122 | Todo acesso a documento é registrado no log de acesso | Ações logadas: upload, download, substituição, exclusão, visualização. Campos: user_id, acao, documento_id, ip_address, created_at. Tabela append-only (ADR-035) |
| RN-123 | Documentos são armazenados em diretórios isolados por contrato e tipo | Estrutura de storage: `documentos/contratos/{contrato_id}/{tipo_documento}/{arquivo}`. Nunca acessíveis via URL pública (ADR-033) |
| RN-124 | Contrato vigente sem documento do tipo contrato_original gera alerta de incompletude no dashboard | Alerta visual na listagem e no dashboard de documentos. Eleva score de risco (critério: sem documento — +20 pontos) |
| RN-125 | Aditivo sem documento do tipo aditivo_doc gera alerta de incompletude | Toda entidade Aditivo deve ter pelo menos um documento do tipo aditivo_doc vinculado |
| RN-126 | Prorrogação de prazo sem parecer_juridico vinculado gera alerta | Validação ativa quando aditivo.tipo = prazo e não existe documento tipo parecer_juridico vinculado ao aditivo |
| RN-127 | Contrato com valor_global acima de R$ 500.000 sem publicacao_oficial gera alerta de incompletude | Alerta visual e indicador no dashboard de documentos |
| RN-128 | A completude documental do contrato é classificada em três níveis | Completo (verde): possui todos os documentos do checklist obrigatório. Parcial (amarelo): possui pelo menos o contrato_original mas faltam outros. Incompleto (vermelho): não possui contrato_original |
| RN-129 | O checklist de documentos obrigatórios por contrato é configurável | Configuração padrão do sistema: contrato_original, publicacao_oficial, parecer_juridico, nota_empenho. Admin pode personalizar o checklist por tipo de contrato |
| RN-130 | Acesso a documentos é controlado por perfil de usuário (RBAC) | Permissões via role + secretaria vinculada. Administrador Geral e Controladoria: acesso total. Gestor/Fiscal: upload + download para contratos das secretarias vinculadas. Gabinete: somente leitura. Implementado via DocumentoPolicy + verificação de permissão `documento.{ação}` (RN-302) |
| RN-131 | A busca de documentos suporta filtros combinados | Por número de contrato, fornecedor, tipo de documento, palavra-chave no nome do arquivo, período de upload |
| RN-132 | O dashboard de documentos exibe 4 indicadores de completude | (1) % contratos com documentação completa; (2) total de contratos sem contrato_original; (3) total de aditivos sem documento vinculado; (4) ranking das 5 secretarias com maior pendência documental |
| RN-133 | O relatório para Tribunal de Contas lista todos os documentos de um contrato | Campos: tipo_documento, nome_arquivo, versao, data_upload, responsável (uploaded_by), status. Exportável em PDF via RelatorioService |
| RN-134 | Documentos excluídos não são removidos do storage | Exclusão é lógica (soft delete via campo `deleted_at`). Log de exclusão registrado. Admin pode restaurar |
| RN-135 | Funcionalidades de OCR e busca full-text em PDF são Fase 2 | OCR (Tesseract ou cloud API), extração automática de CNPJ/datas/valores, busca interna no conteúdo do PDF e auto-preenchimento de formulários são funcionalidades de evolução futura. Não implementar em V1 (ADR-037) |

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
| RN-143 | O Painel de Risco é uma página dedicada acessível via menu lateral | Rota: `/painel-risco`. Acessível por todos os perfis (admin, gestor, consulta). Dashboard Executivo mantém Bloco 2 como resumo com link "Ver detalhes" (ADR-039) |
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

### Fluxo 12: Gestão de Documentos na Central de Documentos (Módulo 5)

```
[1. Usuário acessa contrato → aba Documentos OU acessa Central de Documentos]
       │
       ▼
[2. Sistema carrega pasta digital do contrato (RN-118)]
   ├── Lista documentos agrupados por tipo
   ├── Exibe status de completude (RN-128)
   └── Exibe checklist de documentos obrigatórios (RN-129)
       │
       ▼
[3. Usuário clica em "Adicionar Documento"]
       │
       ▼
[4. Preenche dados do upload]
   ├── Tipo do documento (obrigatório — TipoDocumentoContratual) (RN-040)
   ├── Seleciona arquivo PDF (obrigatório — RN-021)
   ├── Descrição (opcional)
   └── Confirma upload
       │
       ▼
[5. Sistema valida]
   ├── Tipo MIME = application/pdf (RN-021)
   ├── Tamanho ≤ 20MB (RN-119)
   └── Tipo de documento preenchido (RN-040)
       │
   ┌───┴───┐
   ▼       ▼
[OK]    [Erro → Exibe erro de validação]
   │
   ▼
[6. Sistema determina versão]
   ├── Busca documentos existentes do mesmo tipo no contrato
   ├── Se existe → versao = MAX(versao) + 1; is_versao_atual dos anteriores = false
   └── Se não existe → versao = 1 (RN-120)
       │
       ▼
[7. Sistema salva documento]
   ├── Gera nome padronizado de arquivo (RN-121)
   ├── Armazena em documentos/contratos/{contrato_id}/{tipo_documento}/ (RN-123)
   ├── Cria registro na tabela documentos com uploaded_by + created_at (RN-042)
   └── Marca is_versao_atual = true
       │
       ▼
[8. Sistema registra log de acesso]
   └── Ação: upload | user_id | documento_id | ip_address | created_at (RN-122)
       │
       ▼
[9. Sistema recalcula completude documental do contrato (RN-128)]
   ├── Verifica checklist obrigatório (RN-129)
   ├── Atualiza status_completude (completo/parcial/incompleto)
   └── Se agora completo → remove alerta de incompletude do dashboard (RN-124)
       │
       ▼
[10. Score de risco do contrato recalculado]
    └── Se contrato_original presente → remove critério "sem documento" do score (RN-029)
```

**Regras associadas:** RN-020 a RN-022, RN-039 a RN-042, RN-118 a RN-134

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

[DashboardAgregado] (tabela de agregação — dados pré-calculados do painel executivo)

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
- Avanço sequencial obrigatório (RN-337)

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
- 0-29 → `baixo` (🟢)
- 30-59 → `medio` (🟡)
- 60-100 → `alto` (🔴)

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

### Fórmula: Completude Documental do Contrato

```
checklist_obrigatorio = [contrato_original, publicacao_oficial, parecer_juridico, nota_empenho]
(configurável pelo admin — RN-129)

documentos_presentes = documentos WHERE contrato_id = X AND is_versao_atual = true AND deleted_at IS NULL
tipos_presentes = DISTINCT tipo_documento dos documentos_presentes
tipos_no_checklist_presentes = INTERSECT(tipos_presentes, checklist_obrigatorio)

SE COUNT(tipos_no_checklist_presentes) = COUNT(checklist_obrigatorio):
    status_completude = completo (verde)
SE COUNT(tipos_no_checklist_presentes) > 0 E contrato_original IN tipos_presentes:
    status_completude = parcial (amarelo)
SE contrato_original NOT IN tipos_presentes:
    status_completude = incompleto (vermelho)
```

| Variável | Descrição | Fonte |
|---|---|---|
| checklist_obrigatorio | Lista de tipos de documento obrigatórios | Configuração do sistema (RN-129) |
| documentos_presentes | Documentos ativos (versão atual, não deletados) do contrato | Tabela `documentos` filtrada |
| tipos_no_checklist_presentes | Tipos obrigatórios que possuem ao menos um documento | Interseção lógica |

*Nota: RN-128. Calculado pelo DocumentoObserver ao criar/excluir documento.*

### Fórmula: Indicadores do Dashboard de Documentos

```
pct_contratos_completos     = COUNT(contratos WHERE status_completude = completo AND status = vigente) / total_contratos_ativos * 100
total_sem_contrato_original = COUNT(contratos WHERE status = vigente AND NOT EXISTS(documentos WHERE tipo_documento = contrato_original AND is_versao_atual = true AND deleted_at IS NULL))
total_aditivos_sem_doc      = COUNT(aditivos WHERE status = vigente AND NOT EXISTS(documentos WHERE tipo_documento = aditivo_doc AND is_versao_atual = true))
ranking_secretarias_pendencia = TOP 5 secretarias ORDER BY (contratos WHERE status_completude != completo / total_contratos_secretaria) DESC
```

| Indicador | Descrição | Regra |
|---|---|---|
| % contratos completos | Percentual de contratos ativos com documentação completa | RN-132 (1) |
| Total sem contrato original | Contratos ativos sem o documento mais básico | RN-132 (2) |
| Total aditivos sem documento | Aditivos vigentes sem documento vinculado | RN-132 (3) |
| Ranking secretarias pendentes | Top 5 secretarias com maior % de contratos incompletos | RN-132 (4) |

*Nota: RN-132. Exibidos no dashboard da Central de Documentos.*

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

---

## Regras de Negócio — Multi-Tenant (Bloco RN-200)

> Regras que governam o isolamento de dados e operação multi-prefeitura do SaaS.

| ID | Regra | Detalhamento |
|---|---|---|
| RN-200 | Cada prefeitura opera em banco de dados isolado (database-per-tenant) | Um banco MySQL dedicado por prefeitura-cliente. Sem compartilhamento de tabelas de negócio entre tenants (ADR-042) |
| RN-201 | Dados de uma prefeitura nunca são acessíveis por outra | Isolamento total — nenhum mecanismo de query cross-tenant (sem UNION, sem JOIN entre connections). Middleware `SetTenantConnection` garante escopo |
| RN-202 | Migrations devem ser aplicadas em todos os bancos tenant simultaneamente | Comando artisan dedicado percorre todos os tenants ativos e aplica migrations pendentes em cada banco |
| RN-203 | Admin SaaS pode gerenciar tenants (criar, ativar, desativar) | Operações no banco master: provisionar novo banco, aplicar migrations, criar admin inicial, configurar storage. Desativação é soft (is_ativo = false) |
| RN-204 | Storage de arquivos isolado por tenant (bucket/pasta separada) | Estrutura S3: `{tenant_slug}/documentos/contratos/{contrato_id}/{tipo}/...`. Nunca misturar arquivos de tenants diferentes |
| RN-205 | Cache Redis isolado por tenant (prefixo de chave) | Chaves Redis: `tenant_{id}:dashboard`, `tenant_{id}:painel_risco`. Evita colisão entre tenants |
| RN-206 | Jobs/Queues devem carregar contexto do tenant | Todo job assíncrono recebe `tenant_id` no payload e configura connection antes de executar |

---

## Regras de Negócio — LGPD (Bloco RN-210)

> Regras de conformidade com a Lei Geral de Proteção de Dados (Lei 13.709/2018).

| ID | Regra | Detalhamento |
|---|---|---|
| RN-210 | Todo tratamento de dados pessoais deve ter base legal registrada | Cada tipo de dado pessoal (CNPJ fornecedor, dados de fiscal, contatos) deve ter base legal identificada (execução contratual, obrigação legal, etc.) |
| RN-211 | Acesso a dados sensíveis deve ser logado | Login: `login_logs`. Documentos: `log_acesso_documentos`. Dados pessoais: auditoria via `historico_alteracoes`. Logs são imutáveis (append-only) |
| RN-212 | Política de retenção de dados configurável por tenant | Cada prefeitura define por quanto tempo manter dados pessoais e logs. Padrão: 5 anos (compatível com prazos legais de guarda de documentos públicos) |
| RN-213 | Dados pessoais devem ser anonimizáveis sob solicitação | O sistema deve permitir anonimizar dados pessoais de fornecedores e fiscais quando solicitado formalmente, mantendo integridade dos contratos |

---

## Regras de Negócio — Auditoria Expandida (Bloco RN-220)

> Regras de auditoria e conformidade para segurança jurídica e proteção contra adulteração.

| ID | Regra | Detalhamento |
|---|---|---|
| RN-220 | Todo documento recebe hash SHA-256 no momento do upload | `$hash = hash('sha256', file_get_contents($arquivo))` — armazenado no campo `hash_integridade` do Model Documento |
| RN-221 | Hash de integridade verificável a qualquer momento | O sistema permite recalcular o hash do arquivo em storage e comparar com o hash armazenado. Divergência indica possível adulteração |
| RN-222 | Relatório de logs exportável por período | O sistema gera relatório de auditoria filtrável por período, tipo de ação, usuário e entidade. Exportável em PDF e CSV |
| RN-223 | Logs de login registram IP, user-agent, sucesso/falha | Tabela `login_logs` com campos: user_id, tenant_id, ip_address, user_agent, success (boolean), created_at. Append-only |
| RN-224 | Histórico de auditoria é imutável (append-only) | Tabelas `historico_alteracoes`, `log_acesso_documentos`, `log_notificacoes` e `login_logs` não permitem UPDATE nem DELETE |
| RN-225 | Relatório de conformidade documental | Lista documentos com hash de integridade, data de upload, responsável, verificação de integridade e status — instrumento de defesa para auditoria externa |

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
- Manter numeração sequencial sem gaps dentro de cada bloco (não reutilizar IDs)
- Blocos de numeração: RN-001 a RN-155 (core), RN-200+ (multi-tenant), RN-210+ (LGPD), RN-220+ (auditoria)
- Referenciar este banco em toda implementação que envolva lógica de negócio
