<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Risco Contratual — TCE</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .page-break { page-break-after: always; }

        .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 16px; color: #1e40af; margin-bottom: 4px; }
        .header h2 { font-size: 12px; color: #555; font-weight: normal; }
        .header .meta { font-size: 9px; color: #888; margin-top: 6px; }

        .section-title { font-size: 12px; color: #1e40af; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin: 15px 0 8px; }

        .resumo { display: table; width: 100%; margin-bottom: 15px; }
        .resumo-item { display: table-cell; text-align: center; padding: 8px; border: 1px solid #ddd; }
        .resumo-item .valor { font-size: 18px; font-weight: bold; }
        .resumo-item .label { font-size: 9px; color: #666; }
        .bg-success { background-color: #dcfce7; }
        .bg-warning { background-color: #fef3c7; }
        .bg-danger { background-color: #fee2e2; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; font-size: 9px; }
        th { background-color: #f1f5f9; font-weight: bold; color: #334155; }
        tr:nth-child(even) { background-color: #f8fafc; }

        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }

        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 4px; }

        .justificativa-list { margin: 2px 0; padding-left: 12px; }
        .justificativa-list li { margin-bottom: 1px; font-size: 8px; }
    </style>
</head>
<body>

<div class="footer">
    vigiacontratos — Relatório gerado automaticamente em {{ $dados['data_geracao'] }} | Página <span class="pagenum"></span>
</div>

{{-- CABECALHO --}}
<div class="header">
    <h1>Relatório de Risco Contratual</h1>
    <h2>{{ $dados['municipio'] }}</h2>
    <div class="meta">
        Gerado em: {{ $dados['data_geracao'] }} | Tribunal de Contas do Estado — Instrumento de Defesa Administrativa
    </div>
</div>

{{-- RESUMO EXECUTIVO --}}
<h3 class="section-title">1. Resumo Executivo</h3>
<div class="resumo">
    <div class="resumo-item">
        <div class="valor">{{ $dados['resumo']['total_monitorados'] }}</div>
        <div class="label">Contratos Monitorados</div>
    </div>
    <div class="resumo-item bg-danger">
        <div class="valor">{{ $dados['resumo']['alto_risco'] }}</div>
        <div class="label">Alto Risco</div>
    </div>
    <div class="resumo-item bg-warning">
        <div class="valor">{{ $dados['resumo']['medio_risco'] }}</div>
        <div class="label">Médio Risco</div>
    </div>
    <div class="resumo-item bg-success">
        <div class="valor">{{ $dados['resumo']['baixo_risco'] }}</div>
        <div class="label">Baixo Risco</div>
    </div>
</div>

<p style="font-size: 9px; color: #555; margin-bottom: 15px;">
    Este relatório demonstra que o município de <strong>{{ $dados['municipio'] }}</strong> monitora proativamente
    seus contratos administrativos, identificando riscos e adotando medidas preventivas para garantir a conformidade
    legal e a eficiência na gestão dos recursos públicos.
</p>

{{-- TABELA DETALHADA --}}
<h3 class="section-title">2. Contratos Monitorados — Detalhamento de Risco</h3>
<table>
    <thead>
        <tr>
            <th style="width: 8%;">Contrato</th>
            <th style="width: 18%;">Objeto</th>
            <th style="width: 12%;">Fornecedor</th>
            <th style="width: 10%;">Secretaria</th>
            <th style="width: 8%;">Valor (R$)</th>
            <th style="width: 6%;">Vigência</th>
            <th style="width: 4%;">Score</th>
            <th style="width: 5%;">Nível</th>
            <th style="width: 10%;">Categorias</th>
            <th style="width: 19%;">Justificativas</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dados['contratos'] as $contrato)
            <tr>
                <td><strong>{{ $contrato['numero'] }}</strong></td>
                <td>{{ \Illuminate\Support\Str::limit($contrato['objeto'], 60) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($contrato['fornecedor'] ?? '-', 30) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($contrato['secretaria'] ?? '-', 20) }}</td>
                <td style="text-align: right;">{{ number_format($contrato['valor_global'], 2, ',', '.') }}</td>
                <td style="text-align: center;">
                    {{ $contrato['data_inicio'] }}<br>a {{ $contrato['data_fim'] ?? '-' }}
                </td>
                <td style="text-align: center; font-weight: bold;">{{ $contrato['score'] }}</td>
                <td style="text-align: center;">
                    <span class="badge badge-{{ $contrato['cor_nivel'] }}">{{ $contrato['nivel'] }}</span>
                </td>
                <td>
                    @foreach ($contrato['categorias'] as $cat)
                        <span class="badge badge-warning" style="margin: 1px 0;">{{ $cat }}</span>
                    @endforeach
                </td>
                <td>
                    @if (!empty($contrato['justificativas']))
                        <ul class="justificativa-list">
                            @foreach (array_slice($contrato['justificativas'], 0, 4) as $just)
                                <li>{{ $just }}</li>
                            @endforeach
                            @if (count($contrato['justificativas']) > 4)
                                <li><em>+ {{ count($contrato['justificativas']) - 4 }} critério(s)</em></li>
                            @endif
                        </ul>
                    @else
                        <em>Nenhum critério ativado</em>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- PLANO DE AÇÃO --}}
<h3 class="section-title">3. Plano de Ação Sugerido</h3>
<table>
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Ação</th>
            <th style="width: 30%;">Contratos Impactados</th>
            <th style="width: 15%;">Prazo Sugerido</th>
            <th style="width: 15%;">Responsável</th>
        </tr>
    </thead>
    <tbody>
        @if ($dados['resumo']['alto_risco'] > 0)
            <tr>
                <td>1</td>
                <td>Revisar contratos com score de risco alto e providenciar regularização imediata</td>
                <td>{{ $dados['resumo']['alto_risco'] }} contrato(s) com risco alto</td>
                <td>Imediato (7 dias)</td>
                <td>Controladoria / Gestor</td>
            </tr>
        @endif
        <tr>
            <td>{{ $dados['resumo']['alto_risco'] > 0 ? 2 : 1 }}</td>
            <td>Completar documentação obrigatória dos contratos com pendências documentais</td>
            <td>Contratos com categoria "Documental" ativa</td>
            <td>15 dias</td>
            <td>Gestor de Contrato</td>
        </tr>
        <tr>
            <td>{{ $dados['resumo']['alto_risco'] > 0 ? 3 : 2 }}</td>
            <td>Designar fiscal para contratos sem fiscal atribuído</td>
            <td>Contratos com categoria "Operacional" ativa</td>
            <td>7 dias</td>
            <td>Secretaria responsável</td>
        </tr>
        <tr>
            <td>{{ $dados['resumo']['alto_risco'] > 0 ? 4 : 3 }}</td>
            <td>Iniciar processo de renovação ou nova licitação para contratos próximos do vencimento</td>
            <td>Contratos com categoria "Vencimento" ativa</td>
            <td>30 dias antes do vencimento</td>
            <td>Gestor / Licitação</td>
        </tr>
        <tr>
            <td>{{ $dados['resumo']['alto_risco'] > 0 ? 5 : 4 }}</td>
            <td>Avaliar justificativas de aditivos que ultrapassaram o limite legal</td>
            <td>Contratos com categoria "Financeiro" ou "Jurídico" ativa</td>
            <td>15 dias</td>
            <td>Procuradoria / Controladoria</td>
        </tr>
    </tbody>
</table>

<p style="font-size: 8px; color: #888; margin-top: 20px; text-align: center;">
    Este documento foi gerado automaticamente pelo sistema vigiacontratos e constitui instrumento de defesa administrativa
    perante o Tribunal de Contas do Estado, demonstrando o monitoramento proativo da gestão contratual municipal.
</p>

</body>
</html>
