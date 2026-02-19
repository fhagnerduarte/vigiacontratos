# Conhecimento — Módulo: Documentos

> Extraído de `banco-de-conhecimento.md`. Carregar quando trabalhando no módulo de Documentos / Central de Documentos.
> Inclui: Regras (RN-020 a RN-022, RN-039 a RN-042, RN-118 a RN-135), Fluxo (12), Fórmulas (completude documental, indicadores dashboard documentos).

---

## Regras de Negócio

### Módulo: Documentos

| ID | Regra | Detalhamento |
|---|---|---|
| RN-020 | Documentos são vinculados a um contrato ou aditivo | Relação polimórfica (documentable) |
| RN-021 | Apenas PDF é aceito para documentos contratuais | Validação de tipo MIME no upload |
| RN-022 | Tamanho máximo de upload: 20MB por arquivo | Validação no Form Request (ADR-032 — ampliado de 10MB para 20MB) |
| RN-039 | Upload múltiplo de documentos é permitido | Vários arquivos podem ser enviados de uma vez |
| RN-040 | Todo documento deve ter tipo classificado | tipo_documento obrigatório (contrato_original, termo_referencia, etc.) |
| RN-041 | Documentos possuem versionamento | Ao reuplodar documento do mesmo tipo, versão é incrementada automaticamente |
| RN-042 | Registro automático de quem anexou o documento | uploaded_by + data/hora registrados automaticamente |

### Módulo: Documentos — Central de Documentos (Módulo 5)

| ID | Regra | Detalhamento |
|---|---|---|
| RN-118 | Cada contrato possui uma pasta digital própria com estrutura hierárquica padrão | A pasta é identificada por `contrato_id` e organizada por tipo de documento. Os tipos obrigatórios padrão são: contrato_original, publicacao_oficial, parecer_juridico, nota_empenho |
| RN-119 | O limite de tamanho por arquivo é 20MB | Validação no StoreDocumentoRequest. Aplica-se a todos os tipos de documento (ADR-032) |
| RN-120 | O versionamento é automático e não-destrutivo | Ao fazer upload de documento do mesmo tipo no mesmo contrato, o sistema cria nova versão. A versão anterior não é deletada — apenas `is_versao_atual` é setado para false |
| RN-121 | O nome do arquivo é padronizado automaticamente pelo sistema | Formato: `contrato_{numero_contrato}_{tipo_documento}_v{versao}.pdf`. Nome original do usuário é preservado no campo `nome_original` |
| RN-122 | Todo acesso a documento é registrado no log de acesso | Ações logadas: upload, download, substituição, exclusão, visualização. Campos: user_id, acao, documento_id, ip_address, created_at. Tabela append-only (ADR-035) |
| RN-123 | Documentos são armazenados em diretórios isolados por contrato e tipo | Estrutura de storage: `documentos/contratos/{contrato_id}/{tipo_documento}/{arquivo}`. Nunca acessíveis via URL pública (ADR-033) |
| RN-124 | Contrato vigente sem documento do tipo contrato_original gera alerta de incompletude no dashboard | Alerta visual na listagem e no dashboard de documentos. Eleva score de risco (critério: sem documento — +20 pontos) |
| RN-125 | Aditivo sem documento do tipo aditivo_doc gera alerta de incompletude | Toda entidade Aditivo deve ter pelo menos um documento do tipo aditivo_doc vinculado |
| RN-126 | Prorrogação de prazo sem parecer_juridico vinculado gera alerta | Validação ativa quando aditivo.tipo = prazo e não existe documento tipo parecer_juridico vinculado ao aditivo |
| RN-127 | Contrato com valor_global acima de R$ 500.000 sem publicacao_oficial gera alerta de incompletude | Alerta visual e indicador no dashboard de documentos |
| RN-128 | A completude documental do contrato é classificada em três níveis | Completo (verde): possui todos os documentos do checklist obrigatório. Parcial (amarelo): possui pelo menos o contrato_original mas faltam outros. Incompleto (vermelho): não possui contrato_original |
| RN-129 | O checklist de documentos obrigatórios por contrato é configurável | Configuração padrão do sistema: contrato_original, publicacao_oficial, parecer_juridico, nota_empenho. Admin pode personalizar o checklist por tipo de contrato |
| RN-130 | Acesso a documentos é controlado por perfil de usuário (RBAC) | Permissões via role + secretaria vinculada. Administrador Geral e Controladoria: acesso total. Gestor/Fiscal: upload + download para contratos das secretarias vinculadas. Gabinete: somente leitura. Implementado via DocumentoPolicy + verificação de permissão `documento.{ação}` (RN-302) |
| RN-131 | A busca de documentos suporta filtros combinados | Por número de contrato, fornecedor, tipo de documento, palavra-chave no nome do arquivo, período de upload |
| RN-132 | O dashboard de documentos exibe 4 indicadores de completude | (1) % contratos com documentação completa; (2) total de contratos sem contrato_original; (3) total de aditivos sem documento vinculado; (4) ranking das 5 secretarias com maior pendência documental |
| RN-133 | O relatório para Tribunal de Contas lista todos os documentos de um contrato | Campos: tipo_documento, nome_arquivo, versao, data_upload, responsável (uploaded_by), status. Exportável em PDF via RelatorioService |
| RN-134 | Documentos excluídos não são removidos do storage | Exclusão é lógica (soft delete via campo `deleted_at`). Log de exclusão registrado. Admin pode restaurar |
| RN-135 | Funcionalidades de OCR e busca full-text em PDF são Fase 2 | OCR (Tesseract ou cloud API), extração automática de CNPJ/datas/valores, busca interna no conteúdo do PDF e auto-preenchimento de formulários são funcionalidades de evolução futura. Não implementar em V1 (ADR-037) |

