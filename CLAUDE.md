# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Projeto

**vigiacontratos** — Sistema de gestão contratual municipal que garante que nenhum contrato da prefeitura vença sem controle, evita riscos jurídicos e organiza toda a gestão contratual em um único painel.

- Stack: Laravel 12, PHP 8.2+, Docker / Laravel Sail
- Banco: MySQL 8
- Cache: Redis
- Idioma da UI e domínio: pt-BR

---

## Arquitetura Multi-Agente

Este projeto opera com um sistema de 6 agentes virtuais e 4 bases de conhecimento compartilhadas. **Toda implementação deve seguir o pipeline obrigatório.**

### Agentes

| # | Agente | Arquivo | Poder |
|---|--------|---------|-------|
| 1 | Guardião de Regras | `memory/agents/01-guardiao-de-regras.md` | Bloquear execução |
| 2 | Gestor de Memória | `memory/agents/02-gestor-de-memoria.md` | Alertar conflitos/retrabalho |
| 3 | Curador de Conhecimento | `memory/agents/03-curador-de-conhecimento.md` | Bloquear lógica errada |
| 4 | Arquiteto | `memory/agents/04-arquiteto.md` | Definir plano técnico |
| 5 | Engenheiro Executor | `memory/agents/05-engenheiro-executor.md` | Gerar código (só com plano) |
| 6 | Auditor Técnico | `memory/agents/06-auditor-tecnico.md` | Reprovar e devolver |

Cada agente contém: responsabilidade, checklist de validação, critérios de decisão e formato de saída. Consultar o arquivo do agente para detalhes.

### Bases Compartilhadas

| Base | Arquivo | Propósito |
|------|---------|-----------|
| Banco de Regras | `memory/banco-de-regras.md` | Governança técnica |
| Banco de Memória | `memory/banco-de-memoria.md` | Estado do projeto |
| Banco de Conhecimento | `memory/banco-de-conhecimento.md` | Domínio: Gestão Contratual Municipal |
| Banco de Tema | `memory/banco-de-tema.md` | Tema visual e componentes UI |

Índice rápido: `memory/MEMORY.md`

---

## Pipeline Obrigatório

### Modo Completo (features, refactoring, modelagem, mudanças estruturais)

Toda resposta deve seguir esta estrutura:

```
1. GUARDIÃO DE REGRAS: [verifica violações nas regras técnicas]
2. GESTOR DE MEMÓRIA: [consulta estado atual, identifica impactos e evita retrabalho]
3. CURADOR DE CONHECIMENTO: [valida lógica de negócio]
4. ARQUITETO: [define plano técnico antes de qualquer código]
5. ENGENHEIRO: [implementa seguindo o plano aprovado]
6. AUDITOR: [revisa segurança, padrões, consistência]
7. ATUALIZAÇÃO DAS BASES: [registra o que mudou nos bancos]
```

Se qualquer agente reprovar → volta à etapa anterior para correção.

### Modo Abreviado (typos, config, ajustes menores que não afetam arquitetura)

```
VERIFICAÇÃO: [consulta rápida a regras + memória]
EXECUÇÃO: [implementação]
REGISTRO: [atualiza banco de memória se necessário]
```

---

## Regras Invioláveis

- Nunca gerar código sem plano arquitetural
- Nunca alterar estrutura sem registrar no banco de memória
- Nunca ignorar decisão anterior — sempre consultar banco de memória
- Nunca inventar regra de negócio — sempre consultar banco de conhecimento
- Sempre atualizar as bases após execução significativa
- Sempre ler as 3 bases antes de qualquer implementação nova

---

## Stack e Convenções

### Código
- Padrão: **PSR-12** estrito
- PHP 8.2+ com typed properties e enums nativos
- Models/Entidades: Singular PascalCase (`Contrato`, `Fornecedor`, `Aditivo`)
- Tabelas: Plural snake_case (`contratos`, `fornecedores`, `aditivos`)
- Colunas/campos: snake_case (`data_inicio`, `valor_total`, `is_ativo`)
- Controllers/Handlers: Plural PascalCase + Controller, resource-based (`ContratosController`)
- Rotas API: kebab-case, prefixo `/api/v1/` (`/api/v1/contratos`)

### Padrões Obrigatórios
- Validação de input: **Form Requests** (`StoreContratoRequest`, `UpdateContratoRequest`)
- Output da API: **API Resources** (`ContratoResource`, `FornecedorResource`)
- Lógica de negócio: **Services** (`ContratoService`, `AlertaService`)
- Valores fixos: **Enums** nativos PHP 8.1+ (`StatusContrato`, `TipoContrato`)
- Proteção de dados: **$fillable** obrigatório em todos os Models

### Banco de Dados
- Migrations sempre com rollback funcional (`down()` implementado)
- Foreign keys com cascade rules explícitas (`cascadeOnDelete()`)
- Soft deletes em entidades financeiras/críticas (contratos, aditivos)
- Campos monetários: `decimal(15,2)` — nunca float
- Campos percentuais: `decimal(5,2)` — nunca float
- `timestamps()` sempre incluído

### Container/Ambiente
- Docker / Laravel Sail
- Serviços: PHP 8.2+, MySQL 8, Redis
- Comandos: `sail up -d`, `sail artisan migrate`, `sail test`
- Não commitar `.env`, manter `.env.example` atualizado

### Git
- Commits em Português: `tipo: descrição` (feat, fix, refactor, docs, test)
- Branches: `feature/`, `fix/`, `refactor/`

### Anti-patterns Proibidos
- Lógica de negócio em Controllers (usar Services)
- Queries raw sem necessidade (usar Eloquent)
- Migrations não-reversíveis
- Variáveis de ambiente hardcoded
- Overengineering: não criar abstrações sem necessidade concreta
- N+1 queries (usar eager loading)
- Retornar Model diretamente na API (usar Resources)
