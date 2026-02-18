# vigiacontratos — Índice Rápido

## Status

| Campo | Valor |
|---|---|
| Tipo | Sistema de gestão contratual municipal |
| Stack | Laravel 12, PHP 8.2+, MySQL 8, Redis |
| Container | Docker / Laravel Sail |
| Fase Atual | Fase 0 — Setup Inicial |
| Última Atualização | 2026-02-18 |

---

## Módulos

| Módulo | Status | Observação |
|---|---|---|
| Dashboard | Planejado | Painel com visão geral de contratos, alertas e indicadores |
| Contratos | Planejado | Cadastro inteligente multi-etapa, score de risco, fiscais, execução financeira, audit trail |
| Fornecedores | Planejado | Cadastro e gestão de empresas fornecedoras |
| Aditivos | Planejado | Aditivos contratuais (prazo, valor, supressão) |
| Alertas | Planejado | Alertas de vencimento com prazos configuráveis |
| Documentos | Planejado | Upload e gestão de documentos (PDF) anexados |
| Relatórios | Planejado | Relatórios gerenciais e exportação |

---

## Entidades do Domínio

### Entidades Principais
- **User** — Usuários do sistema (admin, gestor, consulta)
- **Contrato** — Contratos municipais (cadastro inteligente com score de risco)
- **Fornecedor** — Empresas fornecedoras (com validação de CNPJ)
- **Secretaria** — Secretarias/órgãos da prefeitura
- **Fiscal** — Fiscais de contrato (com histórico de trocas)
- **Aditivo** — Aditivos contratuais
- **Documento** — Documentos anexados (PDF, com tipo e versionamento)
- **ExecucaoFinanceira** — Registros de execução/medição financeira
- **HistoricoAlteracao** — Log de auditoria de alterações (imutável)
- **Alerta** — Alertas de vencimento de contratos
- **ConfiguracaoAlerta** — Prazos configuráveis de alerta

### Tipos/Categorias (Enums)
- **StatusContrato** — vigente, vencido, cancelado, suspenso, encerrado, rescindido
- **TipoContrato** — servico, obra, compra, locacao
- **ModalidadeContratacao** — pregao_eletronico, pregao_presencial, concorrencia, tomada_preco, convite, leilao, dispensa, inexigibilidade, adesao_ata
- **TipoPagamento** — mensal, por_medicao, parcelado, unico
- **CategoriaContrato** — essencial, nao_essencial
- **CategoriaServico** — transporte, alimentacao, tecnologia, obras, limpeza, seguranca, manutencao, saude, educacao, outros
- **NivelRisco** — baixo, medio, alto
- **TipoDocumentoContratual** — contrato_original, termo_referencia, publicacao_oficial, parecer_juridico, aditivo_doc, ordem_servico, outros
- **TipoAditivo** — prazo, valor, prazo_e_valor, supressao
- **StatusAditivo** — vigente, vencido, cancelado
- **TipoUsuario** — admin, gestor, consulta
- **StatusAlerta** — pendente, visualizado, resolvido
- **PrioridadeAlerta** — informativo, atencao, urgente

---

## Decisões Recentes

| Data | Decisão | Contexto |
|---|---|---|
| 2026-02-18 | Stack: Laravel 12 + MySQL 8 + Redis + Docker/Sail | Escolha inicial da stack do projeto |
| 2026-02-18 | Template UI: WowDash (Bootstrap 5) | Template admin com dashboard, componentes e dark mode |
| 2026-02-18 | Perfis: Admin, Gestor, Consulta | 3 níveis de acesso ao sistema |
| 2026-02-18 | Alertas com prazos configuráveis | Admin define dias de antecedência para alertas |
| 2026-02-18 | Cadastro inteligente de contratos (wizard 6 etapas) | Formulário multi-etapa com validação por passo |
| 2026-02-18 | Fiscal como entidade com histórico | Rastreabilidade de trocas de fiscal |
| 2026-02-18 | Audit trail completo (historico_alteracoes) | Toda alteração logada com campo, valor, usuário, IP |
| 2026-02-18 | Score de risco automático | Classificação baixo/médio/alto por critérios objetivos |
| 2026-02-18 | Execução financeira como entidade | Acompanhamento de pagamentos e percentual executado |

---

## Agentes

| # | Agente | Arquivo |
|---|--------|---------|
| 1 | Guardião de Regras | `memory/agents/01-guardiao-de-regras.md` |
| 2 | Gestor de Memória | `memory/agents/02-gestor-de-memoria.md` |
| 3 | Curador de Conhecimento | `memory/agents/03-curador-de-conhecimento.md` |
| 4 | Arquiteto | `memory/agents/04-arquiteto.md` |
| 5 | Engenheiro Executor | `memory/agents/05-engenheiro-executor.md` |
| 6 | Auditor Técnico | `memory/agents/06-auditor-tecnico.md` |

## Bases Detalhadas

| Base | Arquivo |
|------|---------|
| Governança Técnica | `memory/banco-de-regras.md` |
| Estado do Projeto | `memory/banco-de-memoria.md` |
| Domínio de Negócio | `memory/banco-de-conhecimento.md` |
| Tema Visual / UI | `memory/banco-de-tema.md` |
