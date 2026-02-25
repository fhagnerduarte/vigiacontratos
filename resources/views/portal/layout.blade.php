<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal de Transparencia — {{ $tenant->nome ?? config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <style>
        .portal-header { background: linear-gradient(135deg, #1b55e2 0%, #0b3a9e 100%); color: #fff; padding: 2rem 0; }
        .portal-header h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
        .portal-header p { opacity: 0.8; margin-bottom: 0; font-size: 0.9rem; }
        .portal-nav { background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 0.5rem 0; }
        .portal-nav a { color: #333; text-decoration: none; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.9rem; }
        .portal-nav a:hover, .portal-nav a.active { background: #1b55e2; color: #fff; }
        .portal-footer { background: #f8f9fa; border-top: 1px solid #dee2e6; padding: 1.5rem 0; margin-top: 3rem; font-size: 0.85rem; color: #666; }
    </style>
    @stack('styles')
</head>
<body>
    <header class="portal-header">
        <div class="container">
            <h1>{{ $tenant->nome ?? 'Prefeitura Municipal' }}</h1>
            <p>Portal de Transparencia — Lei de Acesso a Informacao (Lei 12.527/2011)</p>
        </div>
    </header>

    <nav class="portal-nav">
        <div class="container d-flex gap-2 flex-wrap">
            <a href="{{ route('portal.index', $tenant->slug) }}" class="{{ request()->routeIs('portal.index') ? 'active' : '' }}">Inicio</a>
            <a href="{{ route('portal.contratos', $tenant->slug) }}" class="{{ request()->routeIs('portal.contratos*') ? 'active' : '' }}">Contratos</a>
            <a href="{{ route('portal.fornecedores', $tenant->slug) }}" class="{{ request()->routeIs('portal.fornecedores') ? 'active' : '' }}">Fornecedores</a>
            <a href="{{ route('portal.dados-abertos', $tenant->slug) }}" class="{{ request()->routeIs('portal.dados-abertos') ? 'active' : '' }}">Dados Abertos</a>
            <a href="{{ route('portal.lai.create', $tenant->slug) }}" class="{{ request()->routeIs('portal.lai.*') ? 'active' : '' }}">e-SIC</a>
        </div>
    </nav>

    <main class="container py-4">
        @yield('content')
    </main>

    <footer class="portal-footer">
        <div class="container text-center">
            <p>Portal de Transparencia — {{ $tenant->nome ?? 'Prefeitura Municipal' }}</p>
            <p>Informacoes disponibilizadas conforme Lei de Acesso a Informacao (Lei 12.527/2011)</p>
        </div>
    </footer>

    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
