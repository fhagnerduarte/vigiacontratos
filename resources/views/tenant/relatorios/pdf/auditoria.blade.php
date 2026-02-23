<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio de Auditoria</title>
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

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; font-size: 8px; }
        th { background-color: #f1f5f9; font-weight: bold; color: #334155; font-size: 8px; }
        tr:nth-child(even) { background-color: #f8fafc; }

        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-alteracao { background-color: #dbeafe; color: #1e40af; }
        .badge-login { background-color: #dcfce7; color: #166534; }
        .badge-acesso { background-color: #fef3c7; color: #92400e; }

        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 4px; }
    </style>
</head>
<body>

<div class="footer">
    vigiacontratos — Relatorio gerado automaticamente em {{ $dados['data_geracao'] }} | Pagina <span class="pagenum"></span>
</div>

{{-- CABECALHO --}}
<div class="header">
    <h1>Relatorio de Auditoria</h1>
    <h2>{{ $dados['municipio'] }}</h2>
    <div class="meta">
        Gerado em: {{ $dados['data_geracao'] }} | Logs de Atividade do Sistema (RN-222)
    </div>
</div>

{{-- FILTROS APLICADOS --}}
<div class="filtros">
    <strong>Filtros aplicados:</strong>
    Periodo: {{ $dados['filtros']['data_inicio'] }} a {{ $dados['filtros']['data_fim'] }}
    | Tipo: {{ $dados['filtros']['tipo_acao'] }}
    | Entidade: {{ $dados['filtros']['entidade'] }}
</div>

{{-- RESUMO --}}
<h3 class="section-title">1. Resumo</h3>
<div class="resumo">
    <div class="resumo-item">
        <div class="valor">{{ $dados['total_registros'] }}</div>
        <div class="label">Total de Registros</div>
    </div>
    @foreach ($dados['resumo'] as $tipo => $count)
    <div class="resumo-item">
        <div class="valor">{{ $count }}</div>
        <div class="label">{{ $tipo }}</div>
    </div>
    @endforeach
</div>

{{-- TABELA DETALHADA --}}
<h3 class="section-title">2. Registros Detalhados</h3>
@if (count($dados['registros']) > 0)
<table>
    <thead>
        <tr>
            <th style="width: 10%;">Data/Hora</th>
            <th style="width: 8%;">Tipo</th>
            <th style="width: 12%;">Usuario</th>
            <th style="width: 10%;">Perfil</th>
            <th style="width: 25%;">Descricao</th>
            <th style="width: 25%;">Detalhes</th>
            <th style="width: 10%;">IP</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dados['registros'] as $registro)
            <tr>
                <td>{{ $registro['data'] }}</td>
                <td>
                    @php
                        $badgeClass = match($registro['tipo']) {
                            'Alteracao' => 'badge-alteracao',
                            'Login' => 'badge-login',
                            default => 'badge-acesso',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $registro['tipo'] }}</span>
                </td>
                <td>{{ $registro['usuario'] }}</td>
                <td>{{ $registro['perfil'] }}</td>
                <td>{{ \Illuminate\Support\Str::limit($registro['descricao'], 80) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($registro['detalhes'], 80) }}</td>
                <td>{{ $registro['ip'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@else
<p style="font-size: 9px; color: #888; font-style: italic;">Nenhum registro encontrado com os filtros aplicados.</p>
@endif

<p style="font-size: 8px; color: #888; margin-top: 20px; text-align: center;">
    Este documento foi gerado automaticamente pelo sistema vigiacontratos e constitui registro de auditoria
    para fins de conformidade e transparencia na gestao contratual municipal.
</p>

</body>
</html>
