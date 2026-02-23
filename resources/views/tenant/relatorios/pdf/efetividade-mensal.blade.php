<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio de Efetividade Mensal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .page-break { page-break-after: always; }

        .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 16px; color: #1e40af; margin-bottom: 4px; }
        .header h2 { font-size: 12px; color: #555; font-weight: normal; }
        .header .meta { font-size: 9px; color: #888; margin-top: 6px; }

        .section-title { font-size: 12px; color: #1e40af; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin: 15px 0 8px; }

        .filtros { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 4px; margin-bottom: 15px; font-size: 9px; }
        .filtros strong { color: #334155; }

        .resumo { display: table; width: 100%; margin-bottom: 15px; }
        .resumo-item { display: table-cell; text-align: center; padding: 8px; border: 1px solid #ddd; }
        .resumo-item .valor { font-size: 18px; font-weight: bold; }
        .resumo-item .label { font-size: 9px; color: #666; }
        .resumo-item.verde .valor { color: #166534; }
        .resumo-item.vermelho .valor { color: #dc2626; }
        .resumo-item.amarelo .valor { color: #92400e; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; font-size: 8px; }
        th { background-color: #f1f5f9; font-weight: bold; color: #334155; font-size: 8px; }
        tr:nth-child(even) { background-color: #f8fafc; }

        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #dc2626; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }

        .efetividade-box { text-align: center; margin: 15px 0; padding: 12px; border: 2px solid #ddd; border-radius: 6px; }
        .efetividade-box .valor-grande { font-size: 28px; font-weight: bold; }
        .efetividade-box .descricao { font-size: 10px; color: #666; }

        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 4px; }
    </style>
</head>
<body>

<div class="footer">
    vigiacontratos — Relatorio gerado automaticamente em {{ $dados['data_geracao'] }} | Pagina <span class="pagenum"></span>
</div>

{{-- CABECALHO --}}
<div class="header">
    <h1>Relatorio de Efetividade Mensal</h1>
    <h2>{{ $dados['municipio'] }}</h2>
    <div class="meta">
        Gerado em: {{ $dados['data_geracao'] }} | Efetividade do Sistema de Alertas (RN-057)
    </div>
</div>

{{-- FILTROS --}}
<div class="filtros">
    <strong>Periodo:</strong> {{ $dados['filtros']['periodo'] }}
    | <strong>Secretaria:</strong> {{ $dados['filtros']['secretaria'] }}
</div>

{{-- RESUMO --}}
<h3 class="section-title">1. Resumo Executivo</h3>
<div class="resumo">
    <div class="resumo-item">
        <div class="valor">{{ $dados['resumo']['total_elegiveis'] }}</div>
        <div class="label">Contratos Monitorados</div>
    </div>
    <div class="resumo-item verde">
        <div class="valor">{{ $dados['resumo']['regularizados_a_tempo'] }}</div>
        <div class="label">Regularizados a Tempo</div>
    </div>
    <div class="resumo-item vermelho">
        <div class="valor">{{ $dados['resumo']['vencidos_sem_acao'] }}</div>
        <div class="label">Vencidos sem Acao</div>
    </div>
    <div class="resumo-item amarelo">
        <div class="valor">{{ $dados['resumo']['regularizados_retroativos'] }}</div>
        <div class="label">Regulariz. Retroativos</div>
    </div>
</div>

<div class="efetividade-box" style="border-color: {{ $dados['resumo']['taxa_efetividade'] >= 70 ? '#166534' : ($dados['resumo']['taxa_efetividade'] >= 40 ? '#92400e' : '#dc2626') }};">
    <div class="valor-grande" style="color: {{ $dados['resumo']['taxa_efetividade'] >= 70 ? '#166534' : ($dados['resumo']['taxa_efetividade'] >= 40 ? '#92400e' : '#dc2626') }};">
        {{ $dados['resumo']['taxa_efetividade'] }}%
    </div>
    <div class="descricao">Taxa de Efetividade — Tempo medio de antecipacao: {{ $dados['resumo']['tempo_medio_antecipacao'] }} dias</div>
</div>

{{-- POR SECRETARIA --}}
@if (count($dados['por_secretaria']) > 0)
<h3 class="section-title">2. Efetividade por Secretaria</h3>
<table>
    <thead>
        <tr>
            <th style="width: 35%;">Secretaria</th>
            <th style="width: 10%; text-align: center;">Total</th>
            <th style="width: 13%; text-align: center;">Regularizados</th>
            <th style="width: 13%; text-align: center;">Vencidos</th>
            <th style="width: 13%; text-align: center;">Retroativos</th>
            <th style="width: 16%; text-align: center;">Taxa (%)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dados['por_secretaria'] as $sec)
        <tr>
            <td>{{ $sec['secretaria'] }}</td>
            <td style="text-align: center;">{{ $sec['total'] }}</td>
            <td style="text-align: center; color: #166534; font-weight: bold;">{{ $sec['regularizados'] }}</td>
            <td style="text-align: center; color: #dc2626; font-weight: bold;">{{ $sec['vencidos'] }}</td>
            <td style="text-align: center; color: #92400e;">{{ $sec['retroativos'] }}</td>
            <td style="text-align: center;">
                <span class="badge badge-{{ $sec['taxa'] >= 70 ? 'success' : ($sec['taxa'] >= 40 ? 'warning' : 'danger') }}">
                    {{ $sec['taxa'] }}%
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- DETALHAMENTO --}}
@if (count($dados['contratos']) > 0)
<h3 class="section-title">3. Detalhamento dos Contratos</h3>
<table>
    <thead>
        <tr>
            <th style="width: 8%;">Numero</th>
            <th style="width: 25%;">Objeto</th>
            <th style="width: 15%;">Secretaria</th>
            <th style="width: 8%;">Data Fim</th>
            <th style="width: 10%;">Status</th>
            <th style="width: 12%;">Efetividade</th>
            <th style="width: 14%;">Aditivo</th>
            <th style="width: 8%; text-align: center;">Dias Ant.</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dados['contratos'] as $contrato)
        <tr>
            <td>{{ $contrato['numero'] }}</td>
            <td>{{ \Illuminate\Support\Str::limit($contrato['objeto'], 60) }}</td>
            <td>{{ $contrato['secretaria'] }}</td>
            <td>{{ $contrato['data_fim'] }}</td>
            <td>{{ $contrato['status_atual'] }}</td>
            <td>
                @php
                    $badgeClass = match($contrato['status_efetividade']) {
                        'regularizado_a_tempo' => 'badge-success',
                        'regularizado_retroativo' => 'badge-warning',
                        'vencido_sem_acao' => 'badge-danger',
                        default => '',
                    };
                    $badgeLabel = match($contrato['status_efetividade']) {
                        'regularizado_a_tempo' => 'Regularizado',
                        'regularizado_retroativo' => 'Retroativo',
                        'vencido_sem_acao' => 'Vencido',
                        default => '-',
                    };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
            </td>
            <td>{{ $contrato['aditivo'] }}</td>
            <td style="text-align: center;">{{ $contrato['dias_antecipacao'] !== null ? $contrato['dias_antecipacao'] . 'd' : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<p style="font-size: 8px; color: #888; margin-top: 20px; text-align: center;">
    Este documento foi gerado automaticamente pelo sistema vigiacontratos e mede a efetividade do sistema de alertas
    na prevencao de vencimentos contratuais descontrolados. Periodo: {{ $dados['filtros']['periodo'] }}.
</p>

</body>
</html>
