@extends('layout.auth')

@section('title', 'Admin SaaS - Verificação MFA')

@section('content')
<section class="auth bg-base d-flex flex-wrap">
    <div class="auth-left d-lg-block d-none">
        <div class="d-flex align-items-center flex-column h-100 justify-content-center">
            <img src="{{ asset('assets/images/auth/auth-img.png') }}" alt="">
        </div>
    </div>
    <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
        <div class="max-w-464-px mx-auto w-100">
            <a href="/" class="mb-40 max-w-290-px">
                <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}">
            </a>
            <h4 class="mb-12">Verificação em Dois Fatores</h4>
            <p class="mb-32 text-secondary-light text-lg">
                Insira o código de 6 dígitos gerado pelo seu aplicativo autenticador.
            </p>

            @if (session('error'))
                <div class="alert alert-danger radius-8 mb-16">
                    <p class="mb-0 text-sm">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger radius-8 mb-16">
                    @foreach ($errors->all() as $error)
                        <p class="mb-0 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin-saas.mfa.verify.submit') }}">
                @csrf

                <div class="icon-field mb-24">
                    <span class="icon top-50 translate-middle-y">
                        <iconify-icon icon="solar:shield-keyhole-outline"></iconify-icon>
                    </span>
                    <input type="text" name="code" class="form-control h-56-px bg-neutral-50 radius-12"
                           placeholder="000000" maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                           autocomplete="one-time-code" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12">
                    Verificar
                </button>
            </form>

            <div class="text-center mt-24">
                <a href="{{ route('admin-saas.mfa.recovery') }}" class="text-primary-600 fw-medium text-sm">
                    Usar código de recuperação
                </a>
            </div>

            <div class="text-center mt-16">
                <form method="POST" action="{{ route('admin-saas.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link text-secondary-light text-sm p-0">
                        Sair e voltar ao login
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
