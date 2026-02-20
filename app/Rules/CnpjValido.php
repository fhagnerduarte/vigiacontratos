<?php

namespace App\Rules;

use App\Services\FornecedorService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CnpjValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnpj = preg_replace('/[^0-9]/', '', $value);

        if (! FornecedorService::validarCnpj($cnpj)) {
            $fail('O CNPJ informado e invalido.');
        }
    }
}
