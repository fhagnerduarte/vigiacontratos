# Detalhes IMPs 049-060

## IMP-049 — Campos Compliance + Fiscal Substituto
- Migration 000040: contratos +7 campos, fiscais +3
- Enums: RegimeExecucao (5), TipoFiscal (titular/substituto), TipoDocumentoContratual +3 (15 total)
- FiscalService: designarSubstituto() nao desativa titular (Lei 14.133 art. 117)
- Testes: 22 testes, 77 assertions (ComplianceFieldsTest)

## IMP-050 — Checklist por Fase Contratual
- Migration 000041: configuracoes_checklist_documento +fase, contrato_conformidade_fases
- FaseContratual enum: 7 fases com label/icone/ordem
- ChecklistService: obterChecklistPorFase, calcularConformidade (semaforo verde/amarelo/vermelho)
- Testes: 30+ testes (ChecklistFaseTest)

## IMP-051 — Motor Alertas Completo (Regras 2-10)
- ConfiguracaoAlertaAvancado, 7 novos tipos alerta

## IMP-052 — Encerramento Contratual
- Workflow 6 etapas, Encerramento model, EtapaEncerramento enum
- Testes: 35 testes

## IMP-053 — Execucao Financeira Avancada
- TipoExecucaoFinanceira enum, saldo contratual, empenho insuficiente
- Testes: 30 testes

## IMP-054 — Ocorrencias + Relatorios Fiscais
- Migration 000045: tabelas ocorrencias e relatorios_fiscais
- TipoOcorrencia enum: 6 cases
- OcorrenciaService + RelatorioFiscalService
- Permissoes: 5 novas (total 53)
- Testes: 25 testes

## IMP-055 — Score + Session + Integracao
- NivelRisco labels TCE: Regular/Atencao/Critico
- RiscoService +ocorrencias/relatorios
- Testes E2E: 17 testes

## IMP-056 — Classificacao Sigilo + Campos LAI
- Migration 000046: contratos +5 campos, documentos +2
- ClassificacaoSigilo enum: 4 cases
- ClassificacaoService + command lai:verificar-desclassificacao
- Permissoes: 4 novas (total 57)
- Testes: 24 testes, 62 assertions

## IMP-057 — Portal Publico Contratos
- ResolveTenantPublic middleware, routes/portal.php
- PortalController + DadosAbertosService
- Testes: 16 testes, 44 assertions

## IMP-058 — SIC/e-SIC
- Migration 000047: solicitacoes_lai + historico_solicitacoes_lai
- StatusSolicitacaoLai (6 cases), ClassificacaoRespostaLai (3 cases)
- SolicitacaoLaiService: protocolo LAI-{YYYY}-{SEQ}, prazo 20+10 dias
- Permissoes: 6 novas (total 63)
- Testes: 32 testes, 139 assertions

## IMP-059 — Alertas LAI + Publicacao Auto
- +4 TipoEventoAlerta, lai:publicar-automatico, dashboard LAI
- Testes: 20 testes, 50 assertions

## IMP-060 — Integracao Final LAI + E2E
- RiscoService +transparencia (6 categorias total)
- Relatorio LAI PDF, sidebar dropdown
- Testes: 15 testes, 67 assertions
