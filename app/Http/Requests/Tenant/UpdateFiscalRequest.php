<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFiscalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('fiscal.editar');
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'matricula' => ['required', 'string', 'max:50'],
            'cargo' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do fiscal é obrigatório.',
            'matricula.required' => 'A matrícula do fiscal é obrigatória.',
            'cargo.required' => 'O cargo do fiscal é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
        ];
    }
}
