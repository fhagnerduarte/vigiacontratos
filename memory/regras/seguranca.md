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

#### Validação de Tipo de Arquivo (Obrigatório)
- Validação DEVE usar `mimes:` (verificação de conteúdo MIME real via magic bytes), não apenas `extensions:`
- NUNCA usar somente `mimetypes:` sem `mimes:` — usuário pode renomear arquivo malicioso
- Form Request obrigatório: `'arquivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480']`
  - `mimes:pdf` verifica os bytes `%PDF` no início do arquivo (magic bytes), não apenas a extensão
  - `max:20480` = 20MB em KB (padrão Laravel)
- Para comprovantes/anexos gerais (`pdf,jpg,png`): `'mimes:pdf,jpg,jpeg,png', 'max:5120'`
- Validação adicional pós-upload: verificar magic bytes manualmente para PDF (`%PDF-`) e imagens (`\xff\xd8` JPEG, `\x89PNG` PNG) — implementar no `DocumentoService` como segunda camada de defesa
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

#### Política de Senhas
- Comprimento mínimo: **12 caracteres**
- Requisitos de composição: pelo menos 1 letra maiúscula, 1 letra minúscula, 1 número e 1 caractere especial (`!@#$%^&*()_+-=[]{}|;:,.<>?`)
- Implementação: `Password::min(12)->mixedCase()->numbers()->symbols()` do Laravel (classe `Illuminate\Validation\Rules\Password`)
- Senhas comuns bloqueadas: usar `Password::uncompromised()` para checar contra banco de senhas comprometidas (Have I Been Pwned API) — opcional para V1, obrigatório para V2
- Histórico de senhas: sem reuso das últimas 5 senhas (implementar na tabela `password_histories` — Fase 6)
- Referências: NIST SP 800-63B, PCI DSS v4.0 section 8

#### MFA (Autenticação Multi-Fator)
- **MFA obrigatório** para perfis `administrador_geral` e `controladoria` — ADR-055
- **MFA opcional (recomendado)** para perfis `secretario`, `gestor_contrato`, `procuradoria` e `financeiro`
- **MFA sem suporte** para perfis `fiscal_contrato` e `gabinete` (perfis de leitura de menor risco)
- Implementação: TOTP (Google Authenticator / Authy) — pacote `pragmarx/google2fa-laravel`
- Middleware `EnsureMfaVerified`: verifica se usuário de perfil obrigatório completou MFA na sessão atual
- **Bloqueio de login**: lockout após 5 tentativas, cooldown 15min (configurável) — ADR-046
- **Logs de login**: tabela `login_logs` (user_id, tenant_id, ip_address, user_agent, success, created_at) — ADR-048
- **Sessão**: expiração automática (`SESSION_LIFETIME` no `.env`, padrão 120min) — ADR-049
- Rate limiting em endpoints de login

#### Fluxo de Reset de Senha (Requisitos de Segurança)
- Token de reset: gerado via `Str::random(64)` + SHA-256 hash armazenado na tabela `password_reset_tokens` (padrão Laravel)
- Expiração do token: **60 minutos** (configurável via `auth.passwords.users.expire` — não mais do que 2 horas)
- Token de uso único: após uso, token é deletado imediatamente (`password_reset_tokens` limpo)
- Rate limiting: máximo **3 solicitações** de reset por email por hora via `RateLimiter::for('reset-password', ...)`
- Rate limiting: máximo **5 tentativas** de uso de token inválido por IP (mesma proteção do login)
- Email de reset: enviado apenas se o email existir no banco — sem confirmação de existência por resposta diferente (evitar user enumeration)
- Resposta padronizada: sempre retornar `"Se este e-mail existir, você receberá o link em breve"` — nunca confirmar ou negar existência
- Link de reset expira e só funciona uma vez — reuso deve gerar erro e solicitar novo reset

