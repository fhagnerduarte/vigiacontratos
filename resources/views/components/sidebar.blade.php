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
                <a href="{{ route('tenant.dashboard') }}"
                   class="{{ request()->routeIs('tenant.dashboard') ? 'active-page' : '' }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- GESTAO CONTRATUAL --}}
            @if (auth()->user()->hasPermission('contrato.visualizar') || auth()->user()->hasPermission('aditivo.visualizar') || auth()->user()->hasPermission('documento.visualizar'))
                <li class="sidebar-menu-group-title">Gestao Contratual</li>

                @if (auth()->user()->hasPermission('contrato.visualizar'))
                    <li class="dropdown">
                        <a href="javascript:void(0)"
                           class="{{ request()->routeIs('tenant.contratos.*') ? 'active-page' : '' }}">
                            <iconify-icon icon="solar:document-bold" class="menu-icon"></iconify-icon>
                            <span>Contratos</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li>
                                <a href="{{ route('tenant.contratos.index') }}"
                                   class="{{ request()->routeIs('tenant.contratos.index') || request()->routeIs('tenant.contratos.show') ? 'active-page' : '' }}">
                                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Todos os Contratos
                                </a>
                            </li>
                            @if (auth()->user()->hasPermission('contrato.criar'))
                                <li>
                                    <a href="{{ route('tenant.contratos.create') }}"
                                       class="{{ request()->routeIs('tenant.contratos.create') ? 'active-page' : '' }}">
                                        <i class="ri-circle-fill circle-icon text-success-main w-auto"></i> Novo Contrato
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (auth()->user()->hasPermission('aditivo.visualizar'))
                    <li>
                        <a href="{{ route('tenant.aditivos.index') }}"
                           class="{{ request()->routeIs('tenant.aditivos.*') || request()->routeIs('tenant.contratos.aditivos.*') ? 'active-page' : '' }}">
                            <iconify-icon icon="solar:add-circle-bold" class="menu-icon"></iconify-icon>
                            <span>Aditivos</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->hasPermission('documento.visualizar'))
                    <li>
                        <a href="{{ route('tenant.documentos.index') }}"
                           class="{{ request()->routeIs('tenant.documentos.*') ? 'active-page' : '' }}">
                            <iconify-icon icon="solar:folder-bold" class="menu-icon"></iconify-icon>
                            <span>Documentos</span>
                        </a>
                    </li>
                @endif
            @endif

            {{-- CADASTROS --}}
            @if (auth()->user()->hasPermission('fornecedor.visualizar') || auth()->user()->hasPermission('secretaria.visualizar') || auth()->user()->hasPermission('servidor.visualizar'))
                <li class="sidebar-menu-group-title">Cadastros</li>

                @if (auth()->user()->hasPermission('fornecedor.visualizar'))
                    <li>
                        <a href="{{ route('tenant.fornecedores.index') }}"
                           class="{{ request()->routeIs('tenant.fornecedores.*') ? 'active-page' : '' }}">
                            <iconify-icon icon="solar:buildings-bold" class="menu-icon"></iconify-icon>
                            <span>Fornecedores</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->hasPermission('secretaria.visualizar'))
                    <li>
                        <a href="{{ route('tenant.secretarias.index') }}"
                           class="{{ request()->routeIs('tenant.secretarias.*') ? 'active-page' : '' }}">
                            <iconify-icon icon="solar:case-round-bold" class="menu-icon"></iconify-icon>
                            <span>Secretarias</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->hasPermission('servidor.visualizar'))
                    <li>
                        <a href="{{ route('tenant.servidores.index') }}"
                           class="{{ request()->routeIs('tenant.servidores.*') ? 'active-page' : '' }}">
                            <iconify-icon icon="solar:user-id-bold" class="menu-icon"></iconify-icon>
                            <span>Servidores</span>
                        </a>
                    </li>
                @endif
            @endif

            {{-- MONITORAMENTO --}}
            @if (auth()->user()->hasPermission('alerta.visualizar') || auth()->user()->hasPermission('relatorio.visualizar') || auth()->user()->hasPermission('contrato.visualizar'))
                <li class="sidebar-menu-group-title">Monitoramento</li>

                @if (auth()->user()->hasPermission('alerta.visualizar'))
                    <li>
                        <a href="{{ route('tenant.alertas.index') }}"
                           class="{{ request()->routeIs('tenant.alertas.*') ? 'active-page' : '' }}">
                            <iconify-icon icon="solar:bell-bold" class="menu-icon"></iconify-icon>
                            <span>Alertas</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->hasPermission('relatorio.visualizar'))
                    <li>
                        <a href="#">
                            <iconify-icon icon="solar:chart-bold" class="menu-icon"></iconify-icon>
                            <span>Relatorios</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->hasPermission('contrato.visualizar'))
                    <li>
                        <a href="#">
                            <iconify-icon icon="solar:shield-warning-bold" class="menu-icon"></iconify-icon>
                            <span>Painel de Risco</span>
                        </a>
                    </li>
                @endif
            @endif

            {{-- ADMINISTRACAO --}}
            @if (auth()->user()->hasPermission('usuario.visualizar') || auth()->user()->hasPermission('configuracao.visualizar'))
                <li class="sidebar-menu-group-title">Administracao</li>

                @if (auth()->user()->hasPermission('usuario.visualizar'))
                    <li>
                        <a href="{{ route('tenant.users.index') }}"
                           class="{{ request()->routeIs('tenant.users.*') ? 'active-page' : '' }}">
                            <iconify-icon icon="solar:users-group-two-rounded-bold" class="menu-icon"></iconify-icon>
                            <span>Usuarios</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->hasPermission('configuracao.visualizar'))
                    <li class="dropdown">
                        <a href="javascript:void(0)"
                           class="{{ request()->routeIs('tenant.roles.*') || request()->routeIs('tenant.permissoes.*') ? 'active-page' : '' }}">
                            <iconify-icon icon="solar:settings-bold" class="menu-icon"></iconify-icon>
                            <span>Configuracoes</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li>
                                <a href="{{ route('tenant.roles.index') }}"
                                   class="{{ request()->routeIs('tenant.roles.*') || request()->routeIs('tenant.permissoes.*') ? 'active-page' : '' }}">
                                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Perfis e Permissoes
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if (auth()->user()->hasPermission('auditoria.visualizar'))
                    <li>
                        <a href="#">
                            <iconify-icon icon="solar:clipboard-list-bold" class="menu-icon"></iconify-icon>
                            <span>Auditoria</span>
                        </a>
                    </li>
                @endif
            @endif

        </ul>
    </div>
</aside>
