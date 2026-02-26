@extends('portal.layout')

@section('title', 'Nova Solicitacao e-SIC')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('portal.index', $tenant->slug) }}">Inicio</a></li>
    <li class="breadcrumb-item">e-SIC</li>
    <li class="breadcrumb-item active" aria-current="page">Nova Solicitacao</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h2 class="portal-section-title">Solicitacao de Informacao (e-SIC)</h2>

        {{-- Info Card --}}
        <div class="portal-info-card">
            <iconify-icon icon="solar:info-circle-bold" width="22"></iconify-icon>
            <div>
                <p>Conforme a Lei de Acesso a Informacao (Lei 12.527/2011), todo cidadao tem direito de solicitar informacoes publicas. O prazo legal para resposta e de <strong>20 dias uteis</strong>, prorrogavel por mais 10 dias mediante justificativa.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card portal-card">
            <div class="card-header" style="background: var(--portal-primary); color: #fff;">
                <h5 class="mb-0"><iconify-icon icon="solar:pen-new-round-bold" width="18"></iconify-icon> Nova Solicitacao</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('portal.lai.store', $tenant->slug) }}" method="POST">
                    @csrf

                    <h6 class="text-muted mb-3"><iconify-icon icon="solar:user-bold" width="16"></iconify-icon> Dados do Solicitante</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nome_solicitante" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nome_solicitante') is-invalid @enderror"
                                   id="nome_solicitante" name="nome_solicitante" value="{{ old('nome_solicitante') }}" required>
                            @error('nome_solicitante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email_solicitante" class="form-label">E-mail <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email_solicitante') is-invalid @enderror"
                                   id="email_solicitante" name="email_solicitante" value="{{ old('email_solicitante') }}" required>
                            @error('email_solicitante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="cpf_solicitante" class="form-label">CPF <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('cpf_solicitante') is-invalid @enderror"
                                   id="cpf_solicitante" name="cpf_solicitante" value="{{ old('cpf_solicitante') }}"
                                   placeholder="000.000.000-00" required>
                            @error('cpf_solicitante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="telefone_solicitante" class="form-label">Telefone</label>
                            <input type="text" class="form-control @error('telefone_solicitante') is-invalid @enderror"
                                   id="telefone_solicitante" name="telefone_solicitante" value="{{ old('telefone_solicitante') }}"
                                   placeholder="(00) 00000-0000">
                            @error('telefone_solicitante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3"><iconify-icon icon="solar:document-text-bold" width="16"></iconify-icon> Informacao Solicitada</h6>

                    <div class="mb-3">
                        <label for="assunto" class="form-label">Assunto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('assunto') is-invalid @enderror"
                               id="assunto" name="assunto" value="{{ old('assunto') }}"
                               placeholder="Resuma o assunto da solicitacao" required>
                        @error('assunto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="descricao" class="form-label">Descricao Detalhada <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('descricao') is-invalid @enderror"
                                  id="descricao" name="descricao" rows="5"
                                  placeholder="Descreva com detalhes a informacao que deseja obter (minimo 20 caracteres)" required>{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('portal.lai.consultar', $tenant->slug) }}" class="btn btn-outline-secondary">
                            <iconify-icon icon="solar:magnifer-bold" width="16"></iconify-icon> Consultar Existente
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <iconify-icon icon="solar:plain-bold" width="16"></iconify-icon> Enviar Solicitacao
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mascara CPF
    var cpf = document.getElementById('cpf_solicitante');
    if (cpf) {
        cpf.addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '').substring(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.value = v;
        });
    }
    // Mascara Telefone
    var tel = document.getElementById('telefone_solicitante');
    if (tel) {
        tel.addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '').substring(0, 11);
            if (v.length > 10) {
                v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (v.length > 6) {
                v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            this.value = v;
        });
    }
});
</script>
@endpush
@endsection
