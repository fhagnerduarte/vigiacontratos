# Conhecimento — Módulo: Fornecedores

> Extraído de `banco-de-conhecimento.md`. Carregar quando trabalhando no módulo de Fornecedores.
> Inclui: Regras (RN-018, RN-019, RN-038).

---

## Regras de Negócio

### Módulo: Fornecedores

| ID | Regra | Detalhamento |
|---|---|---|
| RN-018 | CNPJ do fornecedor deve ser único no sistema | Não permite cadastro duplicado |
| RN-019 | Fornecedor com contratos vigentes não pode ser excluído | Soft delete + validação antes de excluir |
| RN-038 | Validação automática de CNPJ com dígito verificador | Validar algoritmo do CNPJ no cadastro e edição |
