# Banco de Regras — Governança Técnica

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

### Classes Esperadas por Tipo

**Controllers:**
- Admin/DashboardController
- Admin/ContratosController
- Admin/FornecedoresController
- Admin/AditivosController
- Admin/FiscaisController
- Admin/AlertasController
- Admin/DocumentosController
- Admin/ExecucoesFinanceirasController
- Admin/RelatoriosController
- Admin/SecretariasController
- Admin/UsersController
- Admin/ConfiguracoesController

**Services:**
- ContratoService
- FornecedorService
- AditivoService
- FiscalService
- AlertaService
- DocumentoService
- ExecucaoFinanceiraService
- AuditoriaService
- RiscoService
- RelatorioService

**Validadores (Form Requests):**
- StoreContratoRequest / UpdateContratoRequest
- StoreFornecedorRequest / UpdateFornecedorRequest
- StoreAditivoRequest / UpdateAditivoRequest
- StoreFiscalRequest / UpdateFiscalRequest
- StoreExecucaoFinanceiraRequest
- StoreDocumentoRequest
- StoreSecretariaRequest / UpdateSecretariaRequest
- StoreUserRequest / UpdateUserRequest

**Transformadores (API Resources):**
- ContratoResource / ContratoCollection
- FornecedorResource / FornecedorCollection
- AditivoResource
- FiscalResource
- AlertaResource
- DocumentoResource
- ExecucaoFinanceiraResource
- HistoricoAlteracaoResource
- SecretariaResource
- UserResource

**Enums / Constantes:**
- StatusContrato (vigente, vencido, cancelado, suspenso, encerrado, rescindido)
- TipoContrato (servico, obra, compra, locacao)
- ModalidadeContratacao (pregao_eletronico, pregao_presencial, concorrencia, tomada_preco, convite, leilao, dispensa, inexigibilidade, adesao_ata)
- TipoPagamento (mensal, por_medicao, parcelado, unico)
- CategoriaContrato (essencial, nao_essencial)
- CategoriaServico (transporte, alimentacao, tecnologia, obras, limpeza, seguranca, manutencao, saude, educacao, outros)
- NivelRisco (baixo, medio, alto)
- TipoDocumentoContratual (contrato_original, termo_referencia, publicacao_oficial, parecer_juridico, aditivo_doc, ordem_servico, outros)
- TipoAditivo (prazo, valor, prazo_e_valor, supressao)
- StatusAditivo (vigente, vencido, cancelado)
- TipoUsuario (admin, gestor, consulta)
- StatusAlerta (pendente, visualizado, resolvido)
- PrioridadeAlerta (informativo, atencao, urgente)

---

## Estrutura de Diretórios

