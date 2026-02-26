@extends('portal.layout')

@section('title', 'Consultar Solicitação')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('portal.index', $tenant->slug) }}">Início</a></li>
    <li class="breadcrumb-item">e-SIC</li>
    <li class="breadcrumb-item active" aria-current="page">Consultar</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <h2 class="portal-section-title">Consultar Solicitação LAI</h2>

        <p class="text-muted mb-4">
            Informe o número do protocolo e o e-mail utilizado no cadastro para acompanhar sua solicitação.
        </p>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card portal-card">
            <div class="card-body text-center py-4">
                <iconify-icon icon="solar:magnifer-bold" width="40" style="color: var(--portal-primary); opacity: 0.5;" class="mb-3"></iconify-icon>

                <form action="{{ route('portal.lai.show', [$tenant->slug, ':protocolo']) }}" method="GET" id="form-consulta">
                    <div class="mb-3 text-start">
                        <label for="protocolo" class="form-label">Número do Protocolo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="protocolo" name="protocolo"
                               placeholder="LAI-2026-000001" required>
                    </div>

                    <div class="mb-4 text-start">
                        <label for="email" class="form-label">E-mail do Solicitante <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="seu@email.com" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('portal.lai.create', $tenant->slug) }}" class="btn btn-outline-secondary">
                            <iconify-icon icon="solar:pen-new-round-bold" width="16"></iconify-icon> Nova Solicitação
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <iconify-icon icon="solar:magnifer-bold" width="16"></iconify-icon> Consultar
                        </button>
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
    var protocolo = document.getElementById('protocolo').value.trim();
    var email = document.getElementById('email').value.trim();
    if (protocolo && email) {
        var baseUrl = '{{ route("portal.lai.show", [$tenant->slug, ":proto"]) }}'.replace(':proto', encodeURIComponent(protocolo));
        window.location.href = baseUrl + '?email=' + encodeURIComponent(email);
    }
});
</script>
@endpush
@endsection
