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
            'tipo_ocorrencia.required' => 'O tipo de ocorrencia e obrigatorio.',
            'tipo_ocorrencia.in' => 'Tipo de ocorrencia invalido.',
            'data_ocorrencia.required' => 'A data da ocorrencia e obrigatoria.',
            'data_ocorrencia.before_or_equal' => 'A data da ocorrencia nao pode ser futura.',
            'descricao.required' => 'A descricao e obrigatoria.',
            'descricao.min' => 'A descricao deve ter no minimo 10 caracteres.',
            'prazo_providencia.after_or_equal' => 'O prazo de providencia deve ser posterior a data da ocorrencia.',
        ];
    }
}
