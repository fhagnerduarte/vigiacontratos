# Banco de Memória — Estado do Projeto

> Consultado pelo **Gestor de Memória** (Agente 02) antes de cada ação.
> Atualizado ao final de cada implementação significativa (Etapa 7 do pipeline).
> Este é o registro vivo do projeto — a fonte da verdade sobre o que foi feito.

---

## Estado Atual

| Campo | Valor |
|---|---|
| Projeto | vigiacontratos |
| Tipo | Sistema de gestão contratual municipal |
| Fase Atual | Fase 0 — Setup Inicial |
| Última Atualização | 2026-02-18 |
| Próximo Passo | Fase 1 — Configurar projeto Laravel + banco + template WowDash |

### Cadeia de Fases

```
[Fase 0: Setup] → [Fase 1: Infraestrutura] → [Fase 2: Cadastros Base] → [Fase 3: Contratos] → [Fase 4: Alertas] → [Fase 5: Relatórios] → [Fase 6: Refinamento]
     ▲ atual
```

**Detalhamento das Fases:**
- **Fase 0 — Setup Inicial:** Preencher bases de conhecimento, definir stack e convenções
- **Fase 1 — Infraestrutura:** Criar projeto Laravel, configurar Docker/Sail, MySQL, Redis, integrar template WowDash, autenticação, migrations base
- **Fase 2 — Cadastros Base:** CRUD de Secretarias, Fornecedores, Usuários (entidades de apoio)
- **Fase 3 — Contratos (Cadastro Inteligente):** Wizard multi-etapa, CRUD completo de Contratos + Aditivos + Fiscais + Documentos + Execução Financeira + Score de Risco + Audit Trail
- **Fase 4 — Alertas:** Sistema de alertas de vencimento com prazos configuráveis, jobs/filas
- **Fase 5 — Relatórios:** Dashboard com indicadores, relatórios gerenciais, exportação
- **Fase 6 — Refinamento:** Testes, ajustes de UX, performance, segurança final

---

## Registro de Implementações

