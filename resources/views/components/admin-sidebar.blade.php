<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('admin-saas.dashboard') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }} Admin" class="light-logo">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="{{ config('app.name') }} Admin" class="dark-logo">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="{{ config('app.name') }} Admin" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">

            <li class="sidebar-menu-group-title">Admin SaaS</li>

            <li>
                <a href="{{ route('admin-saas.dashboard') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin-saas.tenants.index') }}">
                    <iconify-icon icon="solar:buildings-bold" class="menu-icon"></iconify-icon>
                    <span>Tenants</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin-saas.tenants.create') }}">
                    <iconify-icon icon="ic:baseline-plus" class="menu-icon"></iconify-icon>
                    <span>Novo Tenant</span>
                </a>
            </li>

        </ul>
    </div>
</aside>
