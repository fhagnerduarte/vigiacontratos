# IMP-060: Integracao Final LAI + Testes E2E

## Contexto

Este e o ultimo IMP da Fase 8 (Conformidade LAI 12.527/2011). Os IMPs 056-059 construiram ClassificacaoSigilo, Portal Publico, SIC/e-SIC e Alertas LAI. O IMP-060 integra tudo: adiciona categoria de risco "transparencia" ao score, cria relatorio LAI em PDF, expande a sidebar e valida o ciclo completo com testes E2E.

---

## Entregas

### 1. RiscoService: +1 categoria "transparencia"

**Arquivo:** `app/Services/RiscoService.php`

- Atualizar docblock de `calcularExpandido()` (linha 31): "5 categorias" → "6 categorias"
- Adicionar `'transparencia' => self::calcularRiscoTransparencia($contrato)` no array `$categorias` (linha 43)
- Novo metodo privado `calcularRiscoTransparencia(Contrato $contrato): array` apos `calcularRiscoOperacional()` (apos linha 345)

**Criterios de pontuacao:**
| Criterio | Pontos | Condicao |
|----------|--------|----------|
| Nao publicado no portal | +10pts | `classificacao_sigilo = publico AND publicado_portal = false` |
| Sigilo sem justificativa | +10pts | `classificacao_sigilo != publico AND justificativa_sigilo vazia` |
| Dados publicacao incompletos | +5pts | `data_publicacao IS NULL OR veiculo_publicacao IS NULL` |

**Nota:** Solicitacoes LAI nao tem `contrato_id` (sao tenant-level), portanto nao entram no score por contrato. Max transparencia = 25pts.

### 2. PainelRiscoService: label/cor da nova categoria

**Arquivo:** `app/Services/PainelRiscoService.php`

- `labelCategoria()` (linha 200): adicionar `'transparencia' => 'Transparencia'` antes de `default`
- `corCategoria()` (linha 216): adicionar `'transparencia' => 'success'` antes de `default`

### 3. RelatorioService: metodo `dadosRelatorioLai()`

**Arquivo:** `app/Services/RelatorioService.php`

Novo metodo estatico `dadosRelatorioLai(): array` seguindo padrao existente. Retorna:

```php
[
    'municipio' => string,
    'data_geracao' => string,
    'resumo' => [
        'total_contratos', 'contratos_publicos', 'publicados_portal',
        'nao_publicados', 'sigilo_sem_justificativa', 'sem_dados_publicacao',
    ],
    'classificacao' => [ // por ClassificacaoSigilo case
        ['classificacao' => label, 'icone' => icon, 'cor' => cor, 'total' => count],
    ],
    'sic' => [ // via SolicitacaoLaiService::resumo() com try-catch
        'total_solicitacoes', 'pendentes', 'respondidas', 'vencidas', 'tempo_medio_resposta',
    ],
]
```

Usa `withoutGlobalScope(SecretariaScope::class)` para visao global. Try-catch em `SolicitacaoLaiService::resumo()` para tenants legados.

### 4. View PDF do relatorio LAI

**Novo arquivo:** `resources/views/tenant/relatorios/pdf/transparencia-lai.blade.php`

Seguir CSS/HTML do `efetividade-mensal.blade.php`. 3 secoes:
1. **Transparencia Ativa** — 5 cards indicadores (total, publicos, publicados, nao publicados, sem dados)
2. **Classificacao de Sigilo** — tabela 4 linhas (publico/reservado/secreto/ultrassecreto) + badge warning sigilo sem justificativa
3. **Transparencia Passiva (SIC/e-SIC)** — 5 cards (total, pendentes, respondidas, vencidas, tempo medio)

### 5. Controller + Rota do relatorio

**Arquivo:** `app/Http/Controllers/Tenant/RelatoriosController.php`

Novo metodo `transparenciaLaiPdf()` que chama `RelatorioService::dadosRelatorioLai()`, gera PDF via `Pdf::loadView()` e retorna download.

**Arquivo:** `routes/web.php` (apos linha 270)

```php
Route::get('relatorios/transparencia-lai', [RelatoriosController::class, 'transparenciaLaiPdf'])
    ->name('tenant.relatorios.lai')
    ->middleware(['permission:lai.relatorio', 'throttle:exportacoes']);
```

Permissao `lai.relatorio` ja existe no PermissionSeeder (criada IMP-058).

### 6. Sidebar: expandir secao Transparencia

**Arquivo:** `resources/views/components/sidebar.blade.php` (linhas 145-158)

Substituir link unico por dropdown com 3 itens:
1. **e-SIC / Solicitacoes LAI** → `tenant.solicitacoes-lai.index` (permissao `lai.visualizar`)
2. **Portal Publico** → link externo para `/{slug}/portal` em nova aba (permissao `lai.visualizar`)
3. **Relatorio LAI** → `tenant.relatorios.lai` (permissao `lai.relatorio`)

Usar pattern dropdown do sidebar (mesmo de "Contratos" linhas 29-51). Active-page: `request()->routeIs('tenant.solicitacoes-lai.*')` ou `request()->routeIs('tenant.relatorios.lai')`.

### 7. Relatorios index: card LAI

**Arquivo:** `resources/views/tenant/relatorios/index.blade.php`

