<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio de Documentos — Contrato {{ $dados['contrato']['numero'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }

        .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 16px; color: #1e40af; margin-bottom: 4px; }
        .header h2 { font-size: 12px; color: #555; font-weight: normal; }
        .header .meta { font-size: 9px; color: #888; margin-top: 6px; }

        .section-title { font-size: 12px; color: #1e40af; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin: 15px 0 8px; }

        .info-grid { display: table; width: 100%; margin-bottom: 15px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; font-weight: bold; padding: 3px 8px 3px 0; width: 130px; font-size: 9px; color: #555; }
        .info-value { display: table-cell; padding: 3px 0; font-size: 9px; }

        .completude { padding: 6px 10px; border-radius: 4px; font-weight: bold; font-size: 10px; display: inline-block; margin-bottom: 10px; }
        .completude-completo { background-color: #dcfce7; color: #166534; }
        .completude-parcial { background-color: #fef3c7; color: #92400e; }
        .completude-incompleto { background-color: #fee2e2; color: #991b1b; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; font-size: 9px; }
        th { background-color: #f1f5f9; font-weight: bold; color: #334155; }
        tr:nth-child(even) { background-color: #f8fafc; }

        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 4px; }
    </style>
</head>
<body>

<div class="footer">
    vigiacontratos — Relatorio gerado automaticamente em {{ $dados['data_geracao'] }} | Pagina <span class="pagenum"></span>
</div>

{{-- CABECALHO --}}
<div class="header">
    <h1>Relatorio de Documentos Contratuais</h1>
    <h2>{{ $dados['municipio'] }}</h2>
    <div class="meta">
        Gerado em: {{ $dados['data_geracao'] }} | Tribunal de Contas do Estado — Listagem Documental (RN-133)
    </div>
</div>

{{-- DADOS DO CONTRATO --}}
<h3 class="section-title">1. Dados do Contrato</h3>
<div class="info-grid">
    <div class="info-row">
        <div class="info-label">Numero:</div>
        <div class="info-value"><strong>{{ $dados['contrato']['numero'] }}</strong></div>
    </div>
    <div class="info-row">
        <div class="info-label">Objeto:</div>
        <div class="info-value">{{ $dados['contrato']['objeto'] }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Fornecedor:</div>
        <div class="info-value">{{ $dados['contrato']['fornecedor'] }} (CNPJ: {{ $dados['contrato']['cnpj'] }})</div>
    </div>
    <div class="info-row">
        <div class="info-label">Secretaria:</div>
        <div class="info-value">{{ $dados['contrato']['secretaria'] }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Valor Global:</div>
        <div class="info-value">R$ {{ number_format($dados['contrato']['valor_global'], 2, ',', '.') }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vigencia:</div>
        <div class="info-value">{{ $dados['contrato']['data_inicio'] }} a {{ $dados['contrato']['data_fim'] }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Status:</div>
        <div class="info-value">{{ $dados['contrato']['status'] }}</div>
    </div>
</div>

{{-- STATUS DE COMPLETUDE --}}
<h3 class="section-title">2. Status de Completude Documental</h3>
<div class="completude completude-{{ strtolower(str_replace(' ', '', $dados['completude']['cor'])) }}">
    {{ $dados['completude']['status'] }}
</div>
<p style="font-size: 9px; color: #555; margin-bottom: 15px;">
    Total de documentos na versao atual: <strong>{{ $dados['total_documentos'] }}</strong>
</p>

{{-- TABELA DE DOCUMENTOS --}}
<h3 class="section-title">3. Documentos Vinculados</h3>
@if (count($dados['documentos']) > 0)
<table>
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 20%;">Tipo Documento</th>
            <th style="width: 30%;">Nome Arquivo</th>
            <th style="width: 8%;">Versao</th>
            <th style="width: 15%;">Data Upload</th>
            <th style="width: 15%;">Responsavel</th>
            <th style="width: 7%;">Tamanho</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($dados['documentos'] as $i => $doc)
            <tr>
                <td style="text-align: center;">{{ $i + 1 }}</td>
                <td>{{ $doc['tipo_documento'] }}</td>
                <td>{{ $doc['nome_arquivo'] }}</td>
                <td style="text-align: center;">v{{ $doc['versao'] }}</td>
                <td>{{ $doc['data_upload'] }}</td>
                <td>{{ $doc['responsavel'] }}</td>
                <td style="text-align: right;">{{ $doc['tamanho_kb'] }} KB</td>
            </tr>
        @endforeach
    </tbody>
</table>
@else
<p style="font-size: 9px; color: #888; font-style: italic;">Nenhum documento vinculado a este contrato.</p>
@endif

<p style="font-size: 8px; color: #888; margin-top: 20px; text-align: center;">
    Este documento foi gerado automaticamente pelo sistema vigiacontratos e constitui instrumento de
    conferencia documental para fins de auditoria e prestacao de contas perante o Tribunal de Contas do Estado.
</p>

</body>
</html>
