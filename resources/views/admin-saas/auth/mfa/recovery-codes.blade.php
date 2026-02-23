@extends('layout.auth')

@section('title', 'Admin SaaS - Códigos de Recuperação')

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

            <div class="text-center mb-24">
                <div class="w-64-px h-64-px bg-success-100 rounded-circle d-flex justify-content-center align-items-center mx-auto mb-16">
                    <iconify-icon icon="solar:shield-check-bold" class="text-success-600 text-xxl"></iconify-icon>
                </div>
                <h4 class="mb-8">MFA Ativado com Sucesso!</h4>
                <p class="text-secondary-light text-lg">
                    Sua conta de administrador agora está protegida com autenticação em dois fatores.
                </p>
            </div>

            <div class="alert alert-warning radius-8 mb-24">
                <div class="d-flex align-items-start gap-12">
                    <iconify-icon icon="solar:danger-triangle-bold" class="text-warning-600 text-xl flex-shrink-0 mt-4"></iconify-icon>
                    <div>
                        <p class="fw-semibold mb-4 text-sm">Salve seus códigos de recuperação!</p>
                        <p class="mb-0 text-sm text-secondary-light">
                            Esses códigos são a única forma de acessar sua conta caso perca acesso ao aplicativo autenticador.
                            Cada código pode ser usado apenas uma vez. <strong>Eles não serão exibidos novamente.</strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-neutral-50 p-20 radius-12 mb-24">
                <div class="row g-12" id="recovery-codes">
                    @foreach ($recoveryCodes as $code)
                        <div class="col-6">
                            <code class="d-block text-center py-8 bg-white radius-8 fw-semibold text-primary-600 border">{{ $code }}</code>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex gap-12 mb-24">
                <button type="button" class="btn btn-outline-primary-600 text-sm btn-sm px-12 py-12 w-100 radius-12" onclick="copyCodes()">
                    <iconify-icon icon="solar:copy-outline" class="me-4"></iconify-icon> Copiar Códigos
                </button>
                <button type="button" class="btn btn-outline-primary-600 text-sm btn-sm px-12 py-12 w-100 radius-12" onclick="downloadCodes()">
                    <iconify-icon icon="solar:download-outline" class="me-4"></iconify-icon> Baixar Códigos
                </button>
            </div>

            <a href="{{ route('admin-saas.dashboard') }}" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12">
                Continuar para o Painel Admin
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    var codes = @json($recoveryCodes);

    function copyCodes() {
        var text = codes.join('\n');
        navigator.clipboard.writeText(text).then(function() {
            var btn = event.target.closest('button');
            var originalText = btn.innerHTML;
            btn.innerHTML = '<iconify-icon icon="solar:check-circle-outline" class="me-4"></iconify-icon> Copiado!';
            setTimeout(function() { btn.innerHTML = originalText; }, 2000);
        });
    }

    function downloadCodes() {
        var text = '{{ config("app.name") }} Admin - Códigos de Recuperação MFA\n';
        text += '='.repeat(50) + '\n\n';
        text += 'Gerado em: {{ now()->format("d/m/Y H:i") }}\n\n';
        text += codes.join('\n');
        text += '\n\nCADA CÓDIGO SÓ PODE SER USADO UMA VEZ.';

        var blob = new Blob([text], { type: 'text/plain' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'vigiacontratos-admin-recovery-codes.txt';
        a.click();
        URL.revokeObjectURL(a.href);
    }
</script>
@endpush
