@extends('portal.layout')

@section('content')
<h2 class="mb-4">Fornecedores</h2>

<form method="GET" class="row g-2 mb-4">
    <div class="col-md-4">
        <input type="text" name="busca" class="form-control" placeholder="Buscar por razao social ou CNPJ..." value="{{ request('busca') }}">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Razao Social</th>
                <th>CNPJ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fornecedores as $fornecedor)
            <tr>
                <td>{{ $fornecedor->razao_social }}</td>
                <td>{{ $fornecedor->cnpj }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="text-center text-muted py-4">Nenhum fornecedor encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $fornecedores->withQueryString()->links() }}
@endsection