#### Sanctum Token Abilities (API `/api/v1/`)
- Tokens de API (Laravel Sanctum) devem ter abilities alinhadas às permissões RBAC do usuário
- Ao criar token via `$user->createToken('nome', $abilities)`, o array `$abilities` deve espelhar as permissões ativas do usuário no momento da criação
- Exemplo: `$user->createToken('api-token', $user->getAllPermissions()->pluck('nome')->toArray())`
- Validação no middleware: `$request->user()->tokenCan('contrato.editar')` antes de cada ação protegida na API
- Tokens com abilities mais amplas que as permissões atuais do role do usuário são inválidos — TokenService deve revogar tokens de usuários cujo role for alterado
- Tokens expiram em 24 horas por padrão (configurável via `sanctum.expiration`)
- Revogar todos os tokens ao desativar usuário ou ao trocar de tenant

#### Invalidação de Sessão por Desativação
- **Desativação de usuário** (`is_ativo = false`): todas as sessões ativas devem ser invalidadas imediatamente
  - Implementação: `DB::table('sessions')->where('user_id', $userId)->delete()` no `UserService::desativar()`
  - Para API: `$user->tokens()->delete()` (revogar todos os tokens Sanctum)
- **Desativação de tenant** (`is_ativo = false`): todos os usuários do tenant devem ter sessões invalidadas
  - Implementação: loop em todos os user_ids do tenant + limpeza de sessões + revogação de tokens
  - Registrar evento de invalidação em `login_logs` com success=false e campo adicional `motivo='tenant_desativado'`
- **Troca de role**: sessão não é invalidada, mas tokens Sanctum são revogados e reemitidos com novas abilities
- Middleware `EnsureUserIsActive`: verificar `$user->is_ativo` e `tenant->is_ativo` em toda request autenticada — retornar 401 e redirecionar para login se inativo

#### Rate Limiting de API (Endpoints `/api/v1/`)
- Login: já coberto pelo lockout de 5 tentativas (ADR-046)
- Endpoints gerais de API: **60 requests/minuto por usuário autenticado** via `RateLimiter::for('api', ...)`
- Endpoints de relatório/exportação (PDF/CSV): **10 requests/minuto** (operações pesadas)
- Endpoints de upload: **20 requests/minuto** por usuário
- Endpoints de verificação de integridade de hash: **5 requests/minuto**
- Implementação em `RouteServiceProvider::configureRateLimiting()` + middleware `throttle:api` nas rotas
- Resposta de rate limit: HTTP 429 com header `Retry-After` e mensagem em pt-BR
- Rate limit por IP para endpoints públicos (ex: forgot-password): **3 requests/hora por IP**

- Dados sensíveis nunca expostos (CPF/CNPJ parcialmente mascarados em listagens)
- Inputs financeiros sanitizados
- Documentos acessíveis apenas via controller autenticado (não via URL pública)
- **Audit trail** obrigatório em contratos (toda alteração logada com IP)

#### Configuração de CORS (API `/api/v1/`)
- Configuração em `config/cors.php` (nativo do Laravel)
- `allowed_origins`: lista explícita de domínios autorizados (ex: `['https://*.vigiacontratos.com.br']`) — **nunca `*`**
- `allowed_methods`: `['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']`
- `allowed_headers`: `['Content-Type', 'Authorization', 'X-Requested-With', 'Accept']`
- `exposed_headers`: `[]` (sem headers expostos adicionais)
- `max_age`: `3600` (1 hora de cache para preflight)
- `supports_credentials`: `true` (necessário para autenticação baseada em sessão/cookie)
- Em desenvolvimento: `allowed_origins` deve incluir apenas `['http://localhost:*', 'http://127.0.0.1:*']`
- Nunca commitar CORS com `allowed_origins: ['*']` — mesmo em desenvolvimento

### Segurança de Dados
- **HTTPS obrigatório** em produção (ForceHttps middleware)
- **TLS 1.2+** mínimo (configuração do servidor web / proxy reverso)
- Criptografia de campos sensíveis: `$casts = ['campo' => 'encrypted']` do Laravel

#### Matriz de Classificação e Criptografia de Dados

Campos que requerem criptografia em repouso (`$casts = ['campo' => 'encrypted']` no Model):

