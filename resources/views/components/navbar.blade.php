<div class="navbar-header">
    <div class="row align-items-center justify-content-between">
        <div class="col-auto">
            <div class="d-flex flex-wrap align-items-center gap-4">
                <button type="button" class="sidebar-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon text-2xl non-active"></iconify-icon>
                    <iconify-icon icon="iconoir:arrow-right" class="icon text-2xl active"></iconify-icon>
                </button>
                <button type="button" class="sidebar-mobile-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon"></iconify-icon>
                </button>
                <form class="navbar-search">
                    <input type="text" name="search" placeholder="Buscar...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex flex-wrap align-items-center gap-3">

                {{-- Theme toggle --}}
                <button type="button" data-theme-toggle class="w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center"></button>

                {{-- Notification dropdown --}}
                @auth
                @php
                    $unreadNotifications = auth()->user()->unreadNotifications->take(10);
                    $unreadCount = auth()->user()->unreadNotifications->count();
                @endphp
                <div class="dropdown">
                    <button class="{{ $unreadCount > 0 ? 'has-indicator' : '' }} w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center" type="button" data-bs-toggle="dropdown">
                        <iconify-icon icon="iconoir:bell" class="text-primary-light text-xl"></iconify-icon>
                    </button>
                    <div class="dropdown-menu to-top dropdown-menu-lg p-0">
                        <div class="m-16 py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <h6 class="text-lg text-primary-light fw-semibold mb-0">Notificações</h6>
                            </div>
                            <span class="text-primary-600 fw-semibold text-lg w-40-px h-40-px rounded-circle bg-base d-flex justify-content-center align-items-center">{{ $unreadCount }}</span>
                        </div>
                        <div class="max-h-400-px overflow-y-auto scroll-sm pe-4">
                            @forelse ($unreadNotifications as $notification)
                            <a href="{{ isset($notification->data['alerta_id']) ? route('tenant.alertas.show', $notification->data['alerta_id']) : '#' }}"
                               class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between text-decoration-none"
                               onclick="fetch('{{ route('tenant.notificacoes.marcar-lida', $notification->id) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}})">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="w-44-px h-44-px bg-{{ $notification->data['prioridade_cor'] ?? 'primary' }}-50 radius-8 d-flex justify-content-center align-items-center flex-shrink-0">
                                        <iconify-icon icon="{{ $notification->data['prioridade_icone'] ?? 'solar:bell-bold' }}" class="text-{{ $notification->data['prioridade_cor'] ?? 'primary' }}-600 text-xl"></iconify-icon>
                                    </div>
                                    <div>
                                        <span class="text-sm fw-semibold text-primary-light d-block">
                                            Contrato {{ $notification->data['contrato_numero'] ?? '' }}
                                        </span>
                                        <span class="text-xs text-secondary-light">
                                            {{ \Illuminate\Support\Str::limit($notification->data['mensagem'] ?? '', 80) }}
                                        </span>
                                    </div>
                                </div>
                                <span class="text-xs text-secondary-light flex-shrink-0">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </a>
                            @empty
                            <div class="px-24 py-12 text-center">
                                <p class="text-sm text-secondary-light mb-0">Nenhuma notificação no momento.</p>
                            </div>
                            @endforelse
                        </div>

                        @if ($unreadCount > 0)
                        <div class="text-center py-12 border-top">
                            <a href="{{ route('tenant.alertas.index') }}" class="text-primary-600 fw-semibold text-sm">
                                Ver todos os alertas
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endauth

                {{-- Profile dropdown --}}
                <div class="dropdown">
                    <button class="d-flex justify-content-center align-items-center rounded-circle" type="button" data-bs-toggle="dropdown">
                        <img src="{{ asset('assets/images/user.png') }}" alt="image" class="w-40-px h-40-px object-fit-cover rounded-circle">
                    </button>
                    <div class="dropdown-menu to-top dropdown-menu-sm">
                        <div class="py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <h6 class="text-lg text-primary-light fw-semibold mb-2">{{ auth()->user()->nome ?? '' }}</h6>
                                <span class="text-secondary-light fw-medium text-sm">{{ auth()->user()->email ?? '' }}</span>
                            </div>
                            <button type="button" class="hover-text-danger" data-bs-dismiss="dropdown">
                                <iconify-icon icon="radix-icons:cross-1" class="icon text-xl"></iconify-icon>
                            </button>
                        </div>
                        <ul class="to-top-list">
                            <li>
                                <form method="POST" action="{{ route('tenant.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-danger d-flex align-items-center gap-3">
                                        <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon> Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
