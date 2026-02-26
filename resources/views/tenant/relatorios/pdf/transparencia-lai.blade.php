<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Transparência — LAI 12.527/2011</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }

        .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 16px; color: #1e40af; margin-bottom: 4px; }
        .header h2 { font-size: 12px; color: #555; font-weight: normal; }
        .header .meta { font-size: 9px; color: #888; margin-top: 6px; }

        .section-title { font-size: 12px; color: #1e40af; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin: 15px 0 8px; }

        .resumo { display: table; width: 100%; margin-bottom: 15px; }
        .resumo-item { display: table-cell; text-align: center; padding: 8px; border: 1px solid #ddd; }
        .resumo-item .valor { font-size: 18px; font-weight: bold; }
        .resumo-item .label { font-size: 9px; color: #666; }
        .resumo-item.verde .valor { color: #166534; }
        .resumo-item.vermelho .valor { color: #dc2626; }
        .resumo-item.amarelo .valor { color: #92400e; }
        .resumo-item.azul .valor { color: #1e40af; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; font-size: 9px; }
        th { background-color: #f1f5f9; font-weight: bold; color: #334155; }
        tr:nth-child(even) { background-color: #f8fafc; }

        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #dc2626; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }

        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 4px; }
    </style>
</head>
<body>

<div class="footer">
    vigiacontratos — Relatório gerado automaticamente em {{ $dados['data_geracao'] }} | Página <span class="pagenum"></span>
</div>

{{-- CABECALHO --}}
<div class="header">
    <h1>Relatório de Transparência</h1>
    <h2>{{ $dados['municipio'] }}</h2>
    <div class="meta">
        Gerado em: {{ $dados['data_geracao'] }} | Lei de Acesso a Informação — LAI 12.527/2011
    </div>
</div>

{{-- SECAO 1: TRANSPARENCIA ATIVA --}}
<h3 class="section-title">1. Transparência Ativa — Publicação de Contratos</h3>
<div class="resumo">
    <div class="resumo-item azul">
        <div class="valor">{{ $dados['resumo']['total_contratos'] }}</div>
        <div class="label">Total de Contratos</div>
    </div>
    <div class="resumo-item verde">
        <div class="valor">{{ $dados['resumo']['contratos_publicos'] }}</div>
        <div class="label">Contratos Públicos</div>
    </div>
    <div class="resumo-item verde">
        <div class="valor">{{ $dados['resumo']['publicados_portal'] }}</div>
        <div class="label">Publicados no Portal</div>
    </div>
    <div class="resumo-item vermelho">
        <div class="valor">{{ $dados['resumo']['nao_publicados'] }}</div>
        <div class="label">Não Publicados</div>
    </div>
    <div class="resumo-item amarelo">
        <div class="valor">{{ $dados['resumo']['sem_dados_publicacao'] }}</div>
        <div class="label">Sem Dados de Publicação</div>
    </div>
</div>

{{-- SEÇÃO 2: CLASSIFICAÇÃO DE SIGILO --}}
<h3 class="section-title">2. Classificação de Sigilo — LAI Art. 24</h3>
<table>
    <thead>
        <tr>
            <th style="width: 40%;">Classificação</th>
            <th style="width: 30%; text-align: center;">Total de Contratos</th>
            <th style="width: 30%; text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dados['classificacao'] as $cls)
        <tr>
            <td>{{ $cls['classificacao'] }}</td>
            <td style="text-align: center; font-weight: bold;">{{ $cls['total'] }}</td>
            <td style="text-align: center;">
                @if ($cls['total'] > 0)
                    <span class="badge badge-{{ $cls['cor'] === 'success' ? 'success' : ($cls['cor'] === 'danger' || $cls['cor'] === 'dark' ? 'danger' : 'warning') }}">
                        {{ $cls['total'] }} contrato(s)
                    </span>
                @else
                    -
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if ($dados['resumo']['sigilo_sem_justificativa'] > 0)
<p style="margin-bottom: 15px;">
    <span class="badge badge-warning">ATENÇÃO</span>
    {{ $dados['resumo']['sigilo_sem_justificativa'] }} contrato(s) classificado(s) com sigilo sem justificativa registrada.
    A LAI art. 24 exige fundamentação para qualquer restrição de acesso.
</p>
@endif

{{-- SECAO 3: TRANSPARENCIA PASSIVA (SIC/e-SIC) --}}
<h3 class="section-title">3. Transparência Passiva — SIC/e-SIC (LAI Art. 9)</h3>
<div class="resumo">
    <div class="resumo-item azul">
        <div class="valor">{{ $dados['sic']['total_solicitacoes'] }}</div>
        <div class="label">Total de Solicitações</div>
    </div>
    <div class="resumo-item amarelo">
        <div class="valor">{{ $dados['sic']['pendentes'] }}</div>
        <div class="label">Pendentes</div>
    </div>
    <div class="resumo-item verde">
        <div class="valor">{{ $dados['sic']['respondidas'] }}</div>
        <div class="label">Respondidas</div>
    </div>
    <div class="resumo-item vermelho">
        <div class="valor">{{ $dados['sic']['vencidas'] }}</div>
        <div class="label">Vencidas (Prazo Expirado)</div>
    </div>
    <div class="resumo-item">
        <div class="valor">{{ $dados['sic']['tempo_medio_resposta'] }}</div>
        <div class="label">Tempo Médio de Resposta (dias)</div>
    </div>
</div>

@if ($dados['sic']['vencidas'] > 0)
<p style="margin-bottom: 15px;">
    <span class="badge badge-danger">ALERTA</span>
    {{ $dados['sic']['vencidas'] }} solicitação(ões) com prazo legal vencido.
    A LAI art. 11 estabelece prazo de 20 dias úteis (prorrogável por +10 dias) para resposta.
</p>
@endif

<p style="font-size: 8px; color: #888; margin-top: 20px; text-align: center;">
    Este documento foi gerado automaticamente pelo sistema vigiacontratos e consolida indicadores de conformidade
    com a Lei de Acesso a Informação (Lei 12.527/2011). Os dados refletem o estado no momento da geração.
</p>

</body>
</html>
