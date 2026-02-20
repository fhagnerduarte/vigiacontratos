<?php

namespace App\Services;

class FornecedorService
{
    /**
     * Valida os digitos verificadores do CNPJ (RN-038).
     * Aceita CNPJ com ou sem formatacao.
     */
    public static function validarCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Primeiro digito verificador
        $soma = 0;
        $peso = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $soma += (int) $cnpj[$i] * $peso[$i];
        }
        $resto = $soma % 11;
        $dig1 = $resto < 2 ? 0 : 11 - $resto;

        if ((int) $cnpj[12] !== $dig1) {
            return false;
        }

        // Segundo digito verificador
        $soma = 0;
        $peso = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $soma += (int) $cnpj[$i] * $peso[$i];
        }
        $resto = $soma % 11;
        $dig2 = $resto < 2 ? 0 : 11 - $resto;

        return (int) $cnpj[13] === $dig2;
    }

    /**
     * Formata CNPJ no padrao 00.000.000/0001-00.
     */
    public static function formatarCnpj(string $cnpj): string
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        return preg_replace(
            '/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/',
            '$1.$2.$3/$4-$5',
            $cnpj
        );
    }
}