```
app/
├── Enums/
│   ├── StatusContrato.php
│   ├── TipoContrato.php
│   ├── ModalidadeContratacao.php
│   ├── TipoPagamento.php
│   ├── CategoriaContrato.php
│   ├── CategoriaServico.php
│   ├── NivelRisco.php
│   ├── TipoDocumentoContratual.php
│   ├── TipoAditivo.php
│   ├── StatusAditivo.php
│   ├── TipoUsuario.php
│   ├── StatusAlerta.php
│   └── PrioridadeAlerta.php
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── ContratosController.php
│   │       ├── FornecedoresController.php
│   │       ├── AditivosController.php
│   │       ├── FiscaisController.php
│   │       ├── AlertasController.php
│   │       ├── DocumentosController.php
│   │       ├── ExecucoesFinanceirasController.php
│   │       ├── RelatoriosController.php
│   │       ├── SecretariasController.php
│   │       ├── UsersController.php
│   │       └── ConfiguracoesController.php
│   ├── Middleware/
│   │   ├── EnsureUserIsAdmin.php
│   │   └── EnsureUserIsGestor.php
│   ├── Requests/
│   │   ├── StoreContratoRequest.php
│   │   ├── UpdateContratoRequest.php
│   │   ├── StoreFornecedorRequest.php
│   │   ├── UpdateFornecedorRequest.php
│   │   ├── StoreAditivoRequest.php
│   │   ├── UpdateAditivoRequest.php
│   │   ├── StoreFiscalRequest.php
│   │   ├── UpdateFiscalRequest.php
│   │   ├── StoreExecucaoFinanceiraRequest.php
│   │   ├── StoreDocumentoRequest.php
│   │   ├── StoreSecretariaRequest.php
│   │   ├── UpdateSecretariaRequest.php
│   │   ├── StoreUserRequest.php
│   │   └── UpdateUserRequest.php
│   └── Resources/
│       ├── ContratoResource.php
│       ├── ContratoCollection.php
│       ├── FornecedorResource.php
│       ├── FornecedorCollection.php
│       ├── AditivoResource.php
│       ├── FiscalResource.php
│       ├── AlertaResource.php
│       ├── DocumentoResource.php
│       ├── ExecucaoFinanceiraResource.php
│       ├── HistoricoAlteracaoResource.php
│       ├── SecretariaResource.php
│       └── UserResource.php
├── Models/
│   ├── User.php
│   ├── Contrato.php
│   ├── Fornecedor.php
│   ├── Secretaria.php
│   ├── Fiscal.php
│   ├── Aditivo.php
│   ├── Documento.php
│   ├── ExecucaoFinanceira.php
│   ├── HistoricoAlteracao.php
│   ├── Alerta.php
│   └── ConfiguracaoAlerta.php
├── Observers/
│   └── ContratoObserver.php          (audit trail + score de risco)
├── Policies/
│   ├── ContratoPolicy.php
│   ├── FornecedorPolicy.php
│   └── AditivoPolicy.php
└── Services/
    ├── ContratoService.php
    ├── FornecedorService.php
    ├── AditivoService.php
    ├── FiscalService.php
    ├── AlertaService.php
    ├── DocumentoService.php
    ├── ExecucaoFinanceiraService.php
    ├── AuditoriaService.php           (log de alterações)
    ├── RiscoService.php               (cálculo de score de risco)
    └── RelatorioService.php

database/
├── migrations/
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── SecretariaSeeder.php
│   ├── UserSeeder.php
│   └── ConfiguracaoAlertaSeeder.php
└── factories/
    ├── UserFactory.php
    ├── ContratoFactory.php
    └── FornecedorFactory.php

routes/
├── web.php
└── admin.php

resources/
├── views/
│   ├── layout/
│   │   └── layout.blade.php
│   ├── components/
│   │   ├── head.blade.php
│   │   ├── sidebar.blade.php
│   │   ├── navbar.blade.php
│   │   ├── breadcrumb.blade.php
│   │   ├── footer.blade.php
│   │   └── script.blade.php
│   ├── admin/
│   │   ├── dashboard/
│   │   │   └── index.blade.php
│   │   ├── contratos/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php         (wizard multi-etapa)
│   │   │   ├── edit.blade.php
│   │   │   └── show.blade.php           (abas: dados, fiscal, financeiro, documentos, auditoria)
│   │   ├── fornecedores/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── aditivos/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── alertas/
│   │   │   └── index.blade.php
│   │   ├── documentos/
│   │   │   └── index.blade.php
│   │   ├── relatorios/
│   │   │   └── index.blade.php
│   │   ├── secretarias/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── users/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   └── configuracoes/
│   │       └── index.blade.php
│   └── auth/
│       ├── login.blade.php
│       └── forgot-password.blade.php
└── css/
    └── app.css

tests/
├── Unit/
│   └── Services/
│       ├── ContratoServiceTest.php
│       ├── AlertaServiceTest.php
│       ├── RiscoServiceTest.php
│       └── AuditoriaServiceTest.php
└── Feature/
    ├── ContratoTest.php
    ├── FornecedorTest.php
    └── FiscalTest.php
```

---

## Regras de Banco de Dados

### Gerais
- Migrations sempre com **rollback funcional** (`down()` implementado)
- Tabelas nomeadas em português plural snake_case
- Foreign keys com **cascade rules explícitas**
- Soft deletes **obrigatório** em: contratos, aditivos, fornecedores
- `timestamps()` sempre incluído
- Tabelas de auditoria (`historico_alteracoes`) são **imutáveis** — sem update/delete

### Tipos de Dados

| Tipo de Dado | Tipo no Banco | Observação |
|---|---|---|
| Valores monetários | `decimal(15,2)` | Nunca usar float |
| Percentuais | `decimal(5,2)` | Nunca usar float |
| Score de risco | `integer` | Calculado (0-100+) |
| Textos curtos | `varchar(255)` | Padrão |
| Textos longos | `text` | Descrições, observações, objeto do contrato |
| Booleanos | `boolean` | Com default definido |
| Datas | `date` | Datas de vigência, vencimento |
| Data/hora | `datetime` | Timestamps, logs |
| Números de processo | `varchar(50)` | Números de licitação/processo/empenho |
| Dotação orçamentária | `varchar(255)` | Classificação orçamentária completa |
| IP address | `varchar(45)` | IPv4 e IPv6 |

