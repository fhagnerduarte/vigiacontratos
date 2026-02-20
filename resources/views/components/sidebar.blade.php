<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('tenant.dashboard') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}" class="light-logo">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="{{ config('app.name') }}" class="dark-logo">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="{{ config('app.name') }}" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">

            {{-- Dashboard --}}
            <li>
                <a href="{{ route('tenant.dashboard') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- GESTÃO CONTRATUAL --}}
            <li class="sidebar-menu-group-title">Gestão Contratual</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:document-bold" class="menu-icon"></iconify-icon>
                    <span>Contratos</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="#"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Todos os Contratos</a>
                    </li>
                    <li>
                        <a href="#"><i class="ri-circle-fill circle-icon text-success-main w-auto"></i> Novo Contrato</a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="#">
                    <iconify-icon icon="solar:add-circle-bold" class="menu-icon"></iconify-icon>
                    <span>Aditivos</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <iconify-icon icon="solar:folder-bold" class="menu-icon"></iconify-icon>
                    <span>Documentos</span>
                </a>
            </li>

            {{-- CADASTROS --}}
            <li class="sidebar-menu-group-title">Cadastros</li>

            <li>
                <a href="#">
                    <iconify-icon icon="solar:buildings-bold" class="menu-icon"></iconify-icon>
                    <span>Fornecedores</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <iconify-icon icon="solar:case-round-bold" class="menu-icon"></iconify-icon>
                    <span>Secretarias</span>
                </a>
            </li>

            {{-- MONITORAMENTO --}}
            <li class="sidebar-menu-group-title">Monitoramento</li>

            <li>
                <a href="#">
                    <iconify-icon icon="solar:bell-bold" class="menu-icon"></iconify-icon>
                    <span>Alertas</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <iconify-icon icon="solar:chart-bold" class="menu-icon"></iconify-icon>
                    <span>Relatórios</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <iconify-icon icon="solar:shield-warning-bold" class="menu-icon"></iconify-icon>
                    <span>Painel de Risco</span>
                </a>
            </li>

            {{-- ADMINISTRAÇÃO --}}
            <li class="sidebar-menu-group-title">Administração</li>

            <li>
                <a href="#">
                    <iconify-icon icon="solar:users-group-two-rounded-bold" class="menu-icon"></iconify-icon>
                    <span>Usuários</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <iconify-icon icon="solar:settings-bold" class="menu-icon"></iconify-icon>
                    <span>Configurações</span>
                </a>
            </li>

        </ul>
    </div>
</aside>
