# IMP-064 — Integração TCE: Exportação Estruturada

## Contexto

O sistema vigiacontratos já possui um relatório TCE em PDF (`PainelRiscoService::dadosRelatorioTCE()` + Blade template). Porém, o Tribunal de Contas do Estado (TCE) exige dados em formatos estruturados (XML e CSV) para alimentar seus sistemas de auditoria. IMP-064 adiciona:

1. **Exportação em formatos estruturados** (XML, CSV, Excel) para submissão ao TCE
2. **Modelo de histórico de exportações** (auditoria: quem exportou, quando, quantos contratos)
3. **Validação de completude** antes da exportação (campos obrigatórios para TCE)
4. **Service dedicado** (`TceExportService`) com lógica de mapeamento TCE
5. **Endpoints API** para exportação programática
6. **Endpoint web** (controller tenant) para download via interface
7. **Testes** Feature completos

---

## Arquivos a Criar

### 1. Migration — `database/migrations/tenant/2026_02_25_000050_create_exportacoes_tce_table.php`
```
exportacoes_tce:
  id, formato (enum: xml/csv/excel/pdf), filtros (json nullable),
  total_contratos (int), total_pendencias (int),
  arquivo_path (string nullable), arquivo_nome (string),
  gerado_por (FK users), observacoes (text nullable),
  timestamps
```

### 2. Model — `app/Models/ExportacaoTce.php`
- `$connection = 'tenant'`
- Casts: formato → FormatoExportacaoTce, filtros → array
- Relacionamento: `geradoPor()` → BelongsTo User

### 3. Enum — `app/Enums/FormatoExportacaoTce.php`
- Valores: `xml`, `csv`, `excel`, `pdf`
- Método `label()`: XML, CSV, Excel, PDF
- Método `extensao()`: .xml, .csv, .xlsx, .pdf
- Método `contentType()`: application/xml, text/csv, etc.

### 4. Service — `app/Services/TceExportService.php`
Métodos principais:
- `coletarDados(array $filtros): array` — Reutiliza `PainelRiscoService::dadosRelatorioTCE()` e enriquece com campos TCE (número processo, modalidade, fundamento legal, fiscal, aditivos, execução financeira)
- `validarCompletude(array $contratos): array` — Retorna lista de pendências por contrato (campos obrigatórios faltantes para TCE)
- `gerarXml(array $dados): string` — Gera XML no leiaute padrão TCE (SimpleXML)
- `gerarCsv(array $dados): StreamedResponse` — Gera CSV com separador `;` e BOM UTF-8
- `gerarExcel(array $dados): BinaryFileResponse` — Usa Maatwebsite/Excel
- `registrarExportacao(FormatoExportacaoTce $formato, array $filtros, int $total, int $pendencias): ExportacaoTce` — Salva no histórico

Campos obrigatórios para validação TCE:
- numero, objeto, fornecedor (cnpj), valor_global, data_inicio, data_fim
- modalidade_contratacao, numero_processo, data_publicacao
- fiscal designado

### 5. Export Excel — `app/Exports/RelatorioTceExport.php`
- `implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles`
- Recebe dados no constructor (mesmo padrão de `EfetividadeMensalExport`)
- 20 colunas: Numero, Objeto, CNPJ Fornecedor, Razão Social, Secretária, Modalidade, Nº Processo, Valor Global, Valor Empenhado, % Executado, Data Início, Data Fim, Data Publicação, Status, Fiscal Titular, Score Risco, Nível Risco, Categorias Risco, Pendências, Qtd Aditivos

### 6. API Controller — `app/Http/Controllers/Api/V1/TceController.php`
Endpoints:
- `GET /api/v1/tce/dados` — Retorna dados TCE em JSON (com filtros opcionais: status, secretaria_id, nivel_risco)
- `GET /api/v1/tce/validar` — Retorna pendências de completude
- `POST /api/v1/tce/exportar` — Gera arquivo (formato no body: xml/csv/excel), salva histórico, retorna download
- `GET /api/v1/tce/historico` — Lista exportações anteriores

### 7. API Resource — `app/Http/Resources/ExportacaoTceResource.php`
- Serializa ExportacaoTce para JSON (id, formato {value,label}, total_contratos, total_pendencias, gerado_por, created_at)

