<?php

namespace App\Http\Requests\Tenant;

use App\Rules\CnpjValido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFornecedorRequest extends FormRequest
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
            'cnpj'                => ['required', 'string', 'max:18', Rule::unique('tenant.fornecedores', 'cnpj'), new CnpjValido()],
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
            'razao_social.required' => 'A razao social e obrigatoria.',
            'cnpj.required'         => 'O CNPJ e obrigatorio.',
            'cnpj.unique'           => 'Este CNPJ ja esta cadastrado.',
            'email.email'           => 'Informe um e-mail valido.',
            'uf.size'               => 'A UF deve ter exatamente 2 caracteres.',
        ];
    }
}