| ID | Data | Descrição | Arquivos Afetados | Status |
|---|---|---|---|---|
| IMP-001 | 2026-02-18 | Preenchimento das bases de conhecimento do projeto | CLAUDE.md, memory/*.md | Concluído |
| IMP-002 | 2026-02-18 | Detalhamento do Módulo 1 — Cadastro Inteligente de Contratos | memory/banco-de-conhecimento.md, memory/banco-de-regras.md, memory/banco-de-memoria.md, memory/MEMORY.md, memory/banco-de-tema.md | Concluído |

### Como registrar:
1. Use ID sequencial (IMP-XXX)
2. Descreva o que foi feito (não como)
3. Liste os arquivos principais afetados
4. Status: `Concluído` | `Parcial` | `Revertido`

---

## Decisões Arquiteturais (ADRs)

| ID | Data | Decisão | Contexto | Alternativas Consideradas |
|---|---|---|---|---|
| ADR-001 | 2026-02-18 | Stack: Laravel 12 + PHP 8.2+ + MySQL 8 + Redis | Escolha da stack principal do projeto | Node.js/Express, Python/Django — Laravel escolhido pela produtividade e ecossistema maduro para admin panels |
| ADR-002 | 2026-02-18 | Template UI: WowDash (Bootstrap 5) | Escolha do template admin para o frontend | AdminLTE, Tabler, Tailwind puro — WowDash escolhido pois já disponível e com componentes ricos |
| ADR-003 | 2026-02-18 | Container: Docker / Laravel Sail | Ambiente de desenvolvimento padronizado | PHP local, Herd/Valet — Sail escolhido pela consistência entre ambientes |
| ADR-004 | 2026-02-18 | 3 perfis de usuário: Admin, Gestor, Consulta | Definição dos níveis de acesso | 2 perfis (admin/operador), RBAC granular — 3 perfis cobre as necessidades sem complexidade excessiva |
| ADR-005 | 2026-02-18 | Alertas com prazos configuráveis pelo admin | Como gerenciar antecedência dos alertas de vencimento | Prazos fixos (30/60/90 dias) — configurável dá flexibilidade sem complexidade |
| ADR-006 | 2026-02-18 | Tipos de contrato: Serviços, Obras, Compras, Locação | Tipos iniciais de contrato municipal | Incluir Concessão/Convênio — mantidos os 4 mais comuns, expansível via Enum |
| ADR-007 | 2026-02-18 | Cadastro de contrato via formulário multi-etapa (wizard 6 passos) | UX do cadastro de contratos — garantir qualidade dos dados e reduzir erros | Formulário único longo — wizard escolhido para melhor UX e validação por etapa |
| ADR-008 | 2026-02-18 | Fiscal como entidade separada (tabela `fiscais`) com histórico de trocas | Rastreabilidade de fiscais — auditorias exigem saber quem fiscalizou em cada período | Campo texto simples no contrato — entidade separada permite histórico e validação |
| ADR-009 | 2026-02-18 | Audit trail completo via tabela `historico_alteracoes` (polimórfica, imutável) | Requisito de auditoria e Tribunal de Contas — toda alteração deve ser rastreável | Logs de aplicação, pacote terceiro (Spatie Activity Log) — implementação própria para controle total |
| ADR-010 | 2026-02-18 | Execução financeira como entidade separada (tabela `execucoes_financeiras`) | Acompanhamento do percentual executado e alertas de estouro | Campo percentual manual no contrato — entidade separada permite cálculo automático e histórico |
| ADR-011 | 2026-02-18 | Score de risco automático calculado por critérios objetivos | Diferencial competitivo — classificação de risco encanta controladores internos | Classificação manual — automática é mais consistente e escalável |
| ADR-012 | 2026-02-18 | Modalidade de contratação como Enum (9 valores) | Validações legais dependem da modalidade (dispensa exige fundamento, etc.) | Campo texto livre — Enum garante consistência e permite regras condicionais |

### Como registrar:
1. Use ID sequencial (ADR-XXX)
2. Descreva a decisão de forma clara e objetiva
3. Contexto: por que essa decisão foi necessária?
4. Alternativas: o que mais foi considerado e por que não foi escolhido?

### Regras sobre ADRs:
- Uma vez registrada, uma ADR só pode ser alterada com justificativa explícita
- O Gestor de Memória (Agente 02) **bloqueia** ações que contradigam ADRs registradas
- Para reverter uma ADR, registre uma nova ADR que a substitua (nunca delete a original)

---

## Problemas Conhecidos

| ID | Descrição | Severidade | Módulo | Status |
|---|---|---|---|---|
| — | Nenhum problema registrado | — | — | — |

---

## Pendências

### Módulo: Infraestrutura
- [ ] Criar projeto Laravel 12 via Sail
- [ ] Configurar Docker (MySQL 8 + Redis)
- [ ] Integrar template WowDash (assets, layout, componentes)
- [ ] Configurar autenticação (login, logout, forgot password)
- [ ] Criar migrations base (users, secretarias, fornecedores)
- [ ] Criar seeders iniciais (admin user, secretarias)

### Módulo: Contratos (Cadastro Inteligente)
- [ ] Migration da tabela contratos (campos expandidos: modalidade, score_risco, percentual_executado, etc.)
- [ ] Migration da tabela fiscais (com histórico de trocas)
- [ ] Migration da tabela aditivos
- [ ] Migration da tabela documentos (com tipo_documento e versao)
- [ ] Migration da tabela execucoes_financeiras
- [ ] Migration da tabela historico_alteracoes (polimórfica, imutável)
- [ ] Enums: ModalidadeContratacao, TipoPagamento, CategoriaContrato, CategoriaServico, NivelRisco, TipoDocumentoContratual
- [ ] Models: Fiscal, ExecucaoFinanceira, HistoricoAlteracao
- [ ] Services: RiscoService (cálculo score), AuditoriaService (audit trail), FiscalService, ExecucaoFinanceiraService
- [ ] Observer: ContratoObserver (audit trail + recálculo de score)
- [ ] Formulário multi-etapa (wizard) para cadastro de contrato
- [ ] Tela de detalhes do contrato com abas (dados, fiscal, financeiro, documentos, auditoria)
- [ ] CRUD de aditivos (vinculado a contrato)
- [ ] CRUD de fiscais (com troca e histórico)
- [ ] Registro de execuções financeiras
- [ ] Upload múltiplo de documentos com classificação por tipo
- [ ] Versionamento de documentos
- [ ] Validação de CNPJ (dígito verificador)
- [ ] Validações condicionais por modalidade (dispensa → fundamento legal, obra → resp. técnico)
- [ ] Cálculo automático de score de risco
- [ ] Cálculo automático de percentual executado
- [ ] Filtros inteligentes na listagem (secretaria, vencimento, risco, fornecedor, número)

### Módulo: Alertas
- [ ] Migration da tabela alertas + configuracoes_alerta
- [ ] Job/Command para verificar vencimentos
- [ ] Tela de configuração de prazos de alerta
- [ ] Listagem de alertas com filtros

### Módulo: Dashboard/Relatórios
- [ ] Dashboard com indicadores (contratos vigentes, vencendo, vencidos)
- [ ] Gráficos com ApexCharts
- [ ] Relatórios gerenciais

### Geral
- [ ] Testes unitários (Services: ContratoService, AlertaService, RiscoService, AuditoriaService)
- [ ] Testes unitários (validação de CNPJ, cálculo de score de risco)
- [ ] Testes de integração (fluxos CRUD, cadastro multi-etapa)
- [ ] Testes de imutabilidade do audit trail

---

## Instruções de Manutenção

### Quando atualizar este arquivo?
- Após **cada implementação** aprovada pelo Auditor (Etapa 7)
- Quando uma **nova ADR** for tomada
- Quando um **problema** for descoberto ou resolvido
- Quando **pendências** forem concluídas ou criadas

### O que atualizar?
1. **Estado Atual**: atualizar fase e próximo passo
2. **Implementações**: adicionar novo registro IMP-XXX
3. **ADRs**: registrar se decisão arquitetural foi tomada
4. **Problemas**: adicionar/resolver bugs
5. **Pendências**: marcar concluídas ou adicionar novas
6. **MEMORY.md**: atualizar tabela de módulos se novo módulo criado