Nova secao "Transparencia LAI" (entre Auditoria e Exportar, apos linha 158). Guardada por `lai.relatorio`. Card com icone `solar:eye-bold`, cor `lilac/success`, descricao sobre LAI 12.527/2011, botao "Gerar PDF" linkando para `tenant.relatorios.lai`.

### 8. Testes E2E: LaiIntegracaoTest (15 testes)

**Novo arquivo:** `tests/Feature/LaiIntegracao/LaiIntegracaoTest.php`

**setUp:** `RunsTenantMigrations`, `SeedsTenantData`, `Queue::fake()`, `seedBaseData()`, `setUpTenant()`, user admin com permissoes LAI.

**Helpers:** `criarContratoPublico($overrides)`, `criarSolicitacaoLai($overrides)`

| # | Grupo | Teste | Verifica |
|---|-------|-------|----------|
| 1 | Risco | `test_risco_transparencia_zerado_contrato_completo` | Publico+publicado+dados completos = score 0 |
| 2 | Risco | `test_risco_transparencia_nao_publicado_portal` | Publico+nao publicado = +10pts |
| 3 | Risco | `test_risco_transparencia_sigilo_sem_justificativa` | Reservado sem justificativa = +10pts |
| 4 | Risco | `test_risco_transparencia_dados_publicacao_incompletos` | Sem data/veiculo publicacao = +5pts |
| 5 | Ciclo | `test_ciclo_classificar_publicar_verificar_risco` | Classificar→desclassificar→publicar→risco=0 |
| 6 | Ciclo | `test_ciclo_solicitacao_criar_analisar_responder` | Criar→analisar→responder deferida, historico=3 |
| 7 | Ciclo | `test_ciclo_solicitacao_prorrogar_e_responder` | Prorrogar +10d→responder, prazo_estendido correto |
| 8 | Ciclo | `test_ciclo_publicacao_automatica` | Contrato publico nao publicado→publicar auto→verificar flag |
| 9 | Relatorio | `test_relatorio_lai_dados_corretos` | Contratos mistos→dadosRelatorioLai()→counts corretos |
| 10 | Relatorio | `test_relatorio_lai_inclui_sic` | 2 solicitacoes→sic.total=2 |
| 11 | Relatorio | `test_relatorio_lai_pdf_download` | GET rota→200→content-type pdf |
| 12 | Views | `test_sidebar_links_transparencia` | GET dashboard→assertSee 'Portal Publico', 'e-SIC', 'Relatorio LAI' |
| 13 | Views | `test_relatorios_index_card_lai` | GET relatorios.index→assertSee 'Transparencia LAI' |
| 14 | Score | `test_expandido_retorna_6_categorias` | calcularExpandido() tem key 'transparencia' |
| 15 | Score | `test_score_transparencia_impacta_total` | Contrato com problemas→transparencia>=15→contribui ao total |

**Datas:** usar `now()->subMonths(N)` distintos por teste para evitar overlap.

**Comando:** `export PATH="..." && ./vendor/bin/sail test --filter=LaiIntegracaoTest`

### 9. Atualizacao bases de conhecimento

**Arquivo:** `memory/conhecimento/_core.md`
- Tabela CategoriaRisco: adicionar 6a linha "transparencia"
- Glossario Score: "5 categorias" → "6 categorias (..., transparencia)"

**Arquivo:** `memory/banco-de-memoria.md` / `MEMORY.md`
- IMP-060 status → Completo com contagem de testes

---

## Ordem de Implementacao

1. `app/Services/RiscoService.php` — `calcularRiscoTransparencia()` + integrar
2. `app/Services/PainelRiscoService.php` — label/cor 'transparencia'
3. `app/Services/RelatorioService.php` — `dadosRelatorioLai()`
4. `resources/views/tenant/relatorios/pdf/transparencia-lai.blade.php` — criar view PDF
5. `app/Http/Controllers/Tenant/RelatoriosController.php` — `transparenciaLaiPdf()`
6. `routes/web.php` — rota `tenant.relatorios.lai`
7. `resources/views/components/sidebar.blade.php` — expandir Transparencia (dropdown 3 itens)
8. `resources/views/tenant/relatorios/index.blade.php` — card LAI
9. `tests/Feature/LaiIntegracao/LaiIntegracaoTest.php` — 15 testes E2E
10. Executar testes `--filter=LaiIntegracaoTest`
11. Atualizar bases de conhecimento/memoria

---

## Verificacao

1. `./vendor/bin/sail test --filter=LaiIntegracaoTest` — 15 testes passando
2. Verificar regressao: `--filter=RiscoServiceTest` (possivel +5pts de "dados incompletos" em contratos factory sem data_publicacao)
3. Visual: sidebar com dropdown Transparencia (3 itens)
4. PDF: download do relatorio LAI com 3 secoes

---

## Riscos e Mitigacao

| Risco | Impacto | Mitigacao |
|-------|---------|-----------|
| ContratoFactory sem data_publicacao gera +5pts em todos os testes | Testes RiscoService existentes quebram | Setar `data_publicacao` e `veiculo_publicacao` nos testes afetados |
| `app('tenant')` nulo no sidebar durante testes | Erro ao montar link Portal | Usar `app('tenant')?->slug ?? ''` |
| Route `portal.index` requer param `slug` | Link invalido | Usar `url("/{slug}/portal")` com slug do tenant |
