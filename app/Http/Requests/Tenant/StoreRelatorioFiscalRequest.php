<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreRelatorioFiscalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('relatorio_fiscal.criar');
    }

    public function rules(): array
    {
        return [
            'periodo_inicio' => ['required', 'date'],
            'periodo_fim' => ['required', 'date', 'after_or_equal:periodo_inicio'],
            'descricao_atividades' => ['required', 'string', 'min:10', 'max:5000'],
            'conformidade_geral' => ['required', 'boolean'],
            'nota_desempenho' => ['nullable', 'integer', 'min:1', 'max:10'],
            'fiscal_id' => ['nullable', 'integer', 'exists:tenant.fiscais,id'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'periodo_inicio.required' => 'O periodo de inicio e obrigatorio.',
            'periodo_fim.required' => 'O periodo final e obrigatorio.',
            'periodo_fim.after_or_equal' => 'O periodo final deve ser posterior ao inicio.',
            'descricao_atividades.required' => 'A descricao das atividades e obrigatoria.',
            'descricao_atividades.min' => 'A descricao deve ter no minimo 10 caracteres.',
            'nota_desempenho.min' => 'A nota de desempenho minima e 1.',
            'nota_desempenho.max' => 'A nota de desempenho maxima e 10.',
        ];
    }
}
