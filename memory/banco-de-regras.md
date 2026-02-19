# Banco de Regras — Governança Técnica (Índice)

> Consultado pelo **Guardião de Regras** (Agente 01) e pelo **Engenheiro Executor** (Agente 05).
> Define COMO o código deve ser escrito. Qualquer violação bloqueia a execução.
>
> **Este arquivo é um índice.** O conteúdo foi separado em arquivos temáticos na pasta `memory/regras/`.

---

## Arquivos

| Arquivo | Conteúdo | Quando carregar |
|---------|----------|-----------------|
| [_core.md](regras/_core.md) | Convenções de código: PSR-12, nomenclatura, padrões | **Sempre** — base obrigatória |
| [classes-esperadas.md](regras/classes-esperadas.md) | Lista de Controllers, Services, Commands, Jobs, Requests, Resources, Enums | Criar novas classes |
| [estrutura-diretorios.md](regras/estrutura-diretorios.md) | Mapa completo da estrutura de diretórios do projeto | Criar novos arquivos |
| [banco-de-dados.md](regras/banco-de-dados.md) | Regras de BD, tipos de dados, índices, tabelas do sistema | Migrations, schema |
| [arquitetura.md](regras/arquitetura.md) | Camadas obrigatórias e anti-patterns proibidos | Decisões arquiteturais, revisão de código |
| [rbac.md](regras/rbac.md) | 8 perfis RBAC, implementação, autenticação/sessão | Módulo RBAC, permissões, middleware |
| [seguranca.md](regras/seguranca.md) | Upload/mídia, segurança de acesso/dados, LGPD | Upload, autenticação, LGPD |
| [multi-tenant.md](regras/multi-tenant.md) | Database-per-tenant, middleware, isolamento | Multi-tenant, provisioning |
| [auditoria-performance.md](regras/auditoria-performance.md) | Auditoria, hash SHA-256, performance, escalabilidade | Logs, auditoria, cache, performance |
| [ambiente-git-testes.md](regras/ambiente-git-testes.md) | Docker/Sail, Git, testes (PHPUnit + casos planejados) | Setup, commits, testes |
