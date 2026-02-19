# Regras — Auditoria, Conformidade e Performance

> Extraído de `banco-de-regras.md`. Carregar ao trabalhar com logs, auditoria, hash de integridade, cache ou performance.

---

## Auditoria e Conformidade

### Obrigatório
- **Relatório de logs por período**: exportável em PDF/CSV para auditoria externa
- **Histórico imutável**: tabelas de log são append-only (sem UPDATE/DELETE em historico_alteracoes, log_acesso_documentos, log_notificacoes, login_logs)
- **Hash de integridade de documento**: `hash('sha256', file_get_contents($arquivo))` armazenado no campo `hash_integridade` do Model Documento no momento do upload (ADR-047, RN-220)
- **Verificação de integridade**: hash verificável a qualquer momento — comparação do hash armazenado com hash recalculado do arquivo em storage (RN-221)
- **Relatório de conformidade**: lista de documentos com hash, data de upload, responsável, status de integridade
- **Proteção contra adulteração**: hash SHA-256 serve como prova de que o documento não foi alterado após upload

### Tabelas Imutáveis (append-only)
- `historico_alteracoes` — audit trail de entidades
- `log_acesso_documentos` — acesso a documentos
- `log_notificacoes` — envio de notificações
- `login_logs` — acessos ao sistema
- `workflow_aprovacoes` — etapas de aprovação (imutável após aprovação/reprovação)

---

## Performance e Escalabilidade

### Requisitos Mínimos por Prefeitura
- 5.000 a 20.000 contratos
- 50.000+ documentos
- 100 usuários ativos simultâneos

### Regras Obrigatórias
- **Tempo de resposta**: < 2 segundos para qualquer página
- **Paginação obrigatória** em todas as listagens (máximo 50 registros por página, configurável)
- **Indexação adequada**: índices em todas as colunas usadas em WHERE, JOIN e ORDER BY frequentes
- **Processamento assíncrono**: OCR (Fase 2), notificações, agregações de dashboard — sempre via Queue
- **Cache Redis**: dashboard (TTL 24h), painel de risco (TTL 24h), dados frequentes
- **Jobs noturnos**: agregação de dados fora do horário comercial (AgregarDashboardCommand)
- **Eager loading** obrigatório: usar `with()` para evitar N+1 queries
- **Disponibilidade**: 24/7 (infraestrutura de produção com monitoramento)
