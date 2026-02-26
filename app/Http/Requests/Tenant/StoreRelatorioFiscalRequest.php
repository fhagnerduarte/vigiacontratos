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
            'periodo_inicio.required' => 'O período de início é obrigatório.',
            'periodo_fim.required' => 'O período final é obrigatório.',
            'periodo_fim.after_or_equal' => 'O período final deve ser posterior ao início.',
            'descricao_atividades.required' => 'A descrição das atividades é obrigatória.',
            'descricao_atividades.min' => 'A descrição deve ter no mínimo 10 caracteres.',
            'nota_desempenho.min' => 'A nota de desempenho mínima é 1.',
            'nota_desempenho.max' => 'A nota de desempenho máxima é 10.',
        ];
    }
}
