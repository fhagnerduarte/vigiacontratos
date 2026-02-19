# Conhecimento — Regras Transversais

> Extraido de `banco-de-conhecimento.md`. Carregar quando trabalhando em Multi-Tenant, LGPD ou Auditoria.
> Inclui: Regras Multi-Tenant (RN-200 a RN-206), LGPD (RN-210 a RN-213), Auditoria Expandida (RN-220 a RN-225).

---

## Regras de Negócio — Multi-Tenant (Bloco RN-200)

> Regras que governam o isolamento de dados e operação multi-prefeitura do SaaS.

| ID | Regra | Detalhamento |
|---|---|---|
| RN-200 | Cada prefeitura opera em banco de dados isolado (database-per-tenant) | Um banco MySQL dedicado por prefeitura-cliente. Sem compartilhamento de tabelas de negócio entre tenants (ADR-042) |
| RN-201 | Dados de uma prefeitura nunca são acessíveis por outra | Isolamento total — nenhum mecanismo de query cross-tenant (sem UNION, sem JOIN entre connections). Middleware `SetTenantConnection` garante escopo |
| RN-202 | Migrations devem ser aplicadas em todos os bancos tenant simultaneamente | Comando artisan dedicado percorre todos os tenants ativos e aplica migrations pendentes em cada banco |
| RN-203 | Admin SaaS pode gerenciar tenants (criar, ativar, desativar) | Operações no banco master: provisionar novo banco, aplicar migrations, criar admin inicial, configurar storage. Desativação é soft (is_ativo = false) |
| RN-204 | Storage de arquivos isolado por tenant (bucket/pasta separada) | Estrutura S3: `{tenant_slug}/documentos/contratos/{contrato_id}/{tipo}/...`. Nunca misturar arquivos de tenants diferentes |
| RN-205 | Cache Redis isolado por tenant (prefixo de chave) | Chaves Redis: `tenant_{id}:dashboard`, `tenant_{id}:painel_risco`. Evita colisão entre tenants |
| RN-206 | Jobs/Queues devem carregar contexto do tenant | Todo job assíncrono recebe `tenant_id` no payload e configura connection antes de executar |

---

## Regras de Negócio — LGPD (Bloco RN-210)

> Regras de conformidade com a Lei Geral de Proteção de Dados (Lei 13.709/2018).

| ID | Regra | Detalhamento |
|---|---|---|
| RN-210 | Todo tratamento de dados pessoais deve ter base legal registrada | Cada tipo de dado pessoal (CNPJ fornecedor, dados de fiscal, contatos) deve ter base legal identificada (execução contratual, obrigação legal, etc.) |
| RN-211 | Acesso a dados sensíveis deve ser logado | Login: `login_logs`. Documentos: `log_acesso_documentos`. Dados pessoais: auditoria via `historico_alteracoes`. Logs são imutáveis (append-only) |
| RN-212 | Política de retenção de dados configurável por tenant | Cada prefeitura define por quanto tempo manter dados pessoais e logs. Padrão: 5 anos (compatível com prazos legais de guarda de documentos públicos) |
| RN-213 | Dados pessoais anonimizáveis por solicitação formal — com estratégia de dois planos para preservar imutabilidade do audit trail | Tabelas operacionais (fornecedores, fiscais, users inativos): anonimização via `LGPDService`. Tabelas de auditoria (historico_alteracoes, login_logs, log_acesso_documentos): preservadas conforme mandato legal de controle público (art. 4o, III da LGPD). Nova tabela `log_lgpd_solicitacoes` registra todas as solicitações. Ver especificação completa em `memory/regras/seguranca.md` seção LGPD (ADR-057) |

---

## Regras de Negócio — Auditoria Expandida (Bloco RN-220)

> Regras de auditoria e conformidade para segurança jurídica e proteção contra adulteração.

| ID | Regra | Detalhamento |
|---|---|---|
| RN-220 | Todo documento recebe hash SHA-256 no momento do upload | `$hash = hash('sha256', file_get_contents($arquivo))` — armazenado no campo `hash_integridade` do Model Documento |
| RN-221 | Hash de integridade verificável a qualquer momento | O sistema permite recalcular o hash do arquivo em storage e comparar com o hash armazenado. Divergência indica possível adulteração |
| RN-222 | Relatório de logs exportável por período | O sistema gera relatório de auditoria filtrável por período, tipo de ação, usuário e entidade. Exportável em PDF e CSV |
| RN-223 | Logs de login registram IP, user-agent, sucesso/falha | Tabela `login_logs` com campos: user_id, tenant_id, ip_address, user_agent, success (boolean), created_at. Append-only |
| RN-224 | Histórico de auditoria é imutável (append-only) | Tabelas `historico_alteracoes`, `log_acesso_documentos`, `log_notificacoes` e `login_logs` não permitem UPDATE nem DELETE |
| RN-225 | Relatório de conformidade documental | Lista documentos com hash de integridade, data de upload, responsável, verificação de integridade e status — instrumento de defesa para auditoria externa |

---

## Instruções de Manutenção

### Quando atualizar este arquivo?
- Quando uma **nova regra de negócio** for descoberta ou validada com o usuário
- Quando um **novo termo** do domínio for identificado
- Quando um **novo fluxo** for mapeado
- Quando uma **regra existente** precisar ser corrigida ou detalhada

### Regras sobre este banco:
- **Nunca inventar** regras — sempre validar com o usuário ou documentação oficial
- **Nunca deletar** regras — se uma regra for invalidada, marque como `[OBSOLETA]` com justificativa
- Manter numeração sequencial sem gaps dentro de cada bloco (não reutilizar IDs)
- Blocos de numeração: RN-001 a RN-155 (core), RN-200+ (multi-tenant), RN-210+ (LGPD), RN-220+ (auditoria)
- Referenciar este banco em toda implementação que envolva lógica de negócio
