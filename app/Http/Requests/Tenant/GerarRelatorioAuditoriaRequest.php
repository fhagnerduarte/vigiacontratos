<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class GerarRelatorioAuditoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('relatorio.gerar');
    }

    public function rules(): array
    {
        return [
            'data_inicio' => ['required', 'date', 'before_or_equal:data_fim'],
            'data_fim'    => ['required', 'date', 'after_or_equal:data_inicio'],
            'tipo_acao'   => ['nullable', 'string', 'in:alteracao,login,acesso_documento'],
            'user_id'     => ['nullable', 'integer', 'exists:tenant.users,id'],
            'entidade'    => ['nullable', 'string', 'in:contrato,aditivo,fornecedor,secretaria,servidor,user,role'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_inicio.required' => 'A data de inicio e obrigatoria.',
            'data_inicio.before_or_equal' => 'A data de inicio deve ser anterior ou igual a data fim.',
            'data_fim.required' => 'A data de fim e obrigatoria.',
            'data_fim.after_or_equal' => 'A data de fim deve ser posterior ou igual a data de inicio.',
        ];
    }
}
