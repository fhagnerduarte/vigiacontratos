/**
 * DataTables Global Init — Locale pt-BR + auto-init.
 *
 * Classe .datatable → full client-side (search, paging, ordering)
 * Tabelas grandes (contratos, alertas) usam paginacao server-side sem DataTables.
 */
(function () {
    'use strict';

    if (typeof $.fn.DataTable === 'undefined') return;

    // Locale pt-BR
    var ptBR = {
        emptyTable: 'Nenhum registro encontrado',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
        infoFiltered: '(filtrado de _MAX_ registros)',
        lengthMenu: 'Mostrar _MENU_ registros',
        loadingRecords: 'Carregando...',
        processing: 'Processando...',
        zeroRecords: 'Nenhum registro encontrado',
        search: 'Buscar:',
        paginate: {
            first: 'Primeiro',
            last: 'Ultimo',
            next: 'Proximo',
            previous: 'Anterior'
        },
        aria: {
            sortAscending: ': ordenar coluna crescente',
            sortDescending: ': ordenar coluna decrescente'
        }
    };

    // Client-side full DataTable
    document.querySelectorAll('table.datatable').forEach(function (table) {
        $(table).DataTable({
            language: ptBR,
            pageLength: 25,
            order: [],
            dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rtip',
            columnDefs: [
                { targets: 'no-sort', orderable: false }
            ]
        });
    });
})();
