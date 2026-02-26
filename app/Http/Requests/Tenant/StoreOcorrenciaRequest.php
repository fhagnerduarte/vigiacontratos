<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TipoOcorrencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOcorrenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('ocorrencia.criar');
    }

    public function rules(): array
    {
        return [
            'tipo_ocorrencia' => ['required', Rule::in(array_column(TipoOcorrencia::cases(), 'value'))],
            'data_ocorrencia' => ['required', 'date', 'before_or_equal:today'],
            'descricao' => ['required', 'string', 'min:10', 'max:2000'],
            'providencia' => ['nullable', 'string', 'max:2000'],
            'prazo_providencia' => ['nullable', 'date', 'after_or_equal:data_ocorrencia'],
            'fiscal_id' => ['nullable', 'integer', 'exists:tenant.fiscais,id'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_ocorrencia.required' => 'O tipo de ocorrência é obrigatório.',
            'tipo_ocorrencia.in' => 'Tipo de ocorrência inválido.',
            'data_ocorrencia.required' => 'A data da ocorrência é obrigatória.',
            'data_ocorrencia.before_or_equal' => 'A data da ocorrência não pode ser futura.',
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.min' => 'A descrição deve ter no mínimo 10 caracteres.',
            'prazo_providencia.after_or_equal' => 'O prazo de providência deve ser posterior à data da ocorrência.',
        ];
    }
}
