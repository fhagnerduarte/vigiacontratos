# IMP-065: Integracao PNP — Precos Referenciais

## Contexto

O PNP (Painel Nacional de Precos) e uma ferramenta do governo federal que fornece precos referenciais para compras publicas. Municipios usam essas referencias para verificar se estao pagando precos justos. Como a API real do PNP requer autenticacao governamental, esta integracao cria um **sistema local de precos referenciais** que permite:

- Registrar precos referenciais por categoria de servico (CategoriaServico enum existente)
- Comparar valores de contratos contra precos de referencia
- Alertar quando contratos excedem o teto referencial (sobrepreco)
- Alimentar o score de risco financeiro com criterio de sobrepreco
- Rastrear historico de precos ao longo do tempo

---

## Entregas

### 1. Migration 000051: tabela `precos_referenciais`

**Novo arquivo:** `database/migrations/tenant/2026_02_25_000051_create_precos_referenciais_table.php`

```sql
precos_referenciais
├── id (PK auto)
├── descricao (string 255) — "Servico de limpeza predial", "Locacao de veiculos"
├── categoria_servico (string 30) — CategoriaServico enum value
├── unidade_medida (string 50) — "mes", "m2", "hora", "unidade", "km"
├── preco_minimo (decimal 15,2) — piso referencial
├── preco_mediano (decimal 15,2) — mediana referencial
├── preco_maximo (decimal 15,2) — teto referencial
├── fonte (string 255) — "PNP 2026", "Pesquisa de mercado", "Ata RP 001/2026"
├── data_referencia (date) — data da coleta do preco
├── vigencia_ate (date, nullable) — validade do referencial
├── observacoes (text, nullable)
├── registrado_por (FK → users, cascadeOnDelete)
├── is_ativo (boolean, default true)
├── timestamps
```

**Indices:** `categoria_servico`, `is_ativo`, `data_referencia`

### 2. Migration 000052: tabela `comparativos_preco`

**Novo arquivo:** `database/migrations/tenant/2026_02_25_000052_create_comparativos_preco_table.php`

```sql
comparativos_preco
├── id (PK auto)
├── contrato_id (FK → contratos, cascadeOnDelete)
├── preco_referencial_id (FK → precos_referenciais, cascadeOnDelete)
├── valor_contrato (decimal 15,2) — valor_mensal ou valor_global do contrato
├── valor_referencia (decimal 15,2) — preco_maximo usado na comparacao
├── percentual_diferenca (decimal 8,2) — ((contrato - referencia) / referencia) * 100
├── status_comparativo (string 20) — enum: adequado, atencao, sobrepreco
├── observacoes (text, nullable)
├── gerado_por (FK → users, cascadeOnDelete)
├── timestamps
```

**Indices:** `contrato_id`, `preco_referencial_id`, `status_comparativo`

### 3. Enum StatusComparativoPreco

**Novo arquivo:** `app/Enums/StatusComparativoPreco.php`

```php
enum StatusComparativoPreco: string {
    case Adequado = 'adequado';     // ate 10% acima do maximo
    case Atencao = 'atencao';       // 10%-25% acima do maximo
    case Sobrepreco = 'sobrepreco'; // >25% acima do maximo

    public function label(): string
    public function cor(): string   // success, warning, danger
}
```

### 4. Model PrecoReferencial

**Novo arquivo:** `app/Models/PrecoReferencial.php`

- `$connection = 'tenant'`
- `$fillable`: todos os campos
- Casts: `categoria_servico` → CategoriaServico, `data_referencia` → date, `vigencia_ate` → date, `is_ativo` → boolean, decimais
- Relationships: `registrador()` → User, `comparativos()` → ComparativoPreco
- Scopes: `scopeAtivos()`, `scopePorCategoria(CategoriaServico)`, `scopeVigentes()`
- Accessor: `isVigente()` — vigencia_ate nula OU >= today

### 5. Model ComparativoPreco

**Novo arquivo:** `app/Models/ComparativoPreco.php`

- `$connection = 'tenant'`
- `$fillable`: todos os campos
- Casts: `status_comparativo` → StatusComparativoPreco, decimais
- Relationships: `contrato()` → Contrato, `precoReferencial()` → PrecoReferencial, `geradoPor()` → User

