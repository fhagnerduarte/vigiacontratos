<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal de Transparencia — {{ $tenant->nome ?? 'Prefeitura Municipal' }}. Consulte contratos, fornecedores, dados abertos e faca solicitacoes via e-SIC.">
    <title>@yield('title', 'Portal de Transparencia') — {{ $tenant->nome ?? config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">
    <style>
        :root {
            --portal-primary: {{ $tenant->cor_primaria ?? '#1b55e2' }};
            --portal-secondary: {{ $tenant->cor_secundaria ?? '#0b3a9e' }};
        }
    </style>
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    @stack('styles')
</head>
<body>

    {{-- Barra de Acessibilidade --}}
    <div class="portal-accessibility">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="d-none d-sm-inline">Acessibilidade:</span>
            <div class="d-flex gap-1 align-items-center">
                <button onclick="portalFontSize('decrease')" title="Diminuir fonte">A-</button>
                <button onclick="portalFontSize('reset')" title="Fonte padrao">A</button>
                <button onclick="portalFontSize('increase')" title="Aumentar fonte">A+</button>
                <span class="d-none d-sm-inline mx-1" style="color:#444">|</span>
                <button onclick="portalContraste()" id="btnContraste" title="Alto contraste">
                    <iconify-icon icon="solar:moon-bold" width="14"></iconify-icon> Contraste
                </button>
            </div>
        </div>
    </div>

    {{-- Header Institucional --}}
    <header class="portal-header">
        <div class="container d-flex align-items-center gap-3">
            @if($tenant->logo_url ?? false)
                <div class="portal-logo">
                    <img src="{{ $tenant->logo_url }}" alt="Brasao {{ $tenant->nome }}">
                </div>
            @else
                <div class="portal-logo-placeholder">
                    <iconify-icon icon="solar:buildings-3-bold" width="28" style="color:#fff"></iconify-icon>
                </div>
            @endif
            <div>
                <h1>{{ $tenant->nome ?? 'Prefeitura Municipal' }}</h1>
                <p>Portal de Transparencia — Lei de Acesso a Informacao (Lei 12.527/2011)</p>
            </div>
        </div>
    </header>

    {{-- Navegacao Principal --}}
    <nav class="portal-nav">
        <div class="container d-flex align-items-center">
            {{-- Hamburger Mobile --}}
            <button class="portal-menu-toggle d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#portalMenu" aria-label="Abrir menu">
                <iconify-icon icon="solar:hamburger-menu-bold" width="24"></iconify-icon>
            </button>

            {{-- Links Desktop --}}
            <div class="portal-nav-links d-none d-lg-flex">
                <a href="{{ route('portal.index', $tenant->slug) }}" class="{{ request()->routeIs('portal.index') ? 'active' : '' }}">
                    <iconify-icon icon="solar:home-2-bold" width="18"></iconify-icon> Inicio
                </a>
                <a href="{{ route('portal.contratos', $tenant->slug) }}" class="{{ request()->routeIs('portal.contratos*') ? 'active' : '' }}">
                    <iconify-icon icon="solar:document-bold" width="18"></iconify-icon> Contratos
                </a>
                <a href="{{ route('portal.fornecedores', $tenant->slug) }}" class="{{ request()->routeIs('portal.fornecedores') ? 'active' : '' }}">
                    <iconify-icon icon="solar:users-group-rounded-bold" width="18"></iconify-icon> Fornecedores
                </a>
                <a href="{{ route('portal.dados-abertos', $tenant->slug) }}" class="{{ request()->routeIs('portal.dados-abertos') ? 'active' : '' }}">
                    <iconify-icon icon="solar:database-bold" width="18"></iconify-icon> Dados Abertos
                </a>
                <a href="{{ route('portal.lai.create', $tenant->slug) }}" class="{{ request()->routeIs('portal.lai.*') ? 'active' : '' }}">
                    <iconify-icon icon="solar:chat-round-dots-bold" width="18"></iconify-icon> e-SIC
                </a>
            </div>

            {{-- Offcanvas Mobile --}}
            <div class="offcanvas offcanvas-start" tabindex="-1" id="portalMenu" aria-labelledby="portalMenuLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="portalMenuLabel">{{ $tenant->nome ?? 'Menu' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
                </div>
                <div class="offcanvas-body">
                    <nav class="d-flex flex-column gap-2">
                        <a href="{{ route('portal.index', $tenant->slug) }}" class="d-flex align-items-center gap-2 text-decoration-none py-2 px-3 rounded {{ request()->routeIs('portal.index') ? 'bg-primary text-white' : 'text-dark' }}">
                            <iconify-icon icon="solar:home-2-bold" width="20"></iconify-icon> Inicio
                        </a>
                        <a href="{{ route('portal.contratos', $tenant->slug) }}" class="d-flex align-items-center gap-2 text-decoration-none py-2 px-3 rounded {{ request()->routeIs('portal.contratos*') ? 'bg-primary text-white' : 'text-dark' }}">
                            <iconify-icon icon="solar:document-bold" width="20"></iconify-icon> Contratos
                        </a>
                        <a href="{{ route('portal.fornecedores', $tenant->slug) }}" class="d-flex align-items-center gap-2 text-decoration-none py-2 px-3 rounded {{ request()->routeIs('portal.fornecedores') ? 'bg-primary text-white' : 'text-dark' }}">
                            <iconify-icon icon="solar:users-group-rounded-bold" width="20"></iconify-icon> Fornecedores
                        </a>
                        <a href="{{ route('portal.dados-abertos', $tenant->slug) }}" class="d-flex align-items-center gap-2 text-decoration-none py-2 px-3 rounded {{ request()->routeIs('portal.dados-abertos') ? 'bg-primary text-white' : 'text-dark' }}">
                            <iconify-icon icon="solar:database-bold" width="20"></iconify-icon> Dados Abertos
                        </a>
                        <a href="{{ route('portal.lai.create', $tenant->slug) }}" class="d-flex align-items-center gap-2 text-decoration-none py-2 px-3 rounded {{ request()->routeIs('portal.lai.*') ? 'bg-primary text-white' : 'text-dark' }}">
                            <iconify-icon icon="solar:chat-round-dots-bold" width="20"></iconify-icon> e-SIC
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </nav>

    {{-- Breadcrumb --}}
    @hasSection('breadcrumb')
    <div class="portal-breadcrumb">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
    </div>
    @endif

    {{-- Conteudo --}}
    <main class="container py-4">
        @yield('content')
    </main>

    {{-- Footer 3 Colunas --}}
    <footer class="portal-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5>{{ $tenant->nome ?? 'Prefeitura Municipal' }}</h5>
                    <p>Portal de Transparencia</p>
                    @if($tenant->cnpj ?? false)
                        <p>CNPJ: {{ $tenant->cnpj }}</p>
                    @endif
                    @if($tenant->gestor_nome ?? false)
                        <p>{{ $tenant->gestor_nome }}</p>
                    @endif
                </div>
                <div class="col-md-4">
                    <h5>Contato</h5>
                    @if($tenant->endereco ?? false)
                        <p><iconify-icon icon="solar:map-point-bold" width="14"></iconify-icon> {{ $tenant->endereco }}</p>
                    @endif
                    @if($tenant->telefone ?? false)
                        <p><iconify-icon icon="solar:phone-bold" width="14"></iconify-icon> {{ $tenant->telefone }}</p>
                    @endif
                    @if($tenant->email_contato ?? false)
                        <p><iconify-icon icon="solar:letter-bold" width="14"></iconify-icon> {{ $tenant->email_contato }}</p>
                    @endif
                    <p><iconify-icon icon="solar:clock-circle-bold" width="14"></iconify-icon> {{ $tenant->horario_atendimento ?? 'Seg-Sex, 8h as 14h' }}</p>
                </div>
                <div class="col-md-4">
                    <h5>Links Rapidos</h5>
                    <ul>
                        <li><a href="{{ route('portal.contratos', $tenant->slug) }}"><iconify-icon icon="solar:document-bold" width="14"></iconify-icon> Contratos</a></li>
                        <li><a href="{{ route('portal.fornecedores', $tenant->slug) }}"><iconify-icon icon="solar:users-group-rounded-bold" width="14"></iconify-icon> Fornecedores</a></li>
                        <li><a href="{{ route('portal.lai.create', $tenant->slug) }}"><iconify-icon icon="solar:chat-round-dots-bold" width="14"></iconify-icon> e-SIC</a></li>
                        <li><a href="{{ route('portal.dados-abertos', $tenant->slug) }}"><iconify-icon icon="solar:database-bold" width="14"></iconify-icon> Dados Abertos</a></li>
                    </ul>
                </div>
            </div>
            <div class="portal-footer-bottom text-center">
                &copy; {{ date('Y') }} {{ $tenant->nome ?? 'Prefeitura Municipal' }} — Powered by VigiaContratos
            </div>
        </div>
    </footer>

    {{-- LGPD Banner --}}
    <div class="portal-lgpd" id="lgpdBanner">
        <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
            <p class="mb-0">Este portal utiliza cookies conforme a LGPD (Lei 13.709/2018). Ao continuar navegando, voce concorda com nossa Politica de Privacidade.</p>
            <button onclick="aceitarLgpd()" class="btn btn-sm btn-outline-light flex-shrink-0">Aceitar</button>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    <script>
        // Acessibilidade: Tamanho de Fonte
        function portalFontSize(action) {
            var sizes = [14, 16, 18, 20];
            var current = parseInt(localStorage.getItem('portal_font_size')) || 16;
            var idx = sizes.indexOf(current);
            if (idx === -1) idx = 1;
            if (action === 'increase' && idx < sizes.length - 1) idx++;
            else if (action === 'decrease' && idx > 0) idx--;
            else if (action === 'reset') idx = 1;
            var newSize = sizes[idx];
            localStorage.setItem('portal_font_size', newSize);
            document.documentElement.style.setProperty('--portal-font-size', newSize + 'px');
        }

        // Acessibilidade: Alto Contraste
        function portalContraste() {
            var isActive = document.body.classList.toggle('high-contrast');
            var btn = document.getElementById('btnContraste');
            if (isActive) {
                localStorage.setItem('portal_contrast', '1');
                if (btn) btn.classList.add('active');
            } else {
                localStorage.removeItem('portal_contrast');
                if (btn) btn.classList.remove('active');
            }
        }

        // LGPD Banner
        function aceitarLgpd() {
            localStorage.setItem('lgpd_aceito', '1');
            document.getElementById('lgpdBanner').classList.add('d-none');
        }

        // Inicializacao
        (function() {
            if (localStorage.getItem('lgpd_aceito')) {
                document.getElementById('lgpdBanner').classList.add('d-none');
            }
            if (localStorage.getItem('portal_contrast')) {
                document.body.classList.add('high-contrast');
                var btn = document.getElementById('btnContraste');
                if (btn) btn.classList.add('active');
            }
            var fs = localStorage.getItem('portal_font_size');
            if (fs) {
                document.documentElement.style.setProperty('--portal-font-size', fs + 'px');
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
