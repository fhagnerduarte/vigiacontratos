/**
 * Input Masks — vigiacontratos
 *
 * Uso: adicionar data-mask no input.
 *   data-mask="cnpj"       → 00.000.000/0001-00
 *   data-mask="cpf"        → 000.000.000-00
 *   data-mask="telefone"   → (00) 0000-0000 ou (00) 00000-0000
 *   data-mask="cep"        → 00000-000
 *   data-mask="moeda"      → 1.234.567,89
 *   data-mask="percentual" → 100,00
 */
$(function () {
    var masks = {
        cnpj: function (v) {
            v = v.replace(/\D/g, '').substring(0, 14);
            v = v.replace(/^(\d{2})(\d)/, '$1.$2');
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
            v = v.replace(/(\d{4})(\d)/, '$1-$2');
            return v;
        },
        cpf: function (v) {
            v = v.replace(/\D/g, '').substring(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            return v;
        },
        telefone: function (v) {
            v = v.replace(/\D/g, '').substring(0, 11);
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (v.length > 6) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else if (v.length > 0) {
                v = v.replace(/^(\d{0,2})/, '($1');
            }
            return v;
        },
        cep: function (v) {
            v = v.replace(/\D/g, '').substring(0, 8);
            v = v.replace(/^(\d{5})(\d)/, '$1-$2');
            return v;
        },
        moeda: function (v) {
            v = v.replace(/\D/g, '');
            if (v === '') return '';
            var inteiro = parseInt(v, 10);
            v = (inteiro / 100).toFixed(2);
            v = v.replace('.', ',');
            v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            return v;
        },
        percentual: function (v) {
            v = v.replace(/\D/g, '').substring(0, 5);
            if (v === '') return '';
            var inteiro = parseInt(v, 10);
            if (inteiro > 10000) inteiro = 10000;
            v = (inteiro / 100).toFixed(2);
            v = v.replace('.', ',');
            return v;
        }
    };

    $(document).on('input', '[data-mask]', function () {
        var tipo = $(this).data('mask');
        var fn = masks[tipo];
        if (fn) {
            var pos = this.selectionStart;
            var oldLen = this.value.length;
            this.value = fn(this.value);
            var newLen = this.value.length;
            var newPos = pos + (newLen - oldLen);
            if (newPos < 0) newPos = 0;
            this.setSelectionRange(newPos, newPos);
        }
    });

    // Aplicar mascara nos valores pre-existentes (edit forms)
    $('[data-mask]').each(function () {
        var tipo = $(this).data('mask');
        var fn = masks[tipo];
        if (fn && this.value) {
            this.value = fn(this.value);
        }
    });
});
