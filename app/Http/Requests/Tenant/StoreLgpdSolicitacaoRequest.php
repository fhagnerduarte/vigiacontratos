<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TipoSolicitacaoLGPD;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLgpdSolicitacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_solicitacao' => ['required', Rule::enum(TipoSolicitacaoLGPD::class)],
            'entidade_tipo' => ['required', 'in:fornecedor,fiscal,servidor,usuario'],
            'entidade_id' => ['required', 'integer', 'min:1'],
            'justificativa' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_solicitacao.required' => 'O tipo de solicitação é obrigatório.',
            'entidade_tipo.required' => 'O tipo de entidade é obrigatório.',
            'entidade_tipo.in' => 'Tipo de entidade inválido.',
            'entidade_id.required' => 'Selecione a entidade.',
            'entidade_id.integer' => 'ID da entidade inválido.',
            'justificativa.required' => 'A justificativa é obrigatória.',
            'justificativa.min' => 'A justificativa deve ter pelo menos 10 caracteres.',
            'justificativa.max' => 'A justificativa deve ter no máximo 500 caracteres.',
        ];
    }
}
