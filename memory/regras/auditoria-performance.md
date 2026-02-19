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

#### Proteção de Imutabilidade no Nível de Banco de Dados

A imutabilidade das tabelas append-only é enforçada em **duas camadas**:

**Camada 1 — Aplicação (já definida):**
- Models não expõem métodos `update()` nem `delete()`
- Controllers/Services não têm endpoints de modificação para estas tabelas
- Policies retornam `false` para ações `update` e `delete` (inclusive para `administrador_geral`)

**Camada 2 — Banco de Dados (triggers MySQL):**

```sql
-- Trigger para historico_alteracoes (aplicar em cada banco tenant)
DELIMITER $$
CREATE TRIGGER trg_historico_no_update
BEFORE UPDATE ON historico_alteracoes
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Tabela historico_alteracoes e imutavel. UPDATE nao permitido.';
END$$

CREATE TRIGGER trg_historico_no_delete
BEFORE DELETE ON historico_alteracoes
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Tabela historico_alteracoes e imutavel. DELETE nao permitido.';
END$$
DELIMITER ;
```

Aplicar triggers equivalentes para: `login_logs`, `log_acesso_documentos`, `log_notificacoes`.

**Trigger parcial para `workflow_aprovacoes`** (imutabilidade após decisão):
```sql
CREATE TRIGGER trg_workflow_no_update
BEFORE UPDATE ON workflow_aprovacoes
FOR EACH ROW
BEGIN
    IF OLD.status != 'pendente' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Etapa de workflow ja concluida nao pode ser alterada.';
    END IF;
END$$
```

**Implementação:** Triggers criados nas migrations das respectivas tabelas (não em arquivo separado) para garantir aplicação em todos os bancos tenant via `tenant:migrate`.

**Usuário MySQL com privileges restritos:** O usuário MySQL que a aplicação usa (`DB_USERNAME`) deve ter apenas `SELECT, INSERT` nas tabelas imutáveis — sem `UPDATE, DELETE`. Criar usuário dedicado com grants restritos por tabela.

#### Verificação Automática de Integridade de Hash (Agendada)

Além da verificação on-demand (RN-221), implementar verificação periódica automatizada:

- **Command:** `documentos:verificar-integridade` — verifica todos os documentos com `hash_integridade` preenchido
- **Schedule:** Execução semanal (domingos às 02h00) — fora do horário comercial
- **Lógica:** Para cada documento ativo, recalcular SHA-256 do arquivo em storage e comparar com `hash_integridade` armazenado
- **Resultado OK:** Nenhuma ação necessária — log interno de verificação
- **Resultado DIVERGENTE:**
  1. Registrar em tabela `log_integridade_documentos` (id, documento_id, hash_esperado, hash_calculado, detectado_em, status='divergente')
  2. Notificar `administrador_geral` do tenant via email e notificação interna
  3. Marcar documento com flag `integridade_comprometida = true` (novo campo boolean no Model Documento)
  4. Bloquear download do documento até revisão administrativa
- **Performance:** Processar em batches de 100 documentos por vez (Queue job `VerificarIntegridadeDocumentoBatch`) para não sobrecarregar storage I/O
- **Nova tabela:** `log_integridade_documentos` (append-only): id, documento_id, hash_esperado varchar(64), hash_calculado varchar(64), detectado_em datetime, status enum(ok, divergente), created_at

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