### Tabelas do Sistema

**Módulo Contratos (Cadastro Inteligente):**
- `contratos` — Contratos municipais (campos expandidos: modalidade, score_risco, percentual_executado, etc.)
- `fiscais` — Fiscais de contrato (com histórico de troca)
- `aditivos` — Aditivos contratuais
- `documentos` — Documentos anexados (polimórfico, com tipo e versionamento)
- `execucoes_financeiras` — Registros de execução financeira/medições
- `historico_alteracoes` — Log de auditoria de todas as alterações (polimórfico, imutável)

**Módulo Cadastros:**
- `fornecedores` — Empresas fornecedoras (com validação de CNPJ)
- `secretarias` — Secretarias/órgãos da prefeitura

**Módulo Alertas:**
- `alertas` — Alertas de vencimento
- `configuracoes_alerta` — Prazos configuráveis de alerta

**Módulo Usuários:**
- `users` — Usuários do sistema

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

---

## Autorização e Tipos de Usuário

| Tipo | Acesso | Descrição |
|---|---|---|
| Administrador (`admin`) | Dashboard completo + configurações + gestão de usuários + auditoria | Gerencia todo o sistema, define prazos de alerta |
| Gestor de Contratos (`gestor`) | CRUD de contratos, fornecedores, aditivos, documentos, fiscais, execuções | Operação diária da gestão contratual |
| Consulta (`consulta`) | Somente visualização de contratos, alertas e relatórios | Acesso de leitura para consultas e auditorias |

### Implementação
- Coluna `tipo` na tabela `users` com Enum `TipoUsuario`
- Middleware `EnsureUserIsAdmin` para rotas administrativas
- Middleware `EnsureUserIsGestor` para rotas de gestão (admin também acessa)
- Policies para controle granular por entidade

---

## Regras de Upload / Mídia

| Entidade | Tipos Permitidos | Tamanho Máximo |
|---|---|---|
| Documento de contrato | pdf | 10MB |
| Documento de aditivo | pdf | 10MB |
| Comprovante/Anexo geral | pdf, jpg, png | 5MB |

- Upload múltiplo permitido (vários arquivos por vez)
- Versionamento automático (mesmo tipo de documento → incrementa versão)
- Armazenamento local em desenvolvimento, S3 em produção
- Nomes de arquivo sanitizados (sem caracteres especiais)
- Organização por entidade e ID: `documentos/{entidade}/{id}/{arquivo}`
- Documentos nunca expostos publicamente (acesso via controller autenticado)
- Registro automático de quem fez upload (uploaded_by) e data/hora
- Classificação obrigatória por tipo (TipoDocumentoContratual)

---

## Segurança

### Obrigatório
- Autenticação via **Session-based** (Laravel padrão) para web
- CSRF em todas as rotas web
- Rate limiting em endpoints de login
- Dados sensíveis nunca expostos (CPF/CNPJ parcialmente mascarados em listagens)
- Inputs financeiros sanitizados
- Documentos acessíveis apenas via controller autenticado (não via URL pública)
- **Audit trail** obrigatório em contratos (toda alteração logada com IP)

### LGPD / Privacidade
- Dados de fornecedores (CNPJ, contatos) protegidos
- Documentos contratuais nunca expostos publicamente
- Logs de acesso a documentos sensíveis
- Registros de auditoria imutáveis (nunca deletar)

---

## Container / Ambiente

- Container: **Docker / Laravel Sail**
- Linguagem: **PHP 8.2+**
- Banco: **MySQL 8**
- Cache: **Redis**
- Nunca commitar `.env`
- Manter `.env.example` atualizado com novas variáveis

---

## Git

### Commits
- Idioma: **Português**
- Formato: `tipo: descrição`
- Tipos: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `style`

### Branches
- Features: `feature/nome-da-feature`
- Correções: `fix/descricao-do-bug`
- Refactoring: `refactor/descricao`

---

## Testes

- Framework de testes: **PHPUnit** (via `sail test`)
- Cobertura mínima: Services e rotas críticas
- Testes unitários para: Services (ContratoService, AlertaService, RiscoService, AuditoriaService), cálculos, validações
- Testes de integração para: endpoints da API, fluxos CRUD completos, fluxo de cadastro multi-etapa
- Factories para dados de teste (ContratoFactory, FornecedorFactory, UserFactory)
- `RefreshDatabase` trait em testes de integração
- Testar cálculo de score de risco com diferentes cenários
- Testar validação de CNPJ (dígito verificador)
- Testar imutabilidade do audit trail
