<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreSecretariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'        => ['required', 'string', 'max:255'],
            'sigla'       => ['nullable', 'string', 'max:20'],
            'responsavel' => ['nullable', 'string', 'max:255'],
            'email'       => ['nullable', 'string', 'email', 'max:255'],
            'telefone'    => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da secretaria e obrigatorio.',
            'email.email'   => 'Informe um e-mail valido.',
        ];
    }
}
