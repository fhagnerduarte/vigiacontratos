/**
 * Dashboard Enhancements — Counters animados, async refresh, tooltips.
 */
(function () {
    'use strict';

    // ============================================
    // 1. Counters animados (count-up)
    // ============================================
    function animateCounter(el) {
        var target = parseFloat(el.dataset.countup);
        var prefix = el.dataset.countupPrefix || '';
        var decimals = parseInt(el.dataset.countupDecimals) || 0;
        var duration = 1500; // ms
        var start = 0;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            // easeOutExpo
            var eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            var current = start + (target - start) * eased;

            if (decimals > 0) {
                el.textContent = prefix + current.toLocaleString('pt-BR', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            } else {
                el.textContent = prefix + Math.round(current).toLocaleString('pt-BR');
            }

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }

        requestAnimationFrame(step);
    }

    // Observer para iniciar animacao quando visivel
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('[data-countup]').forEach(function (el) {
            observer.observe(el);
        });
    } else {
        // Fallback sem observer
        document.querySelectorAll('[data-countup]').forEach(animateCounter);
    }

    // ============================================
    // 2. Async Dashboard Refresh
    // ============================================
    var formAtualizar = document.getElementById('formAtualizarDashboard');
    if (formAtualizar) {
        formAtualizar.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btnAtualizarDashboard');
            if (!btn || btn.disabled) return;

            // Ativar loading
            btn.disabled = true;
            btn.classList.add('loading');
            var spinner = btn.querySelector('.spinner-border');
            var icon = btn.querySelector('.btn-icon');
            if (spinner) spinner.classList.remove('d-none');
            if (icon) icon.classList.add('d-none');

            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            var token = csrfToken ? csrfToken.getAttribute('content') : formAtualizar.querySelector('[name="_token"]').value;

            fetch(formAtualizar.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    // Recarregar pagina para atualizar dados
                    window.location.reload();
                }
            })
            .catch(function () {
                // Fallback: submit normal
                formAtualizar.removeEventListener('submit', arguments.callee);
                formAtualizar.submit();
            })
            .finally(function () {
                btn.disabled = false;
                btn.classList.remove('loading');
                if (spinner) spinner.classList.add('d-none');
                if (icon) icon.classList.remove('d-none');
            });
        });
    }

    // ============================================
    // 3. Bootstrap Tooltips Init
    // ============================================
    var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

})();