| Entidade | Campo | Justificativa LGPD |
|---|---|---|
| User | `email` | Dado pessoal identificador — base legal: execução contratual |
| Fornecedor | `cnpj` | Dado fiscal sensível — base legal: obrigação legal |
| Fornecedor | `email` | Dado pessoal de contato — base legal: execução contratual |
| Fornecedor | `telefone` | Dado pessoal de contato — base legal: execução contratual |
| Fornecedor | `representante_legal` | Nome de pessoa natural — base legal: execução contratual |
| Fiscal | `email` | Dado pessoal de servidor público — base legal: obrigação legal |
| Fiscal | `matricula` | Número funcional — base legal: obrigação legal |
| LoginLog | `ip_address` | Dado pessoal técnico — base legal: segurança e prevenção a fraude |
| LoginLog | `user_agent` | Dado técnico identificador — base legal: segurança |

Campos que NÃO devem ser criptografados (necessitam de indexação/busca eficiente):
- `contratos.numero`, `contratos.valor_global`, `contratos.data_fim` — campos de busca e ordenação
- `users.role_id`, `users.is_ativo` — campos de filtro RBAC

Observação: criptografia em nível de aplicação (`Illuminate\Contracts\Encryption\Encrypter`) impossibilita busca direta via SQL. Para campos que precisam de busca, usar mascaramento em display (CPF/CNPJ parcialmente mascarados em listagens — regra já existente) sem criptografar o valor em si. Reavaliar uso de MySQL Transparent Data Encryption como alternativa em V2.

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
#### Estratégia de Anonimização LGPD (RN-213) — Arquitetura de Dois Planos

O sistema resolve o conflito entre anonimização (RN-213) e imutabilidade de `historico_alteracoes` (RN-037, RN-342) por meio de **anonimização nos dados operacionais + preservação dos registros de auditoria conforme mandato legal**.

**Princípio fundamental:** `historico_alteracoes` documenta EVENTOS administrativos — é um instrumento de controle público obrigatório por lei (Lei 8.666/93, Lei 14.133/21, Lei de Transparência 12.527/2011). Esses registros NÃO são dados pessoais sujeitos a anonimização — são registros de atos administrativos públicos.

**O que PODE ser anonimizado (tabelas operacionais):**
- `fornecedores`: substituir `razao_social`, `representante_legal`, `email`, `telefone` por valores anonimizados (`ANONIMIZADO_[hash8]`) — mantendo `cnpj` intacto (obrigação legal de registro)
- `fiscais`: substituir `nome`, `email` por `ANONIMIZADO_[hash8]` — mantendo `matricula` e `contrato_id` (vínculo funcional público)
- `users` desativados: anonimizar `name`, `email` após prazo de retenção configurado (RN-212)

**O que NÃO pode ser anonimizado (registros de auditoria pública):**
- `historico_alteracoes`: registros imutáveis de atos administrativos. Se `valor_anterior` ou `valor_novo` contém nome de pessoa, esse nome é parte do ato administrativo, não dado pessoal de titular. Conformidade com art. 4o, III da LGPD (tratamento para fins de segurança pública, exercício de atividades de controle)
- `login_logs`: registros de segurança — retidos pelo prazo da política de retenção (RN-212), após o qual são deletados (não anonimizados — tabela imutável não suporta update)
- `log_acesso_documentos`: mesma lógica de `login_logs`

**Implementação:**
- `LGPDService::anonimizarFornecedor(Fornecedor $f)`: atualiza campos nas tabelas operacionais, registra solicitação em log próprio, mantém intacto historico_alteracoes
- Campos anonimizados mantêm formato: `ANONIMIZADO_` + primeiros 8 chars do SHA-256 do valor original (rastreável se necessário por ordem judicial, sem revelar o dado)
- Nova tabela `log_lgpd_solicitacoes`: id, tipo_solicitacao, entidade_tipo, entidade_id, solicitante, data_solicitacao, data_execucao, status — append-only
- Conflito documentado e resolvido: anonimização não viola imutabilidade de audit trail porque opera em tabelas diferentes (ADR-057)
