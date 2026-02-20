<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('usuario.editar');
    }

    public function rules(): array
    {
        return [
            'nome'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', Rule::unique('tenant.users', 'email')->ignore($this->route('user'))],
            'password'      => ['nullable', 'string', Password::min(8), 'confirmed'],
            'role_id'       => ['required', 'integer', Rule::exists('tenant.roles', 'id')],
            'is_ativo'      => ['boolean'],
            'secretarias'   => ['nullable', 'array'],
            'secretarias.*' => ['integer', Rule::exists('tenant.secretarias', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'      => 'O nome e obrigatorio.',
            'email.required'     => 'O e-mail e obrigatorio.',
            'email.email'        => 'Informe um e-mail valido.',
            'email.unique'       => 'Este e-mail ja esta em uso.',
            'password.confirmed' => 'A confirmacao de senha nao confere.',
            'role_id.required'   => 'Selecione um perfil.',
            'role_id.exists'     => 'Perfil selecionado invalido.',
        ];
    }
}
