@extends('portal.layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <h2 class="mb-3">Consultar Solicitacao LAI</h2>
        <p class="text-muted mb-4">
            Informe o numero do protocolo e o e-mail utilizado no cadastro para acompanhar sua solicitacao.
        </p>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('portal.lai.show', [$tenant->slug, ':protocolo']) }}" method="GET" id="form-consulta">
                    <div class="mb-3">
                        <label for="protocolo" class="form-label">Numero do Protocolo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="protocolo" name="protocolo"
                               placeholder="LAI-2026-000001" required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">E-mail do Solicitante <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="seu@email.com" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('portal.lai.create', $tenant->slug) }}" class="btn btn-outline-secondary">
                            Nova Solicitacao
                        </a>
                        <button type="submit" class="btn btn-primary">Consultar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('form-consulta').addEventListener('submit', function(e) {
    e.preventDefault();
    const protocolo = document.getElementById('protocolo').value.trim();
    const email = document.getElementById('email').value.trim();
    if (protocolo && email) {
        const baseUrl = '{{ route("portal.lai.show", [$tenant->slug, ":proto"]) }}'.replace(':proto', encodeURIComponent(protocolo));
        window.location.href = baseUrl + '?email=' + encodeURIComponent(email);
    }
});
</script>
@endpush
@endsection
