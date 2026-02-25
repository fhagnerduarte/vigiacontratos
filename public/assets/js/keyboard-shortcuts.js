/**
 * Keyboard Shortcuts — Atalhos globais de teclado.
 * '/' => foca busca
 * 'Escape' => desfoca / fecha dropdown
 */
(function () {
    'use strict';

    document.addEventListener('keydown', function (e) {
        var tag = document.activeElement.tagName.toLowerCase();
        var isEditing = (tag === 'input' || tag === 'textarea' || tag === 'select' || document.activeElement.isContentEditable);

        // '/' foca a busca (somente fora de campos editaveis)
        if (e.key === '/' && !isEditing) {
            e.preventDefault();
            var searchInput = document.getElementById('globalSearchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }

        // 'Escape' desfoca o campo ativo
        if (e.key === 'Escape' && isEditing) {
            document.activeElement.blur();
        }
    });
})();
