# Template Multi-Agente para Claude Code

Sistema de governança cognitiva com **6 agentes virtuais** e **4 bases de conhecimento** para projetos assistidos por IA.

Garante: qualidade de código, consistência de domínio, rastreabilidade de decisões e zero retrabalho.

---

## Por que usar?

| Problema | Solução Multi-Agente |
|---|---|
| IA gera código sem contexto | Gestor de Memória consulta histórico antes de agir |
| Regras de negócio inventadas | Curador de Conhecimento valida contra base documentada |
| Código fora do padrão | Guardião de Regras bloqueia violações técnicas |
| Implementação sem planejamento | Arquiteto planeja antes, Engenheiro só executa com plano |
| Bugs passam despercebidos | Auditor Técnico revisa tudo antes de consolidar |
| Perda de contexto entre sessões | Bases de conhecimento persistem entre conversas |

---

## Como Configurar para um Novo Projeto

### Passo 1 — Copiar o template

```bash
cp -r template-multi-agente/ /caminho/do/seu/projeto/
```

Copie os seguintes itens para a raiz do seu projeto:
- `CLAUDE.md` → raiz do projeto
- `memory/` → raiz do projeto (pasta inteira)

Os arquivos da pasta `exemplos/` são apenas referência — não precisam ir para o projeto.

### Passo 2 — Escolher a stack

Consulte o exemplo da sua stack em `exemplos/`:
- `stack-laravel.md` — PHP / Laravel
- `stack-nodejs.md` — TypeScript / Node.js (Express ou NestJS)
- `stack-python.md` — Python (Django ou FastAPI)

Use as convenções do exemplo para preencher as seções de stack no `CLAUDE.md` e no `banco-de-regras.md`.

### Passo 3 — Substituir os placeholders

Abra cada arquivo e substitua os placeholders `{{...}}` pelos valores do seu projeto.

### Passo 4 — Preencher as bases de conhecimento

1. **banco-de-conhecimento.md** — Documente o domínio do seu negócio (glossário, regras, fluxos, entidades)
2. **banco-de-regras.md** — Defina as convenções técnicas (já vem pré-preenchido com boas práticas)
3. **banco-de-tema.md** — Documente o template visual / design system que será usado
4. **banco-de-memoria.md** — Registre o estado inicial (fase 0, sem implementações)

### Passo 5 — Validar

Verifique que:
- [ ] Nenhum `{{PLACEHOLDER}}` restou sem substituição
- [ ] `CLAUDE.md` reflete a stack e domínio do projeto
- [ ] Banco de conhecimento tem pelo menos o glossário e regras principais
- [ ] Banco de regras tem as convenções de código definidas
- [ ] Banco de memória está no estado inicial

---

## Placeholders

| Placeholder | Descrição | Exemplo |
|---|---|---|
| `{{NOME_PROJETO}}` | Nome do sistema | Meu Sistema XYZ |
| `{{DESCRICAO_PROJETO}}` | Descrição curta do sistema | Plataforma de gestão financeira |
| `{{STACK_LINGUAGEM}}` | Linguagem principal | PHP 8.2+ / TypeScript 5 / Python 3.11+ |
| `{{STACK_FRAMEWORK}}` | Framework principal | Laravel 12 / NestJS 10 / Django 5 |
| `{{STACK_BANCO}}` | Banco de dados | MySQL 8 / PostgreSQL 16 / MongoDB 7 |
| `{{STACK_CACHE}}` | Sistema de cache | Redis / Memcached / Nenhum |
| `{{STACK_CONTAINER}}` | Containerização | Docker/Sail / Docker Compose / Nenhum |
| `{{IDIOMA_UI}}` | Idioma da interface | pt-BR / en-US / es |
| `{{IDIOMA_COMMITS}}` | Idioma dos commits | Português / English / Español |
| `{{CODING_STANDARD}}` | Padrão de código | PSR-12 / ESLint+Prettier / PEP 8 |
| `{{ORM}}` | ORM utilizado | Eloquent / Prisma / Django ORM |
| `{{TEMPLATE_UI}}` | Template ou design system | Duralux Admin / AdminLTE / Material UI |
| `{{DOMINIO}}` | Domínio de negócio | Financeiro / E-commerce / Saúde |

