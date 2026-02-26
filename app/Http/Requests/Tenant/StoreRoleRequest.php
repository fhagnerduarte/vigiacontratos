<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('configuracao.editar');
    }

    public function rules(): array
    {
        return [
            'nome'      => ['required', 'string', 'max:100', 'regex:/^[a-z_]+$/', Rule::unique('tenant.roles', 'nome')],
            'descricao' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O identificador do perfil é obrigatório.',
            'nome.regex'    => 'O identificador deve conter apenas letras minúsculas e underscores.',
            'nome.unique'   => 'Já existe um perfil com este identificador.',
            'descricao.required' => 'A descrição é obrigatória.',
        ];
    }
}
