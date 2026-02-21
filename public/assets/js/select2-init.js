/**
 * Select2 — Inicializacao global
 *
 * Aplica Select2 a todos os <select> com classe .select2
 * Usa tema Bootstrap 5 e idioma pt-BR.
 *
 * - dropdownParent automatico para selects dentro de modais Bootstrap
 * - allowClear habilitado apenas em selects opcionais (sem required)
 */
$(function () {
    if (!$.fn.select2) return;

    $('.select2').each(function () {
        var $el = $(this);
        var options = {
            theme: 'bootstrap-5',
            language: 'pt-BR',
            width: '100%',
            placeholder: $el.data('placeholder') || 'Selecione...'
        };

        // allowClear apenas em selects opcionais
        if (!$el.prop('required')) {
            options.allowClear = true;
        }

        // dropdownParent para selects dentro de modais Bootstrap
        var $modal = $el.closest('.modal');
        if ($modal.length) {
            options.dropdownParent = $modal;
        }

        $el.select2(options);
    });
});
