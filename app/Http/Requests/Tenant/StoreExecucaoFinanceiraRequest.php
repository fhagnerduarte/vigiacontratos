<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreExecucaoFinanceiraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('financeiro.registrar_empenho');
    }

    public function rules(): array
    {
        return [
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data_execucao' => ['required', 'date'],
            'numero_nota_fiscal' => ['nullable', 'string', 'max:50'],
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
        ];
    }
}
