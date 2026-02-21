<?php

namespace App\Http\Requests\Tenant;

use App\Rules\CpfValido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServidorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'          => ['required', 'string', 'max:255'],
            'cpf'           => [
                'nullable',
                'string',
                'max:14',
                Rule::unique('tenant.servidores', 'cpf')->ignore($this->route('servidor')),
                new CpfValido(),
            ],
            'matricula'     => [
                'required',
                'string',
                'max:50',
                Rule::unique('tenant.servidores', 'matricula')->ignore($this->route('servidor')),
            ],
            'cargo'         => ['required', 'string', 'max:255'],
            'secretaria_id' => ['nullable', Rule::exists('tenant.secretarias', 'id')],
            'email'         => ['nullable', 'string', 'email', 'max:255'],
            'telefone'      => ['nullable', 'string', 'max:20'],
            'is_ativo'      => ['nullable', 'boolean'],
            'observacoes'   => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'      => 'O nome do servidor e obrigatorio.',
            'matricula.required' => 'A matricula e obrigatoria.',
            'matricula.unique'   => 'Esta matricula ja esta cadastrada.',
            'cpf.unique'         => 'Este CPF ja esta cadastrado.',
            'cargo.required'     => 'O cargo e obrigatorio.',
            'email.email'        => 'Informe um e-mail valido.',
        ];
    }
}
