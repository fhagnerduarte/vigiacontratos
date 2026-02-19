# Regras Core — Convenções de Código

> Extraído de `banco-de-regras.md`. Carregado em **TODAS** as tarefas de implementação.
> Consultado pelo **Guardião de Regras** (Agente 01) e pelo **Engenheiro Executor** (Agente 05).
> Define COMO o código deve ser escrito. Qualquer violação bloqueia a execução.

---

## Convenções de Código

### Padrão Geral
- Padrão de código: **PSR-12** estrito
- Linguagem principal: **PHP 8.2+** com typed properties e enums nativos
- Framework: **Laravel 12**

### Nomenclatura

| Elemento | Convenção | Exemplo |
|---|---|---|
| Models / Entidades | Singular PascalCase | `Contrato`, `Fornecedor`, `Fiscal` |
| Tabelas / Coleções | Plural snake_case | `contratos`, `fornecedores`, `fiscais` |
| Colunas / Campos | snake_case | `data_inicio`, `valor_total`, `is_ativo` |
| Controllers / Handlers | Plural PascalCase + Controller | `ContratosController`, `FiscaisController` |
| Services | Singular PascalCase + Service | `ContratoService`, `RiscoService` |
| Validadores | Store/Update + Model + Request | `StoreContratoRequest`, `StoreFiscalRequest` |
| Resources | Singular PascalCase + Resource | `ContratoResource`, `FiscalResource` |
| Enums | PascalCase (sem sufixo) | `StatusContrato`, `ModalidadeContratacao` |
| Policies | Singular PascalCase + Policy | `ContratoPolicy`, `FornecedorPolicy` |
| Rotas API | kebab-case, prefixo `/api/v1/` | `/api/v1/contratos`, `/api/v1/fornecedores` |
