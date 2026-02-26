<?php

namespace App\Http\Requests\Tenant;

use App\Rules\CnpjValido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFornecedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'razao_social'        => ['required', 'string', 'max:255'],
            'nome_fantasia'       => ['nullable', 'string', 'max:255'],
            'cnpj'                => [
                'required',
                'string',
                'max:18',
                Rule::unique('tenant.fornecedores', 'cnpj')->ignore($this->route('fornecedor')),
                new CnpjValido(),
            ],
            'representante_legal' => ['nullable', 'string', 'max:255'],
            'email'               => ['nullable', 'string', 'email', 'max:255'],
            'telefone'            => ['nullable', 'string', 'max:20'],
            'endereco'            => ['nullable', 'string', 'max:255'],
            'cidade'              => ['nullable', 'string', 'max:100'],
            'uf'                  => ['nullable', 'string', 'size:2'],
            'cep'                 => ['nullable', 'string', 'max:10'],
            'observacoes'         => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'razao_social.required' => 'A razão social é obrigatória.',
            'cnpj.required'         => 'O CNPJ é obrigatório.',
            'cnpj.unique'           => 'Este CNPJ já está cadastrado.',
            'email.email'           => 'Informe um e-mail válido.',
            'uf.size'               => 'A UF deve ter exatamente 2 caracteres.',
        ];
    }
}