### 6. PnpService

**Novo arquivo:** `app/Services/PnpService.php`

Metodos estaticos (seguindo padrao TceExportService):

```php
// Listar precos com filtros
public static function listarPrecos(array $filtros = []): LengthAwarePaginator
// Filtros: categoria_servico, is_ativo, vigentes, search (descricao LIKE)

// Registrar preco referencial
public static function registrarPreco(array $dados, int $userId): PrecoReferencial

// Atualizar preco referencial
public static function atualizarPreco(int $id, array $dados): PrecoReferencial

// Gerar comparativo para um contrato
public static function compararContrato(Contrato $contrato): ?ComparativoPreco
// Busca preco referencial vigente pela categoria_servico do contrato
// Calcula percentual_diferenca usando valor_mensal do contrato vs preco_maximo
// Determina status: adequado (<10%), atencao (10-25%), sobrepreco (>25%)
// Salva em comparativos_preco e retorna

// Gerar comparativo em lote (todos contratos vigentes)
public static function gerarComparativoGeral(int $userId): array
// Retorna: ['total_contratos', 'comparados', 'sem_referencia', 'adequados', 'atencao', 'sobrepreco']

// Indicadores PNP
public static function indicadores(): array
// ['total_referencias', 'categorias_cobertas', 'contratos_sobrepreco', 'percentual_sobrepreco_medio', 'economia_potencial']

// Historico de precos por categoria
public static function historicoPorCategoria(CategoriaServico $categoria): Collection
```

### 7. PnpController (API)

**Novo arquivo:** `app/Http/Controllers/Api/V1/PnpController.php`

| Metodo | Rota | Acao |
|--------|------|------|
| `precos()` | GET `/pnp/precos` | Listar precos com filtros + paginacao |
| `showPreco()` | GET `/pnp/precos/{id}` | Detalhe de um preco referencial |
| `storePreco()` | POST `/pnp/precos` | Registrar preco (authorize contrato.criar) |
| `updatePreco()` | PUT `/pnp/precos/{id}` | Atualizar preco |
| `categorias()` | GET `/pnp/categorias` | Listar CategoriaServico enum com contagem de precos |
| `comparativo()` | GET `/pnp/comparativo` | Comparativo geral contratos vs referencias |
| `compararContrato()` | POST `/pnp/contratos/{id}/comparar` | Comparar contrato especifico |
| `indicadores()` | GET `/pnp/indicadores` | Indicadores agregados PNP |
| `historico()` | GET `/pnp/historico` | Historico precos por categoria |

Autorizacao: `$this->authorize('viewAny', Contrato::class)` para leitura, `$this->authorize('create', Contrato::class)` para escrita.
Pattern: `int $id` + `findOrFail($id)`.

### 8. StorePrecoReferencialRequest

**Novo arquivo:** `app/Http/Requests/Api/StorePrecoReferencialRequest.php`

Regras:
- `descricao`: required, string, max:255
- `categoria_servico`: required, Enum(CategoriaServico)
- `unidade_medida`: required, string, max:50
- `preco_minimo`: required, numeric, min:0.01
- `preco_mediano`: required, numeric, gte:preco_minimo
- `preco_maximo`: required, numeric, gte:preco_mediano
- `fonte`: required, string, max:255
- `data_referencia`: required, date, before_or_equal:today
- `vigencia_ate`: nullable, date, after:data_referencia
- `observacoes`: nullable, string, max:1000

### 9. API Resources

**Novo arquivo:** `app/Http/Resources/PrecoReferencialResource.php`
- Todos os campos + formatEnum(categoria_servico) + registrador (whenLoaded)
- is_vigente accessor

**Novo arquivo:** `app/Http/Resources/ComparativoPrecoResource.php`
- Todos os campos + formatEnum(status_comparativo) + contrato/precoReferencial (whenLoaded)

### 10. Rotas API

**Arquivo:** `routes/api.php` — adicionar apos bloco TCE (linha 98)

