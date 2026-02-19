# Regras — Arquitetura e Anti-patterns

> Extraído de `banco-de-regras.md`. Carregar ao tomar decisões arquiteturais ou revisar código.
> Define camadas obrigatórias e anti-patterns proibidos.

---

## Regras de Arquitetura

### Obrigatório

| Camada | Propósito | Regra |
|---|---|---|
| Form Requests | Validação de input | Toda validação de request **deve** estar no Form Request, nunca inline |
| API Resources | Output da API | Toda resposta **deve** usar Resource, nunca retornar Model diretamente |
| Services | Lógica de negócio | Lógica complexa ou com múltiplas operações **deve** estar em um Service |
| Enums | Valores fixos | Status, tipos, categorias **devem** usar Enum nativo PHP 8.1+, nunca strings hardcoded |
| $fillable | Mass assignment | **Sempre** usar $fillable explícito em todos os Models |
| Policies | Autorização | Verificações de autorização **devem** usar Policies |
| Observers | Eventos de Model | Audit trail e cálculos derivados via Eloquent Observers |
| Commands | Tarefas agendadas | Scheduled commands via `schedule()` no Kernel |
| Jobs | Tarefas assíncronas | Processamento em background via Laravel Queue (Redis) |
| Notifications | Notificações multi-canal | Laravel Notification para email + database |

### Anti-patterns Proibidos

| Anti-pattern | Motivo | Solução |
|---|---|---|
| Lógica no Controller | Viola separação de responsabilidades | Mover para Service |
| Queries raw sem necessidade | Risco de SQL injection, difícil manutenção | Usar Eloquent |
| Migration sem rollback | Impossibilita reverter mudanças | Sempre implementar `down()` |
| Variáveis de ambiente hardcoded | Quebra em diferentes ambientes | Usar `config()` / `env()` |
| Overengineering | Complexidade desnecessária | Só abstrair quando houver uso concreto |
| N+1 queries | Problema de performance grave | Usar eager loading (`with()`) |
| Retornar Model na API | Expõe estrutura interna do banco | Usar API Resource |
| Deletar registros de auditoria | Compromete integridade do log | Tabela imutável, nunca delete/update |
| Job sem retry/backoff | Notificações perdidas silenciosamente | Usar `$tries`, `$backoff` no Job |
| Notificação síncrona | Bloqueia request do usuário | Usar queue para envio assíncrono |
| Deletar log de notificação | Perde rastreabilidade de envios | Tabela imutável (append-only) |
