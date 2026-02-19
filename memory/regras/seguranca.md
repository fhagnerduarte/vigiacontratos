# Regras — Segurança, Upload e LGPD

> Extraído de `banco-de-regras.md`. Carregar ao trabalhar com upload de documentos, autenticação, LGPD ou segurança de dados.

---

## Regras de Upload / Mídia

| Entidade | Tipos Permitidos | Tamanho Máximo |
|---|---|---|
| Documento de contrato | pdf | 20MB |
| Documento de aditivo | pdf | 20MB |
| Comprovante/Anexo geral | pdf, jpg, png | 5MB |

- Upload múltiplo permitido (vários arquivos por vez)
- Versionamento automático (mesmo tipo de documento → incrementa versão)
- Armazenamento local em desenvolvimento, S3 em produção
- Nomes de arquivo sanitizados (sem caracteres especiais)
- Organização por contrato e tipo: `documentos/contratos/{contrato_id}/{tipo_documento}/{arquivo}` (ADR-033)
- Nome de arquivo padronizado automaticamente: `contrato_{numero}_{tipo}_v{versao}.pdf` (RN-121)
- Nome original preservado no campo `nome_original` do Model
- Versionamento automático: campo `versao` (int) + `is_versao_atual` (boolean). Versões anteriores mantidas, nunca deletadas (ADR-034)
- Soft delete obrigatório em documentos — nunca deletar fisicamente do storage (RN-134)
- Todo acesso (upload, download, visualização, substituição, exclusão) registrado em `log_acesso_documentos` (RN-122, ADR-035)
- Documentos nunca expostos publicamente (acesso via controller autenticado + DocumentoPolicy)
- Registro automático de quem fez upload (uploaded_by) e data/hora
- Classificação obrigatória por tipo (TipoDocumentoContratual — 12 valores)

---

## Segurança

### Segurança de Acesso
- Autenticação via **Session-based** (Laravel padrão) para web
- CSRF em todas as rotas web
- Senhas: **Argon2id** (driver `argon2id` no `config/hashing.php`) — resistente a ataques de GPU e side-channel (ADR-044)
- **MFA opcional** para admin/gestor via TOTP (Google Authenticator / Authy) — ADR-045
- **Bloqueio de login**: lockout após 5 tentativas, cooldown 15min (configurável) — ADR-046
- **Logs de login**: tabela `login_logs` (user_id, tenant_id, ip_address, user_agent, success, created_at) — ADR-048
- **Sessão**: expiração automática (`SESSION_LIFETIME` no `.env`, padrão 120min) — ADR-049
- Rate limiting em endpoints de login
- Dados sensíveis nunca expostos (CPF/CNPJ parcialmente mascarados em listagens)
- Inputs financeiros sanitizados
- Documentos acessíveis apenas via controller autenticado (não via URL pública)
- **Audit trail** obrigatório em contratos (toda alteração logada com IP)

### Segurança de Dados
- **HTTPS obrigatório** em produção (ForceHttps middleware)
- **TLS 1.2+** mínimo (configuração do servidor web / proxy reverso)
- Criptografia de campos sensíveis: `$casts = ['campo' => 'encrypted']` do Laravel
- **Backup criptografado** (configuração do serviço de backup)
- **Logs imutáveis**: tabelas de log são append-only (sem UPDATE/DELETE)
- Arquivos sensíveis em storage criptografado (S3 server-side encryption)

### LGPD / Privacidade
- Dados de fornecedores (CNPJ, contatos) protegidos
- Documentos contratuais nunca expostos publicamente (acesso somente via DocumentosController autenticado)
- Todo acesso a documento registrado em `log_acesso_documentos` (RN-122, ADR-035)
- Documentos excluídos mantidos em storage (soft delete — RN-134)
- DocumentoPolicy aplicada em todos os endpoints de documentos (RN-130)
- Registros de auditoria imutáveis (nunca deletar)
- **Registro de base legal** por tratamento de dados pessoais (RN-210)
- **Controle de acesso por perfil**: Policy obrigatório em todos os endpoints (RN-211)
- **Log de acesso a dados sensíveis**: login_logs + log_acesso_documentos (RN-211)
- **Política de retenção** de dados configurável por tenant (RN-212)
- **Anonimização**: dados pessoais anonimizáveis sob solicitação (RN-213)
