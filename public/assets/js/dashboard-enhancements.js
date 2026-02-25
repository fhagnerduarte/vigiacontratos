/**
 * Dashboard Enhancements — Counters animados, async refresh, tooltips.
 */
(function () {
    'use strict';

    // ============================================
    // 1. Counters — exibicao direta (sem animacao)
    // ============================================
    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var target = parseFloat(el.dataset.countup);
        var prefix = el.dataset.countupPrefix || '';
        var decimals = parseInt(el.dataset.countupDecimals) || 0;

        if (decimals > 0) {
            el.textContent = prefix + target.toLocaleString('pt-BR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        } else {
            el.textContent = prefix + Math.round(target).toLocaleString('pt-BR');
        }
    });

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
