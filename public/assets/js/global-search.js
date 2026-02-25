/**
 * Global Search — Busca AJAX com typeahead no navbar.
 * Requer: input#globalSearchInput com data-search-url
 */
(function () {
    'use strict';

    var input = document.getElementById('globalSearchInput');
    var resultsContainer = document.getElementById('globalSearchResults');
    if (!input || !resultsContainer) return;

    var searchUrl = input.dataset.searchUrl;
    if (!searchUrl) return;

    var debounceTimer = null;
    var selectedIndex = -1;

    // Debounce e fetch
    input.addEventListener('input', function () {
        var query = this.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            hideResults();
            return;
        }

        debounceTimer = setTimeout(function () {
            fetchResults(query);
        }, 300);
    });

    // Keyboard navigation
    input.addEventListener('keydown', function (e) {
        var items = resultsContainer.querySelectorAll('.search-result-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            highlightItem(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, 0);
            highlightItem(items);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            var link = items[selectedIndex].getAttribute('data-url');
            if (link) window.location.href = link;
        } else if (e.key === 'Escape') {
            hideResults();
            input.blur();
        }
    });

    // Click outside closes results
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
            hideResults();
        }
    });

    function fetchResults(query) {
        resultsContainer.innerHTML = '<div class="px-16 py-12 text-center text-secondary-light text-sm">Buscando...</div>';
        showResults();

        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        var headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');

        fetch(searchUrl + '?q=' + encodeURIComponent(query), { headers: headers })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                renderResults(data);
            })
            .catch(function () {
                resultsContainer.innerHTML = '<div class="px-16 py-12 text-center text-danger text-sm">Erro na busca</div>';
            });
    }

    function renderResults(data) {
        selectedIndex = -1;

        if (!data.length) {
            resultsContainer.innerHTML = '<div class="px-16 py-12 text-center text-secondary-light text-sm">Nenhum resultado encontrado</div>';
            showResults();
            return;
        }

        // Group by type
        var groups = {};
        data.forEach(function (item) {
            if (!groups[item.type]) groups[item.type] = [];
            groups[item.type].push(item);
        });

        var html = '';
        for (var type in groups) {
            html += '<div class="search-group-title">' + type + '</div>';
            groups[type].forEach(function (item) {
                html += '<div class="search-result-item d-flex align-items-center gap-12" data-url="' + item.url + '">';
                html += '<div class="w-36-px h-36-px bg-primary-50 rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">';
                html += '<iconify-icon icon="' + item.icon + '" class="text-primary-600 text-lg"></iconify-icon>';
                html += '</div>';
                html += '<div class="flex-grow-1 min-width-0">';
                html += '<span class="d-block text-sm fw-medium text-truncate">' + escapeHtml(item.label) + '</span>';
                if (item.sublabel) {
                    html += '<span class="d-block text-xs text-secondary-light text-truncate">' + escapeHtml(item.sublabel) + '</span>';
                }
                html += '</div>';
                html += '</div>';
            });
        }

        resultsContainer.innerHTML = html;
        showResults();

        // Click on result
        resultsContainer.querySelectorAll('.search-result-item').forEach(function (el) {
            el.addEventListener('click', function () {
                window.location.href = this.getAttribute('data-url');
            });
        });
    }

    function highlightItem(items) {
        items.forEach(function (el, i) {
            el.classList.toggle('active', i === selectedIndex);
        });
    }

    function showResults() {
        resultsContainer.style.display = 'block';
    }

    function hideResults() {
        resultsContainer.style.display = 'none';
        selectedIndex = -1;
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
