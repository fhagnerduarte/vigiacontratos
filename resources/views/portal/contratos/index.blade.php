@extends('portal.layout')

@section('content')
<h2 class="mb-4">Contratos Publicos</h2>

<form method="GET" class="row g-2 mb-4">
    <div class="col-md-4">
        <input type="text" name="busca" class="form-control" placeholder="Buscar por numero ou objeto..." value="{{ request('busca') }}">
    </div>
    <div class="col-md-2">
        <input type="text" name="ano" class="form-control" placeholder="Ano" value="{{ request('ano') }}">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Numero</th>
                <th>Objeto</th>
                <th>Fornecedor</th>
                <th>Secretaria</th>
                <th class="text-end">Valor Global</th>
                <th>Vigencia</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contratos as $contrato)
            <tr>
                <td>
                    <a href="{{ route('portal.contratos.show', [$tenant->slug, $contrato->numero]) }}">
                        {{ $contrato->numero }}
                    </a>
                </td>
                <td>{{ Str::limit($contrato->objeto, 60) }}</td>
                <td>{{ $contrato->fornecedor?->razao_social }}</td>
                <td>{{ $contrato->secretaria?->nome }}</td>
                <td class="text-end">R$ {{ number_format($contrato->valor_global, 2, ',', '.') }}</td>
                <td>{{ $contrato->data_inicio?->format('d/m/Y') }} a {{ $contrato->data_fim?->format('d/m/Y') }}</td>
                <td><span class="badge bg-{{ $contrato->status?->value === 'vigente' ? 'success' : 'secondary' }}">{{ $contrato->status?->value }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">Nenhum contrato encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $contratos->withQueryString()->links() }}
@endsection
