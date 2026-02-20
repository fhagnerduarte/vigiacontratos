<?php

namespace App\Http\Requests\Tenant;

use App\Enums\CategoriaContrato;
use App\Enums\CategoriaServico;
use App\Enums\ModalidadeContratacao;
use App\Enums\TipoContrato;
use App\Enums\TipoPagamento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContratoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('contrato.criar');
    }

    public function rules(): array
    {
        return [
            // Identificacao
            'ano' => ['required', 'string', 'size:4'],
            'objeto' => ['required', 'string'],
            'tipo' => ['required', 'string', Rule::in(array_column(TipoContrato::cases(), 'value'))],
            'modalidade_contratacao' => ['required', 'string', Rule::in(array_column(ModalidadeContratacao::cases(), 'value'))],
            'secretaria_id' => ['required', Rule::exists('tenant.secretarias', 'id')],
            'unidade_gestora' => ['nullable', 'string', 'max:255'],
            'numero_processo' => ['required', 'string', 'max:50'],

            // Fornecedor
            'fornecedor_id' => ['required', Rule::exists('tenant.fornecedores', 'id')],

            // Financeiro
            'valor_global' => ['required', 'numeric', 'min:0.01'],
            'valor_mensal' => ['nullable', 'numeric', 'min:0'],
            'tipo_pagamento' => ['nullable', 'string', Rule::in(array_column(TipoPagamento::cases(), 'value'))],
            'fonte_recurso' => ['nullable', 'string', 'max:255'],
            'dotacao_orcamentaria' => ['nullable', 'string', 'max:255'],
            'numero_empenho' => ['nullable', 'string', 'max:50'],
            'categoria' => ['nullable', 'string', Rule::in(array_column(CategoriaContrato::cases(), 'value'))],
            'categoria_servico' => ['nullable', 'string', Rule::in(array_column(CategoriaServico::cases(), 'value'))],

            // Vigencia
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'prorrogacao_automatica' => ['nullable', 'boolean'],

            // Condicionais
            'fundamento_legal' => ['nullable', 'required_if:modalidade_contratacao,dispensa', 'required_if:modalidade_contratacao,inexigibilidade', 'string', 'max:255'],
            'responsavel_tecnico' => ['nullable', 'required_if:tipo,obra', 'string', 'max:255'],

            // Fiscal
            'fiscal_nome' => ['required', 'string', 'max:255'],
            'fiscal_matricula' => ['required', 'string', 'max:50'],
            'fiscal_cargo' => ['required', 'string', 'max:255'],
            'fiscal_email' => ['nullable', 'string', 'email', 'max:255'],

            // Outros
            'gestor_nome' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'objeto.required' => 'O objeto do contrato e obrigatorio.',
            'tipo.required' => 'O tipo do contrato e obrigatorio.',
            'modalidade_contratacao.required' => 'A modalidade de contratacao e obrigatoria.',
            'secretaria_id.required' => 'A secretaria e obrigatoria.',
            'secretaria_id.exists' => 'A secretaria selecionada nao existe.',
            'fornecedor_id.required' => 'O fornecedor e obrigatorio.',
            'fornecedor_id.exists' => 'O fornecedor selecionado nao existe.',
            'numero_processo.required' => 'O numero do processo administrativo e obrigatorio (RN-023).',
            'valor_global.required' => 'O valor global e obrigatorio.',
            'valor_global.min' => 'O valor global deve ser maior que zero (RN-004).',
            'data_inicio.required' => 'A data de inicio e obrigatoria.',
            'data_fim.required' => 'A data de fim e obrigatoria.',
            'data_fim.after_or_equal' => 'A data de fim deve ser igual ou posterior a data de inicio (RN-003).',
            'fundamento_legal.required_if' => 'O fundamento legal e obrigatorio para esta modalidade (RN-025/RN-026).',
            'responsavel_tecnico.required_if' => 'O responsavel tecnico e obrigatorio para contratos de obra (RN-028).',
            'fiscal_nome.required' => 'O nome do fiscal e obrigatorio (RN-024).',
            'fiscal_matricula.required' => 'A matricula do fiscal e obrigatoria.',
            'fiscal_cargo.required' => 'O cargo do fiscal e obrigatorio.',
            'ano.required' => 'O ano e obrigatorio.',
            'ano.size' => 'O ano deve ter 4 digitos.',
        ];
    }
}
