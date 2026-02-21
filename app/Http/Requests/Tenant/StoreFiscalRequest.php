<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFiscalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('fiscal.criar');
    }

    public function rules(): array
    {
        return [
            'servidor_id' => ['required', Rule::exists('tenant.servidores', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'servidor_id.required' => 'Selecione o servidor que sera o fiscal.',
            'servidor_id.exists' => 'O servidor selecionado nao existe.',
        ];
    }
}
