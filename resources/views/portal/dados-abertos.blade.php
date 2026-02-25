@extends('portal.layout')

@section('content')
<h2 class="mb-4">Dados Abertos</h2>

<p class="text-muted mb-4">
    Disponibilizamos dados contratuais em formatos abertos para download, conforme
    a Lei de Acesso a Informacao (Lei 12.527/2011).
</p>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <h5 class="mb-2">JSON</h5>
                <p class="text-muted small">Formato padrao para integracao com sistemas</p>
                <a href="{{ route('portal.dados-abertos', [$tenant->slug, 'formato' => 'json']) }}" class="btn btn-outline-primary">Download JSON</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <h5 class="mb-2">CSV</h5>
                <p class="text-muted small">Formato compativel com planilhas</p>
                <a href="{{ route('portal.dados-abertos', [$tenant->slug, 'formato' => 'csv']) }}" class="btn btn-outline-success">Download CSV</a>
            </div>
        </div>
    </div>
</div>
@endsection
