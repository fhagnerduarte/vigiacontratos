@extends('portal.layout')

@section('content')
<h2 class="mb-4">Transparencia Contratual</h2>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-primary fw-bold mb-1">{{ number_format($indicadores['total_contratos']) }}</h3>
                <p class="text-muted mb-0">Contratos Publicados</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-success fw-bold mb-1">R$ {{ number_format($indicadores['valor_total'], 2, ',', '.') }}</h3>
                <p class="text-muted mb-0">Valor Total Contratado</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-info fw-bold mb-1">{{ number_format($indicadores['contratos_vigentes']) }}</h3>
                <p class="text-muted mb-0">Contratos Vigentes</p>
            </div>
        </div>
    </div>
</div>

@if(!empty($indicadores['por_secretaria']))
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Contratos por Secretaria</h5>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Secretaria</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($indicadores['por_secretaria'] as $item)
                <tr>
                    <td>{{ $item['nome'] }}</td>
                    <td class="text-end">{{ $item['total'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
