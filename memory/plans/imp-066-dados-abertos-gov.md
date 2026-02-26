# IMP-066: Integracao Dados Abertos Gov

## Contexto

O sistema ja possui um `DadosAbertosService` (IMP-057) que exporta contratos publicos via portal web (`/{slug}/portal/dados-abertos?formato=json|csv`). Porem, esta funcionalidade e apenas web — nao existe na API REST. O IMP-066 expande isso para:

- Criar endpoints API REST autenticados para dados abertos com mais campos e filtros
- Adicionar formato XML para compliance com padrao dados.gov.br
- Criar modelo de catalogo de datasets (metadados sobre as exportacoes disponiveis)
- Registrar historico de exportacoes para auditoria
- Expandir o DadosAbertosService com mais campos (aditivos, execucao financeira)
- Expor fornecedores e licitacoes via dados abertos

---

## Entregas

### 1. Migration 000053: tabela `exportacoes_dados_abertos`

**Novo arquivo:** `database/migrations/tenant/2026_02_26_000053_create_exportacoes_dados_abertos_table.php`

```sql
exportacoes_dados_abertos
├── id (PK auto)
├── dataset (string 50) — 'contratos', 'fornecedores', 'licitacoes'
├── formato (string 10) — 'json', 'csv', 'xml'
├── filtros (json, nullable)
├── total_registros (unsignedInteger)
├── solicitado_por (FK → users, nullable, cascadeOnDelete) — null = acesso publico
├── ip_solicitante (string 45, nullable)
├── timestamps
```

**Indices:** `dataset`, `formato`, `created_at`

### 2. Enum DatasetDadosAbertos

**Novo arquivo:** `app/Enums/DatasetDadosAbertos.php`

```php
enum DatasetDadosAbertos: string {
    case Contratos = 'contratos';
    case Fornecedores = 'fornecedores';
    case Licitacoes = 'licitacoes';

    public function label(): string
    public function descricao(): string // descricao do dataset para catalogo
}
```

### 3. Enum FormatoDadosAbertos

**Novo arquivo:** `app/Enums/FormatoDadosAbertos.php`

```php
enum FormatoDadosAbertos: string {
    case Json = 'json';
    case Csv = 'csv';
    case Xml = 'xml';

    public function label(): string
    public function contentType(): string
    public function extensao(): string
}
```

### 4. Model ExportacaoDadosAbertos

**Novo arquivo:** `app/Models/ExportacaoDadosAbertos.php`

- `$connection = 'tenant'`
- Casts: dataset → DatasetDadosAbertos, formato → FormatoDadosAbertos, filtros → array
- Relationships: `solicitante()` → User (nullable)

### 5. Expandir DadosAbertosService

**Arquivo existente:** `app/Services/DadosAbertosService.php`

Novos metodos estaticos:

```php
// Exportar contratos com campos expandidos (25+ campos vs 17 atuais)
public static function exportarContratosExpandido(array $filtros = []): array
// Novos campos: fundamento_legal, regime_execucao, categoria_servico, dotacao_orcamentaria,
// nivel_risco, score_risco, percentual_executado, qtd_aditivos, valor_total_aditivos,
// fiscal_titular, data_publicacao, veiculo_publicacao

// Exportar fornecedores publicos
public static function exportarFornecedores(array $filtros = []): array
// Campos: cnpj, razao_social, total_contratos, valor_total_contratado, contratos_vigentes

// Exportar dados de licitacoes (processos)
public static function exportarLicitacoes(array $filtros = []): array
// Campos: numero_processo, modalidade, objeto, valor_estimado, fornecedor, status, data

// Gerar XML dados abertos (padrao dados.gov.br)
public static function gerarXml(string $dataset, array $dados): string

// Catalogo de datasets disponiveis
public static function catalogo(): array
// Retorna metadados: nome, descricao, campos, filtros_disponiveis, formatos, total_registros

// Registrar exportacao
public static function registrarExportacao(
    DatasetDadosAbertos $dataset,
    FormatoDadosAbertos $formato,
    ?array $filtros,
    int $totalRegistros,
    ?int $userId,
    ?string $ip
): ExportacaoDadosAbertos

// Estatisticas de uso
public static function estatisticas(): array
// total_exportacoes, por_dataset, por_formato, ultima_exportacao
```

### 6. DadosAbertosController (API)

**Novo arquivo:** `app/Http/Controllers/Api/V1/DadosAbertosController.php`

| Metodo | Rota | Acao |
|--------|------|------|
| `catalogo()` | GET `/dados-abertos/catalogo` | Listar datasets disponiveis com metadados |
| `contratos()` | GET `/dados-abertos/contratos` | Exportar contratos (JSON paginado) |
| `fornecedores()` | GET `/dados-abertos/fornecedores` | Exportar fornecedores (JSON paginado) |
| `licitacoes()` | GET `/dados-abertos/licitacoes` | Exportar licitacoes (JSON paginado) |
| `exportar()` | POST `/dados-abertos/exportar` | Exportar dataset em formato especifico (JSON/CSV/XML) |
| `estatisticas()` | GET `/dados-abertos/estatisticas` | Estatisticas de uso dos dados abertos |
| `historico()` | GET `/dados-abertos/historico` | Historico de exportacoes |

Autorizacao: `$this->authorize('viewAny', Contrato::class)` para todos.
Pattern: JSON responses com metadata envelope.

### 7. API Resource ExportacaoDadosAbertosResource

**Novo arquivo:** `app/Http/Resources/ExportacaoDadosAbertosResource.php`

### 8. Rotas API

**Arquivo:** `routes/api.php` — adicionar apos bloco PNP

