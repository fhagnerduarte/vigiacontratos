@extends('layout.auth')

@section('title', 'Admin SaaS - Configurar MFA')

@section('content')
<section class="auth bg-base d-flex flex-wrap">
    <div class="auth-left d-lg-block d-none">
        <div class="d-flex align-items-center flex-column h-100 justify-content-center">
            <img src="{{ asset('assets/images/auth/auth-img.png') }}" alt="">
        </div>
    </div>
    <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
        <div class="max-w-464-px mx-auto w-100">
            <a href="/" class="mb-32 max-w-290-px">
                <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}">
            </a>
            <h4 class="mb-12">Configurar Autenticação em Dois Fatores</h4>
            <p class="mb-24 text-secondary-light text-lg">
                A autenticação em dois fatores é obrigatória para administradores. Escaneie o QR code com seu aplicativo autenticador.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger radius-8 mb-16">
                    @foreach ($errors->all() as $error)
                        <p class="mb-0 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="text-center mb-24">
                <div class="bg-white p-16 radius-12 d-inline-block shadow-sm">
                    <img src="{{ $qrCodeDataUri }}" alt="QR Code MFA" style="width: 200px; height: 200px;">
                </div>
            </div>

            <div class="mb-24">
                <p class="text-sm text-secondary-light mb-8">Não consegue escanear? Use a chave manual:</p>
                <div class="bg-neutral-50 p-12 radius-8 d-flex align-items-center justify-content-between">
                    <code class="text-primary-600 fw-semibold letter-spacing-2" id="mfa-secret">{{ $secret }}</code>
                    <button type="button" class="btn btn-sm btn-outline-primary-600 radius-8" onclick="copySecret()">
                        <iconify-icon icon="solar:copy-outline" class="me-4"></iconify-icon> Copiar
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('admin-saas.mfa.enable') }}">
                @csrf

                <label class="form-label fw-semibold text-primary-light text-sm mb-8">
                    Insira o código de 6 dígitos do aplicativo
                </label>
                <div class="icon-field mb-16">
                    <span class="icon top-50 translate-middle-y">
                        <iconify-icon icon="solar:shield-keyhole-outline"></iconify-icon>
                    </span>
                    <input type="text" name="code" class="form-control h-56-px bg-neutral-50 radius-12"
                           placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                           autocomplete="one-time-code" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12">
                    Ativar Autenticação em Dois Fatores
                </button>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function copySecret() {
        var secret = document.getElementById('mfa-secret').textContent;
        navigator.clipboard.writeText(secret).then(function() {
            var btn = event.target.closest('button');
            var originalText = btn.innerHTML;
            btn.innerHTML = '<iconify-icon icon="solar:check-circle-outline" class="me-4"></iconify-icon> Copiado!';
            setTimeout(function() { btn.innerHTML = originalText; }, 2000);
        });
    }
</script>
@endpush