---

## Estrutura de Arquivos

```
seu-projeto/
├── CLAUDE.md                          # Instruções principais para o Claude Code
└── memory/
    ├── MEMORY.md                      # Índice rápido do projeto
    ├── banco-de-regras.md             # Governança técnica
    ├── banco-de-memoria.md            # Estado do projeto (atualizado constantemente)
    ├── banco-de-conhecimento.md       # Domínio de negócio
    ├── banco-de-tema.md               # Tema visual / UI
    └── agents/
        ├── 01-guardiao-de-regras.md   # Valida regras técnicas (pode bloquear)
        ├── 02-gestor-de-memoria.md    # Consulta estado, evita retrabalho
        ├── 03-curador-de-conhecimento.md # Valida lógica de negócio (pode bloquear)
        ├── 04-arquiteto.md            # Planeja antes de codar (nunca gera código)
        ├── 05-engenheiro-executor.md  # Implementa seguindo plano do arquiteto
        └── 06-auditor-tecnico.md      # Revisa tudo antes de consolidar
```

---

## Pipeline de Execução

```
Solicitação do Usuário
       │
       ▼
┌─────────────────────┐
│ 1. GUARDIÃO DE REGRAS │ ← banco-de-regras.md
│    Verifica violações │
│    técnicas           │
└──────────┬────────────┘
           │ APROVADO
           ▼
┌─────────────────────┐
│ 2. GESTOR DE MEMÓRIA │ ← banco-de-memoria.md
│    Consulta estado   │
│    atual do projeto  │
└──────────┬────────────┘
           │ PROSSEGUIR
           ▼
┌──────────────────────────┐
│ 3. CURADOR DE CONHECIMENTO│ ← banco-de-conhecimento.md
│    Valida lógica de       │
│    negócio                │
└──────────┬────────────────┘
           │ APROVADO
           ▼
┌─────────────────────┐
│ 4. ARQUITETO        │ ← todas as bases
│    Define plano     │
│    técnico          │
└──────────┬────────────┘
           │ PLANO DEFINIDO
           ▼
┌─────────────────────┐
│ 5. ENGENHEIRO       │ ← banco-de-regras.md + plano
│    Implementa código│
└──────────┬────────────┘
           │ IMPLEMENTADO
           ▼
┌─────────────────────┐
│ 6. AUDITOR TÉCNICO  │ ← todas as bases
│    Revisa e valida  │
└──────────┬────────────┘
           │ APROVADO
           ▼
┌─────────────────────┐
│ 7. ATUALIZAÇÃO      │
│    DAS BASES        │
└─────────────────────┘
```

Se qualquer agente **reprovar**: a execução volta à etapa anterior para correção.

---

## Manutenção das Bases

### Quando atualizar cada base?

| Base | Quando Atualizar |
|---|---|
| banco-de-memoria.md | Após cada implementação significativa (novas features, refactoring, ADRs) |
| banco-de-regras.md | Quando novas convenções ou padrões forem definidos |
| banco-de-conhecimento.md | Quando regras de negócio forem descobertas ou alteradas |
| banco-de-tema.md | Quando novos componentes UI forem adotados ou o design system mudar |
| MEMORY.md | Quando novos módulos forem criados ou o status geral mudar |

### Boas práticas

- Mantenha o `banco-de-memoria.md` sempre atualizado — é a "fonte da verdade" do estado do projeto
- Numere tudo sequencialmente (IMP-001, ADR-001, RN-001) para facilitar referências cruzadas
- Registre decisões arquiteturais (ADRs) com contexto e alternativas consideradas
- Documente regras de negócio assim que descobertas, mesmo que a implementação venha depois
- Revise as bases periodicamente para remover informações obsoletas
