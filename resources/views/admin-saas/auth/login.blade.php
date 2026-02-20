@extends('layout.auth')

@section('title', 'Admin SaaS - Login')

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
            <h4 class="mb-12">Admin SaaS</h4>
            <p class="mb-32 text-secondary-light text-lg">Acesso restrito ao painel administrativo.</p>

            @if ($errors->any())
                <div class="alert alert-danger radius-8 mb-16">
                    @foreach ($errors->all() as $error)
                        <p class="mb-0 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin-saas.login.submit') }}">
                @csrf

                <div class="icon-field mb-16">
                    <span class="icon top-50 translate-middle-y">
                        <iconify-icon icon="mage:email"></iconify-icon>
                    </span>
                    <input type="email" name="email" class="form-control h-56-px bg-neutral-50 radius-12"
                           placeholder="E-mail" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="position-relative mb-20">
                    <div class="icon-field">
                        <span class="icon top-50 translate-middle-y">
                            <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                        </span>
                        <input type="password" name="password" id="admin-password"
                               class="form-control h-56-px bg-neutral-50 radius-12"
                               placeholder="Senha" required>
                    </div>
                    <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light"
                          data-toggle="#admin-password"></span>
                </div>

                <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12 mt-32">
                    Entrar
                </button>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.toggle-password').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            var input = document.querySelector(this.getAttribute('data-toggle'));
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('ri-eye-line');
                this.classList.add('ri-eye-off-line');
            } else {
                input.type = 'password';
                this.classList.remove('ri-eye-off-line');
                this.classList.add('ri-eye-line');
            }
        });
    });
</script>
@endpush