### 8. Testes — `tests/Feature/Api/ApiTceExportTest.php`
~20 testes:
- `test_dados_tce_retorna_contratos_com_risco` — GET /tce/dados
- `test_dados_tce_filtra_por_status` — filtro status
- `test_dados_tce_filtra_por_secretaria` — filtro secretaria_id
- `test_dados_tce_filtra_por_nivel_risco` — filtro nivel_risco
- `test_validar_retorna_pendencias_contratos_incompletos` — GET /tce/validar
- `test_validar_contrato_completo_sem_pendencias` — contrato ok
- `test_exportar_xml_gera_arquivo_valido` — POST format=xml
- `test_exportar_csv_gera_arquivo_valido` — POST format=csv
- `test_exportar_excel_gera_arquivo_valido` — POST format=excel
- `test_exportar_registra_historico` — verifica ExportacaoTce criada
- `test_historico_lista_exportacoes` — GET /tce/historico
- `test_historico_paginado` — paginação
- `test_xml_contem_estrutura_leiaute_tce` — valida tags XML
- `test_csv_contem_cabecalho_correto` — valida headers CSV
- `test_exportar_requer_autenticacao` — 401 sem token
- `test_exportar_requer_permissao` — 403 sem permission
- `test_dados_tce_bypassa_secretaria_scope` — todos os contratos
- `test_exportar_formato_invalido_retorna_422` — validação

### 9. Seeder — `database/seeders/ExportacaoTceSeeder.php`
- Cria 3 exportações de exemplo (xml, csv, excel) com dados fictícios

---

## Arquivos a Modificar

### 1. `routes/api.php`
Adicionar grupo TCE:
```php
// TCE — Exportação Estruturada
Route::get('/tce/dados', [TceController::class, 'dados'])->name('api.tce.dados');
Route::get('/tce/validar', [TceController::class, 'validar'])->name('api.tce.validar');
Route::post('/tce/exportar', [TceController::class, 'exportar'])->name('api.tce.exportar');
Route::get('/tce/historico', [TceController::class, 'historico'])->name('api.tce.historico');
```

### 2. `app/Services/PainelRiscoService.php`
Expandir `dadosRelatorioTCE()` para incluir campos adicionais que o TCE exige:
- modalidade_contratacao (label), numero_processo, fundamento_legal
- data_assinatura, data_publicacao, veiculo_publicacao
- fiscal_titular (nome), qtd_aditivos, valor_total_aditivos
- percentual_executado, valor_empenhado, saldo_contratual

---

## Estrutura XML TCE (Leiaute)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<RelatorioTCE versao="1.0">
  <Cabecalho>
    <Municipio>Nome do Município</Municipio>
    <DataGeracao>2026-02-25T10:00:00</DataGeracao>
    <TotalContratos>50</TotalContratos>
    <TotalPendencias>5</TotalPendencias>
  </Cabecalho>
  <Resumo>
    <TotalMonitorados>50</TotalMonitorados>
    <AltoRisco>5</AltoRisco>
    <MedioRisco>15</MedioRisco>
    <BaixoRisco>30</BaixoRisco>
  </Resumo>
  <Contratos>
    <Contrato>
      <Numero>001/2026</Numero>
      <Objeto>Prestação de serviços...</Objeto>
      <CNPJFornecedor>12.345.678/0001-90</CNPJFornecedor>
      <RazaoSocial>Empresa LTDA</RazaoSocial>
      <Secretaria>Saúde</Secretaria>
      <Modalidade>Pregão Eletrônico</Modalidade>
      <NumeroProcesso>2026/001</NumeroProcesso>
      <FundamentoLegal>Lei 14.133/2021</FundamentoLegal>
      <ValorGlobal>150000.00</ValorGlobal>
      <ValorEmpenhado>120000.00</ValorEmpenhado>
      <PercentualExecutado>80.00</PercentualExecutado>
      <DataInicio>2026-01-01</DataInicio>
      <DataFim>2026-12-31</DataFim>
      <DataAssinatura>2025-12-15</DataAssinatura>
      <DataPublicacao>2025-12-20</DataPublicacao>
      <Status>vigente</Status>
      <FiscalTitular>João Silva</FiscalTitular>
      <QtdAditivos>2</QtdAditivos>
      <ScoreRisco>45</ScoreRisco>
      <NivelRisco>medio</NivelRisco>
      <CategoriasRisco>Financeiro, Documental</CategoriasRisco>
      <Pendencias>Falta certidão atualizada</Pendencias>
    </Contrato>
  </Contratos>
</RelatorioTCE>
```

---

## Permissão

- Reutilizar permissão existente: `painel-risco.exportar` (já existe no sistema)
- No TceController: `$this->authorize('viewAny', Contrato::class)` + verificar `painel-risco.exportar`

---

## Verificação

1. Rodar testes: `./vendor/bin/sail test --filter=ApiTceExportTest`
2. Verificar: ~20 testes passando
3. Validar XML gerado com SimpleXML::loadString
4. Validar CSV com cabeçalho correto e separador `;`
5. Validar histórico de exportações persistido no banco

---

## Sequência de Implementação

1. Migration + Model + Enum + Seeder
2. TceExportService (core: coletarDados, validarCompletude, gerarXml, gerarCsv)
3. Expandir PainelRiscoService::dadosRelatorioTCE() com campos extras
4. RelatorioTceExport (Maatwebsite/Excel)
5. ExportacaoTceResource
6. TceController (API)
7. Rotas API
8. Testes
9. Atualização das bases de memória