```php
// Dados Abertos Gov (IMP-066)
Route::get('/dados-abertos/catalogo', [DadosAbertosController::class, 'catalogo']);
Route::get('/dados-abertos/contratos', [DadosAbertosController::class, 'contratos']);
Route::get('/dados-abertos/fornecedores', [DadosAbertosController::class, 'fornecedores']);
Route::get('/dados-abertos/licitacoes', [DadosAbertosController::class, 'licitacoes']);
Route::post('/dados-abertos/exportar', [DadosAbertosController::class, 'exportar']);
Route::get('/dados-abertos/estatisticas', [DadosAbertosController::class, 'estatisticas']);
Route::get('/dados-abertos/historico', [DadosAbertosController::class, 'historico']);
```

### 9. Integracao WebhookService

**Arquivo:** `app/Services/WebhookService.php`

Adicionar 1 evento ao `EVENTOS_DISPONIVEIS`:
- `'dados_abertos.exportacao'`

### 10. Testes: ApiDadosAbertosTest (25 testes)

**Novo arquivo:** `tests/Feature/Api/ApiDadosAbertosTest.php`

| # | Grupo | Teste | Verifica |
|---|-------|-------|----------|
| 1 | Auth | `test_endpoints_requerem_autenticacao` | 401 sem token |
| 2 | Auth | `test_endpoints_requerem_tenant_header` | 422 sem header |
| 3 | Catalogo | `test_catalogo_retorna_3_datasets` | GET /catalogo → 3 datasets com metadados |
| 4 | Catalogo | `test_catalogo_inclui_campos_e_filtros` | cada dataset tem campos/filtros/formatos |
| 5 | Contratos | `test_contratos_retorna_dados_paginados` | GET /contratos → data + meta |
| 6 | Contratos | `test_contratos_retorna_campos_expandidos` | 25+ campos por contrato |
| 7 | Contratos | `test_contratos_filtra_por_status` | ?status=vigente filtra |
| 8 | Contratos | `test_contratos_filtra_por_ano` | ?ano=2026 filtra |
| 9 | Contratos | `test_contratos_filtra_por_secretaria` | ?secretaria_id=N filtra |
| 10 | Contratos | `test_contratos_filtra_por_modalidade` | ?modalidade=pregao_eletronico |
| 11 | Contratos | `test_contratos_apenas_publicos_visiveis` | somente classificacao_sigilo=publico + publicado_portal=true |
| 12 | Fornecedores | `test_fornecedores_retorna_dados` | GET /fornecedores → data |
| 13 | Fornecedores | `test_fornecedores_inclui_totais` | total_contratos, valor_total |
| 14 | Fornecedores | `test_fornecedores_filtra_busca` | ?busca=razao_social |
| 15 | Licitacoes | `test_licitacoes_retorna_dados` | GET /licitacoes → data |
| 16 | Licitacoes | `test_licitacoes_filtra_por_modalidade` | ?modalidade filtra |
| 17 | Exportar | `test_exportar_json_contratos` | POST formato=json, dataset=contratos → 200 |
| 18 | Exportar | `test_exportar_csv_contratos` | POST formato=csv → StreamedResponse |
| 19 | Exportar | `test_exportar_xml_contratos` | POST formato=xml → XML valido |
| 20 | Exportar | `test_exportar_registra_historico` | ExportacaoDadosAbertos criada |
| 21 | Exportar | `test_exportar_formato_invalido` | formato=pdf → 422 |
| 22 | Exportar | `test_exportar_dataset_invalido` | dataset=invalido → 422 |
| 23 | Estatisticas | `test_estatisticas_retorna_contadores` | total, por_dataset, por_formato |
| 24 | Historico | `test_historico_retorna_paginado` | GET /historico → data + meta |
| 25 | Enum | `test_enums_dataset_formato_valores` | 3+3 cases com label |

---

## Arquivos Novos (8)

| Arquivo | Tipo |
|---------|------|
| `database/migrations/tenant/2026_02_26_000053_create_exportacoes_dados_abertos_table.php` | Migration |
| `app/Enums/DatasetDadosAbertos.php` | Enum |
| `app/Enums/FormatoDadosAbertos.php` | Enum |
| `app/Models/ExportacaoDadosAbertos.php` | Model |
| `app/Http/Controllers/Api/V1/DadosAbertosController.php` | Controller |
| `app/Http/Resources/ExportacaoDadosAbertosResource.php` | Resource |
| `tests/Feature/Api/ApiDadosAbertosTest.php` | Test |

## Arquivos Modificados (3)

| Arquivo | Alteracao |
|---------|-----------|
| `app/Services/DadosAbertosService.php` | +7 metodos expandidos (contratos expandido, fornecedores, licitacoes, xml, catalogo, registrar, estatisticas) |
| `routes/api.php` | +7 rotas dados-abertos |
| `app/Services/WebhookService.php` | +1 evento dados_abertos.exportacao |

---

## Ordem de Implementacao

1. Migration 000053
2. Enums DatasetDadosAbertos + FormatoDadosAbertos
3. Model ExportacaoDadosAbertos
4. DadosAbertosService (expandir com 7 novos metodos)
5. ExportacaoDadosAbertosResource
6. DadosAbertosController (7 endpoints)
7. routes/api.php (+7 rotas)
8. WebhookService (+1 evento)
9. ApiDadosAbertosTest (25 testes)
10. Executar testes `--filter=ApiDadosAbertosTest`
11. Atualizar bases de memoria

---

## Verificacao

1. `./vendor/bin/sail test --filter=ApiDadosAbertosTest` — 25 testes passando
2. Verificar regressao: testes portal existentes nao quebram (DadosAbertosService e aditivo, nao substitui)
3. Todos os 7 endpoints API respondem corretamente
4. Formatos JSON/CSV/XML geram output valido
5. Historico de exportacoes registrado no banco
