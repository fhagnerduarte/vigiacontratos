@extends('portal.layout')

@section('content')
<div class="mb-3">
    <a href="{{ route('portal.contratos', $tenant->slug) }}" class="text-decoration-none">&larr; Voltar para listagem</a>
</div>

<h2 class="mb-4">Contrato {{ $contrato->numero }}</h2>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-12">
                <strong>Objeto:</strong>
                <p>{{ $contrato->objeto }}</p>
            </div>
            <div class="col-md-4">
                <strong>Fornecedor:</strong>
                <p>{{ $contrato->fornecedor?->razao_social }}<br>
                <small class="text-muted">CNPJ: {{ $contrato->fornecedor?->cnpj }}</small></p>
            </div>
            <div class="col-md-4">
                <strong>Secretaria:</strong>
                <p>{{ $contrato->secretaria?->nome }}</p>
            </div>
            <div class="col-md-4">
                <strong>Modalidade:</strong>
                <p>{{ $contrato->modalidade_contratacao?->label() ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <strong>Valor Global:</strong>
                <p class="text-success fw-bold">R$ {{ number_format($contrato->valor_global, 2, ',', '.') }}</p>
            </div>
            <div class="col-md-3">
                <strong>Valor Mensal:</strong>
                <p>R$ {{ number_format($contrato->valor_mensal, 2, ',', '.') }}</p>
            </div>
            <div class="col-md-3">
                <strong>Vigencia:</strong>
                <p>{{ $contrato->data_inicio?->format('d/m/Y') }} a {{ $contrato->data_fim?->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-3">
                <strong>Status:</strong>
                <p><span class="badge bg-{{ $contrato->status?->value === 'vigente' ? 'success' : 'secondary' }}">{{ $contrato->status?->value }}</span></p>
            </div>
            <div class="col-md-4">
                <strong>Processo Licitatorio:</strong>
                <p>{{ $contrato->numero_processo ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <strong>Fonte de Recurso:</strong>
                <p>{{ $contrato->fonte_recurso ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <strong>Data de Publicacao:</strong>
                <p>{{ $contrato->data_publicacao?->format('d/m/Y') ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

@if($contrato->aditivos->count() > 0)
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Aditivos</h5>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Numero</th>
                    <th>Tipo</th>
                    <th class="text-end">Valor Acrescimo</th>
                    <th>Data Inicio</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contrato->aditivos as $aditivo)
                <tr>
                    <td>{{ $aditivo->numero_sequencial }}o Termo Aditivo</td>
                    <td>{{ $aditivo->tipo?->label() ?? $aditivo->tipo }}</td>
                    <td class="text-end">R$ {{ number_format($aditivo->valor_acrescimo ?? 0, 2, ',', '.') }}</td>
                    <td>{{ $aditivo->data_inicio_vigencia?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $aditivo->status?->value ?? $aditivo->status }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