```php
// PNP — Precos Referenciais (IMP-065)
Route::get('/pnp/precos', [PnpController::class, 'precos']);
Route::get('/pnp/precos/{preco}', [PnpController::class, 'showPreco']);
Route::post('/pnp/precos', [PnpController::class, 'storePreco']);
Route::put('/pnp/precos/{preco}', [PnpController::class, 'updatePreco']);
Route::get('/pnp/categorias', [PnpController::class, 'categorias']);
Route::get('/pnp/comparativo', [PnpController::class, 'comparativo']);
Route::post('/pnp/contratos/{contrato}/comparar', [PnpController::class, 'compararContrato']);
Route::get('/pnp/indicadores', [PnpController::class, 'indicadores']);
Route::get('/pnp/historico', [PnpController::class, 'historico']);
```

### 11. Integracao RiscoService

**Arquivo:** `app/Services/RiscoService.php`

Em `calcularRiscoFinanceiro()` (apos linha 145, antes do return), adicionar:

```php
// Sobrepreco PNP: +10pts se contrato acima do teto referencial
$comparativo = ComparativoPreco::where('contrato_id', $contrato->id)
    ->latest()
    ->first();
if ($comparativo && $comparativo->status_comparativo === StatusComparativoPreco::Sobrepreco) {
    $score += 10;
    $criterios[] = "Sobrepreco detectado ({$comparativo->percentual_diferenca}% acima do referencial) (+10pts)";
}
```

### 12. Integracao WebhookService

**Arquivo:** `app/Services/WebhookService.php`

Adicionar 2 eventos ao `EVENTOS_DISPONIVEIS`:
- `'pnp.preco.registrado'`
- `'pnp.comparativo.gerado'`

### 13. Integracao Contrato Model

**Arquivo:** `app/Models/Contrato.php`

Adicionar relationship:
```php
public function comparativosPreco(): HasMany {
    return $this->hasMany(ComparativoPreco::class);
}
```

### 14. Factory PrecoReferencialFactory

**Novo arquivo:** `database/factories/PrecoReferencialFactory.php`

States: `vigente()`, `expirado()`, `servico()`, `obra()`, `porCategoria(CategoriaServico)`

### 15. PrecoReferencialSeeder

**Novo arquivo:** `database/seeders/PrecoReferencialSeeder.php`

10 precos referenciais cobrindo as 10 categorias de CategoriaServico com valores realistas. Integrar no TenantService.

### 16. Testes: ApiPnpTest (25 testes)

**Novo arquivo:** `tests/Feature/Api/ApiPnpTest.php`

setUp: RunsTenantMigrations, SeedsTenantData, seedBaseData, setUpTenant, Sanctum::actingAs

Helpers: `criarPrecoReferencial($overrides)`, `criarContratoComCategoria(CategoriaServico)`, `apiHeaders()`

| # | Grupo | Teste | Verifica |
|---|-------|-------|----------|
| 1 | Auth | `test_endpoints_requerem_autenticacao` | 401 sem token |
| 2 | Auth | `test_endpoints_requerem_tenant_header` | 422 sem X-Tenant-Slug |
| 3 | Precos | `test_listar_precos_retorna_paginado` | GET /pnp/precos → 200 + data/meta |
| 4 | Precos | `test_listar_precos_filtra_por_categoria` | ?categoria_servico=limpeza filtra |
| 5 | Precos | `test_listar_precos_filtra_vigentes` | ?vigentes=true exclui expirados |
| 6 | Precos | `test_show_preco_retorna_detalhes` | GET /pnp/precos/{id} → 200 |
| 7 | Precos | `test_show_preco_inexistente_retorna_404` | GET /pnp/precos/999 → 404 |
| 8 | Store | `test_registrar_preco_com_sucesso` | POST /pnp/precos → 201 + assertDatabaseHas |
| 9 | Store | `test_registrar_preco_valida_campos_obrigatorios` | POST sem dados → 422 |
| 10 | Store | `test_registrar_preco_valida_ordenacao_valores` | minimo > mediano → 422 |
| 11 | Store | `test_registrar_preco_dispara_webhook` | WebhookService::disparar chamado |
| 12 | Update | `test_atualizar_preco_com_sucesso` | PUT /pnp/precos/{id} → 200 |
| 13 | Update | `test_atualizar_preco_inexistente_retorna_404` | PUT /pnp/precos/999 → 404 |
| 14 | Categorias | `test_categorias_retorna_todas_com_contagem` | GET /pnp/categorias → 10 categorias |
| 15 | Comparativo | `test_comparativo_geral_retorna_resumo` | GET /pnp/comparativo → indicadores |
| 16 | Comparativo | `test_comparar_contrato_adequado` | contrato <= teto → adequado |
| 17 | Comparativo | `test_comparar_contrato_atencao` | contrato 10-25% acima → atencao |
| 18 | Comparativo | `test_comparar_contrato_sobrepreco` | contrato >25% acima → sobrepreco |
| 19 | Comparativo | `test_comparar_contrato_sem_referencia` | sem preco ref → mensagem |
| 20 | Comparativo | `test_comparar_contrato_dispara_webhook` | webhook pnp.comparativo.gerado |
| 21 | Indicadores | `test_indicadores_retorna_estrutura_correta` | GET /pnp/indicadores → 5 campos |
| 22 | Indicadores | `test_indicadores_calcula_economia_potencial` | contratos sobrepreco → economia |
| 23 | Historico | `test_historico_retorna_por_categoria` | GET /pnp/historico?categoria → lista |
| 24 | Risco | `test_sobrepreco_impacta_score_risco` | RiscoService +10pts para sobrepreco |
| 25 | Enum | `test_status_comparativo_enum_valores_labels` | 3 cases com label/cor |

