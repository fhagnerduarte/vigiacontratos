@extends('layout.auth')

@section('title', 'Esqueci a Senha')

@section('content')
<section class="auth forgot-password-page bg-base d-flex flex-wrap">
    <div class="auth-left d-lg-block d-none">
        <div class="d-flex align-items-center flex-column h-100 justify-content-center">
            <img src="{{ asset('assets/images/auth/forgot-pass-img.png') }}" alt="">
        </div>
    </div>
    <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
        <div class="max-w-464-px mx-auto w-100">
            <div>
                <a href="/" class="mb-40 max-w-290-px d-block">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}">
                </a>
                <h4 class="mb-12">Esqueceu a senha?</h4>
                <p class="mb-32 text-secondary-light text-lg">Informe o e-mail associado à sua conta e enviaremos um link para redefinir sua senha.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger radius-8 mb-16">
                    @foreach ($errors->all() as $error)
                        <p class="mb-0 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('tenant.password.email') }}">
                @csrf

                <div class="icon-field">
                    <span class="icon top-50 translate-middle-y">
                        <iconify-icon icon="mage:email"></iconify-icon>
                    </span>
                    <input type="email" name="email" class="form-control h-56-px bg-neutral-50 radius-12"
                           placeholder="Digite seu e-mail" value="{{ old('email') }}" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12 mt-32">
                    Enviar link de redefinição
                </button>

                <div class="text-center mt-24">
                    <a href="{{ route('tenant.login') }}" class="text-primary-600 fw-bold">Voltar ao login</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
