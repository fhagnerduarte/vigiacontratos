# Conhecimento Core — Contexto, Glossário e Tipos

> Extraído de `banco-de-conhecimento.md`. Carregado em TODAS as tarefas de implementação.
> Consultado por todos os agentes que precisam de contexto de negócio.

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
- **Admin SaaS (Root/Superadmin)** — Proprietário/operador da plataforma SaaS. Opera exclusivamente no banco master. Gerencia tenants (prefeituras-clientes), provisiona novos municípios, ativa/desativa clientes e monitora a saúde da plataforma. Acessa o sistema por rota administrativa dedicada, sem vínculo a subdomínio de tenant.
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
| Score de Risco | Pontuação calculada automaticamente que indica o nível de risco do contrato | "Score 40 → Risco Médio" |
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
| Admin SaaS (Root/Superadmin) | Usuário root da plataforma SaaS. Opera no banco master com acesso irrestrito. Responsável por gerenciar tenants (criar, ativar, desativar prefeituras-clientes), provisionamento de novos bancos e monitoramento da plataforma. Autenticado por guard dedicado (`admin`), sem vínculo a subdomínio de tenant. Distinto do `Administrador Geral` que opera dentro de um tenant específico. | "Admin SaaS provisionou novo tenant para Prefeitura de Campinas via painel administrativo" |
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
