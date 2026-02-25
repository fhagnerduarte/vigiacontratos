/**
 * Toast Auto-Init — Inicializa todos os Bootstrap Toasts no DOMContentLoaded.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toast').forEach(function (el) {
        var toast = new bootstrap.Toast(el);
        toast.show();
    });
});