Comando: `export PATH="..." && ./vendor/bin/sail test --filter=ApiPnpTest`

---

## Arquivos Novos (13)

| Arquivo | Tipo |
|---------|------|
| `database/migrations/tenant/2026_02_25_000051_create_precos_referenciais_table.php` | Migration |
| `database/migrations/tenant/2026_02_25_000052_create_comparativos_preco_table.php` | Migration |
| `app/Enums/StatusComparativoPreco.php` | Enum |
| `app/Models/PrecoReferencial.php` | Model |
| `app/Models/ComparativoPreco.php` | Model |
| `app/Services/PnpService.php` | Service |
| `app/Http/Controllers/Api/V1/PnpController.php` | Controller |
| `app/Http/Requests/Api/StorePrecoReferencialRequest.php` | FormRequest |
| `app/Http/Resources/PrecoReferencialResource.php` | Resource |
| `app/Http/Resources/ComparativoPrecoResource.php` | Resource |
| `database/factories/PrecoReferencialFactory.php` | Factory |
| `database/seeders/PrecoReferencialSeeder.php` | Seeder |
| `tests/Feature/Api/ApiPnpTest.php` | Test |

## Arquivos Modificados (4)

| Arquivo | Alteracao |
|---------|-----------|
| `routes/api.php` | +9 rotas PNP apos bloco TCE |
| `app/Services/RiscoService.php` | +criterio sobrepreco em calcularRiscoFinanceiro() |
| `app/Services/WebhookService.php` | +2 eventos EVENTOS_DISPONIVEIS |
| `app/Models/Contrato.php` | +comparativosPreco() relationship |

---

## Ordem de Implementacao

1. Migrations (000051 + 000052)
2. Enum StatusComparativoPreco
3. Models (PrecoReferencial + ComparativoPreco)
4. Factories (PrecoReferencialFactory)
5. PnpService
6. StorePrecoReferencialRequest
7. API Resources (PrecoReferencialResource + ComparativoPrecoResource)
8. PnpController
9. routes/api.php (+9 rotas)
10. Contrato model (+relationship)
11. WebhookService (+2 eventos)
12. RiscoService (+criterio sobrepreco)
13. PrecoReferencialSeeder + integrar TenantService
14. ApiPnpTest (25 testes)
15. Executar testes `--filter=ApiPnpTest`
16. Atualizar bases de memoria

---

## Verificacao

1. `./vendor/bin/sail test --filter=ApiPnpTest` — 25 testes passando
2. Verificar regressao: `--filter=RiscoServiceTest` (criterio sobrepreco nao deve quebrar testes existentes pois ComparativoPreco nao existira nos contratos de teste existentes)
3. Todos os 9 endpoints API respondem corretamente
4. Webhook events disponiveis incluem os 2 novos (GET /webhooks/eventos)
