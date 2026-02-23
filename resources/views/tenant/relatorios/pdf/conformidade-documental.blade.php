<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio de Conformidade Documental</title>
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
        .bg-danger { background-color: #fee2e2; }
        .bg-warning { background-color: #fef3c7; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; font-size: 8px; }
        th { background-color: #f1f5f9; font-weight: bold; color: #334155; }
        tr:nth-child(even) { background-color: #f8fafc; }

        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-secondary { background-color: #f1f5f9; color: #475569; }

        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 4px; }

        .hash { font-family: monospace; font-size: 6px; word-break: break-all; }
    </style>
</head>
<body>

<div class="footer">
    vigiacontratos — Relatorio gerado automaticamente em {{ $dados['data_geracao'] }} | Pagina <span class="pagenum"></span>
</div>

{{-- CABECALHO --}}
<div class="header">
    <h1>Relatorio de Conformidade Documental</h1>
    <h2>{{ $dados['municipio'] }}</h2>
    <div class="meta">
        Gerado em: {{ $dados['data_geracao'] }} | Verificacao de Integridade SHA-256 (RN-225)
    </div>
</div>

<p style="font-size: 9px; color: #555; margin-bottom: 15px;">
    Este relatorio verifica a integridade de todos os documentos armazenados no sistema, recalculando o hash
    SHA-256 de cada arquivo e comparando com o valor registrado no momento do upload. Constitui instrumento
    de defesa para auditoria externa, comprovando que os documentos nao foram adulterados.
</p>

{{-- RESUMO --}}
<h3 class="section-title">1. Resumo da Verificacao</h3>
<div class="resumo">
    <div class="resumo-item">
        <div class="valor">{{ $dados['resumo']['total_documentos'] }}</div>
        <div class="label">Total Verificados</div>
    </div>
    <div class="resumo-item bg-success">
        <div class="valor">{{ $dados['resumo']['integros'] }}</div>
        <div class="label">Integros</div>
    </div>
    <div class="resumo-item bg-danger">
        <div class="valor">{{ $dados['resumo']['corrompidos'] }}</div>
        <div class="label">Corrompidos</div>
    </div>
    <div class="resumo-item bg-warning">
        <div class="valor">{{ $dados['resumo']['ausentes'] }}</div>
        <div class="label">Arquivos Ausentes</div>
    </div>
</div>

{{-- TABELA DETALHADA --}}
<h3 class="section-title">2. Detalhamento por Documento</h3>
@if (count($dados['documentos']) > 0)
<table>
    <thead>
        <tr>
            <th style="width: 8%;">Contrato</th>
            <th style="width: 12%;">Tipo</th>
            <th style="width: 18%;">Arquivo</th>
            <th style="width: 25%;">Hash SHA-256</th>
            <th style="width: 10%;">Data Upload</th>
            <th style="width: 12%;">Responsavel</th>
            <th style="width: 8%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dados['documentos'] as $doc)
            <tr>
                <td><strong>{{ $doc['contrato'] }}</strong></td>
                <td>{{ $doc['tipo_documento'] }}</td>
                <td>{{ $doc['nome_arquivo'] }}</td>
                <td><span class="hash">{{ $doc['hash_sha256'] }}</span></td>
                <td>{{ $doc['data_upload'] }}</td>
                <td>{{ $doc['responsavel'] }}</td>
                <td style="text-align: center;">
                    @php
                        $badgeClass = match($doc['status_integridade']) {
                            'integro' => 'badge-success',
                            'corrompido' => 'badge-danger',
                            'arquivo_ausente' => 'badge-warning',
                            default => 'badge-secondary',
                        };
                        $statusLabel = match($doc['status_integridade']) {
                            'integro' => 'Integro',
                            'corrompido' => 'Corrompido',
                            'arquivo_ausente' => 'Ausente',
                            'sem_hash' => 'Sem Hash',
                            default => $doc['status_integridade'],
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@else
<p style="font-size: 9px; color: #888; font-style: italic;">Nenhum documento encontrado no sistema.</p>
@endif

<p style="font-size: 8px; color: #888; margin-top: 20px; text-align: center;">
    Este documento foi gerado automaticamente pelo sistema vigiacontratos e constitui instrumento de defesa administrativa
    perante orgaos de controle, comprovando a integridade e preservacao dos documentos contratuais do municipio.
</p>

</body>
</html>
