/**
 * Confirm Dialog — Substitui confirm() nativo por SweetAlert2.
 * Uso: <form data-confirm="Mensagem de confirmacao"> ou
 *       <button data-confirm="Mensagem" data-confirm-form="#formId">
 *
 * Atributos opcionais:
 * - data-confirm-title: titulo do dialog (default: "Confirmar acao")
 * - data-confirm-btn: texto do botao confirmar (default: "Sim, confirmar")
 * - data-confirm-icon: icone swal (default: "warning")
 */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swal === 'undefined') return;

    // Forms com data-confirm
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var self = this;

            Swal.fire({
                title: self.dataset.confirmTitle || 'Confirmar acao',
                text: self.dataset.confirm,
                icon: self.dataset.confirmIcon || 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4A00',
                cancelButtonColor: '#6c757d',
                confirmButtonText: self.dataset.confirmBtn || 'Sim, confirmar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    // Remove o listener para evitar loop e submete
                    self.removeAttribute('data-confirm');
                    self.submit();
                }
            });
        });
    });

    // Botoes/links com data-confirm que disparam submit de outro form
    document.querySelectorAll('[data-confirm]:not(form)').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var targetFormSelector = this.dataset.confirmForm;
            if (!targetFormSelector) return; // Sem form target, ignora

            e.preventDefault();
            var self = this;
            var targetForm = document.querySelector(targetFormSelector);
            if (!targetForm) return;

            Swal.fire({
                title: self.dataset.confirmTitle || 'Confirmar acao',
                text: self.dataset.confirm,
                icon: self.dataset.confirmIcon || 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4A00',
                cancelButtonColor: '#6c757d',
                confirmButtonText: self.dataset.confirmBtn || 'Sim, confirmar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    targetForm.submit();
                }
            });
        });
    });
});
