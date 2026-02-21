<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/[^0-9]/', '', $value);

        if (! self::validarCpf($cpf)) {
            $fail('O CPF informado e invalido.');
        }
    }

    public static function validarCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Rejeita sequencias repetidas (000.000.000-00, 111.111.111-11, etc.)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Calculo do primeiro digito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int) $cpf[$i] * (10 - $i);
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : (11 - $resto);

        if ((int) $cpf[9] !== $digito1) {
            return false;
        }

        // Calculo do segundo digito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += (int) $cpf[$i] * (11 - $i);
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : (11 - $resto);

        return (int) $cpf[10] === $digito2;
    }

    public static function formatarCpf(string $cpf): string
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' .
               substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
}
