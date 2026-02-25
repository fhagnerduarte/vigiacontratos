@php
    $flashes = [];
    foreach (['success', 'warning', 'error', 'info'] as $type) {
        if (session($type)) {
            $flashes[] = ['type' => $type, 'message' => session($type)];
        }
    }
    // Suporte para 'status' (usado em auth)
    if (session('status')) {
        $flashes[] = ['type' => 'success', 'message' => session('status')];
    }

    $iconMap = [
        'success' => 'solar:check-circle-bold',
        'warning' => 'solar:danger-triangle-bold',
        'error'   => 'solar:close-circle-bold',
        'info'    => 'solar:info-circle-bold',
    ];

    $bgMap = [
        'success' => 'success',
        'warning' => 'warning',
        'error'   => 'danger',
        'info'    => 'info',
    ];
@endphp

@if (count($flashes) > 0)
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
    @foreach ($flashes as $flash)
    <div class="toast align-items-center border-0 border-start border-4 border-{{ $bgMap[$flash['type']] }} bg-white shadow-lg"
         role="alert" aria-live="assertive" aria-atomic="true"
         data-bs-delay="5000" data-bs-autohide="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <iconify-icon icon="{{ $iconMap[$flash['type']] }}" class="text-{{ $bgMap[$flash['type']] }} text-xl flex-shrink-0"></iconify-icon>
                <span class="text-sm fw-medium">{{ $flash['message'] }}</span>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
    @endforeach
</div>
@endif
