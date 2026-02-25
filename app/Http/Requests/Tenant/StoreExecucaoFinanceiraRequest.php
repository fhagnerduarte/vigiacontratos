<?php

namespace App\Http\Requests\Tenant;

use App\Enums\TipoExecucaoFinanceira;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExecucaoFinanceiraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('financeiro.registrar_empenho');
    }

    public function rules(): array
    {
        return [
            'tipo_execucao' => ['nullable', Rule::in(array_column(TipoExecucaoFinanceira::cases(), 'value'))],
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data_execucao' => ['required', 'date'],
            'numero_nota_fiscal' => ['nullable', 'string', 'max:50'],
            'numero_empenho' => ['nullable', 'string', 'max:50'],
            'competencia' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'descricao.required' => 'A descricao e obrigatoria.',
            'valor.required' => 'O valor e obrigatorio.',
            'valor.min' => 'O valor deve ser maior que zero.',
            'data_execucao.required' => 'A data de execucao e obrigatoria.',
            'data_execucao.date' => 'Informe uma data valida.',
            'competencia.regex' => 'A competencia deve estar no formato AAAA-MM (ex: 2026-01).',
        ];
    }
}