## Fluxos de Negócio

### Fluxo 12: Gestão de Documentos na Central de Documentos (Módulo 5)

```
[1. Usuário acessa contrato → aba Documentos OU acessa Central de Documentos]
       │
       ▼
[2. Sistema carrega pasta digital do contrato (RN-118)]
   ├── Lista documentos agrupados por tipo
   ├── Exibe status de completude (RN-128)
   └── Exibe checklist de documentos obrigatórios (RN-129)
       │
       ▼
[3. Usuário clica em "Adicionar Documento"]
       │
       ▼
[4. Preenche dados do upload]
   ├── Tipo do documento (obrigatório — TipoDocumentoContratual) (RN-040)
   ├── Seleciona arquivo PDF (obrigatório — RN-021)
   ├── Descrição (opcional)
   └── Confirma upload
       │
       ▼
[5. Sistema valida]
   ├── Tipo MIME = application/pdf (RN-021)
   ├── Tamanho ≤ 20MB (RN-119)
   └── Tipo de documento preenchido (RN-040)
       │
   ┌───┴───┐
   ▼       ▼
[OK]    [Erro → Exibe erro de validação]
   │
   ▼
[6. Sistema determina versão]
   ├── Busca documentos existentes do mesmo tipo no contrato
   ├── Se existe → versao = MAX(versao) + 1; is_versao_atual dos anteriores = false
   └── Se não existe → versao = 1 (RN-120)
       │
       ▼
[7. Sistema salva documento]
   ├── Gera nome padronizado de arquivo (RN-121)
   ├── Armazena em documentos/contratos/{contrato_id}/{tipo_documento}/ (RN-123)
   ├── Cria registro na tabela documentos com uploaded_by + created_at (RN-042)
   └── Marca is_versao_atual = true
       │
       ▼
[8. Sistema registra log de acesso]
   └── Ação: upload | user_id | documento_id | ip_address | created_at (RN-122)
       │
       ▼
[9. Sistema recalcula completude documental do contrato (RN-128)]
   ├── Verifica checklist obrigatório (RN-129)
   ├── Atualiza status_completude (completo/parcial/incompleto)
   └── Se agora completo → remove alerta de incompletude do dashboard (RN-124)
       │
       ▼
[10. Score de risco do contrato recalculado]
    └── Se contrato_original presente → remove critério "sem documento" do score (RN-029)
```

**Regras associadas:** RN-020 a RN-022, RN-039 a RN-042, RN-118 a RN-134

## Fórmulas e Cálculos

### Fórmula: Completude Documental do Contrato

```
checklist_obrigatorio = [contrato_original, publicacao_oficial, parecer_juridico, nota_empenho]
(configurável pelo admin — RN-129)

documentos_presentes = documentos WHERE contrato_id = X AND is_versao_atual = true AND deleted_at IS NULL
tipos_presentes = DISTINCT tipo_documento dos documentos_presentes
tipos_no_checklist_presentes = INTERSECT(tipos_presentes, checklist_obrigatorio)

SE COUNT(tipos_no_checklist_presentes) = COUNT(checklist_obrigatorio):
    status_completude = completo (verde)
SE COUNT(tipos_no_checklist_presentes) > 0 E contrato_original IN tipos_presentes:
    status_completude = parcial (amarelo)
SE contrato_original NOT IN tipos_presentes:
    status_completude = incompleto (vermelho)
```

| Variável | Descrição | Fonte |
|---|---|---|
| checklist_obrigatorio | Lista de tipos de documento obrigatórios | Configuração do sistema (RN-129) |
| documentos_presentes | Documentos ativos (versão atual, não deletados) do contrato | Tabela `documentos` filtrada |
| tipos_no_checklist_presentes | Tipos obrigatórios que possuem ao menos um documento | Interseção lógica |

*Nota: RN-128. Calculado pelo DocumentoObserver ao criar/excluir documento.*

### Fórmula: Indicadores do Dashboard de Documentos

```
pct_contratos_completos     = COUNT(contratos WHERE status_completude = completo AND status = vigente) / total_contratos_ativos * 100
total_sem_contrato_original = COUNT(contratos WHERE status = vigente AND NOT EXISTS(documentos WHERE tipo_documento = contrato_original AND is_versao_atual = true AND deleted_at IS NULL))
total_aditivos_sem_doc      = COUNT(aditivos WHERE status = vigente AND NOT EXISTS(documentos WHERE tipo_documento = aditivo_doc AND is_versao_atual = true))
ranking_secretarias_pendencia = TOP 5 secretarias ORDER BY (contratos WHERE status_completude != completo / total_contratos_secretaria) DESC
```

| Indicador | Descrição | Regra |
|---|---|---|
| % contratos completos | Percentual de contratos ativos com documentação completa | RN-132 (1) |
| Total sem contrato original | Contratos ativos sem o documento mais básico | RN-132 (2) |
| Total aditivos sem documento | Aditivos vigentes sem documento vinculado | RN-132 (3) |
| Ranking secretarias pendentes | Top 5 secretarias com maior % de contratos incompletos | RN-132 (4) |

*Nota: RN-132. Exibidos no dashboard da Central de Documentos.*
