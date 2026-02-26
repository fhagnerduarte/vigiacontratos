<?php

namespace App\Http\Requests\Tenant;

use App\Enums\CategoriaContrato;
use App\Enums\CategoriaServico;
use App\Enums\ClassificacaoSigilo;
use App\Enums\ModalidadeContratacao;
use App\Enums\RegimeExecucao;
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
            'regime_execucao' => ['nullable', 'string', Rule::in(array_column(RegimeExecucao::cases(), 'value'))],
            'condicoes_pagamento' => ['nullable', 'string'],
            'garantias' => ['nullable', 'string'],
            'fonte_recurso' => ['nullable', 'string', 'max:255'],
            'dotacao_orcamentaria' => ['nullable', 'string', 'max:255'],
            'numero_empenho' => ['nullable', 'string', 'max:50'],
            'categoria' => ['nullable', 'string', Rule::in(array_column(CategoriaContrato::cases(), 'value'))],
            'categoria_servico' => ['nullable', 'string', Rule::in(array_column(CategoriaServico::cases(), 'value'))],

            // Vigencia
            'data_assinatura' => ['nullable', 'date', 'before_or_equal:data_inicio'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'prorrogacao_automatica' => ['nullable', 'boolean'],

            // Publicacao
            'data_publicacao' => ['nullable', 'date'],
            'veiculo_publicacao' => ['nullable', 'string', 'max:255'],
            'link_transparencia' => ['nullable', 'string', 'max:500', 'url'],
            'classificacao_sigilo' => ['nullable', 'string', Rule::in(array_column(ClassificacaoSigilo::cases(), 'value'))],
            'publicado_portal' => ['nullable', 'boolean'],
            'justificativa_sigilo' => ['nullable', 'required_unless:classificacao_sigilo,publico,classificacao_sigilo,null', 'string'],

            // Condicionais
            'fundamento_legal' => ['nullable', 'required_if:modalidade_contratacao,dispensa', 'required_if:modalidade_contratacao,inexigibilidade', 'string', 'max:255'],
            'responsavel_tecnico' => ['nullable', 'required_if:tipo,obra', 'string', 'max:255'],

            // Fiscal
            'fiscal_servidor_id' => ['required', Rule::exists('tenant.servidores', 'id')],
            'fiscal_substituto_servidor_id' => ['nullable', Rule::exists('tenant.servidores', 'id'), 'different:fiscal_servidor_id'],
            'portaria_designacao' => ['nullable', 'string', 'max:100'],

            // Outros
            'servidor_id' => ['nullable', Rule::exists('tenant.servidores', 'id')],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'objeto.required' => 'O objeto do contrato é obrigatório.',
            'tipo.required' => 'O tipo do contrato é obrigatório.',
            'modalidade_contratacao.required' => 'A modalidade de contratação é obrigatória.',
            'secretaria_id.required' => 'A secretaria é obrigatória.',
            'secretaria_id.exists' => 'A secretaria selecionada não existe.',
            'fornecedor_id.required' => 'O fornecedor é obrigatório.',
            'fornecedor_id.exists' => 'O fornecedor selecionado não existe.',
            'numero_processo.required' => 'O número do processo administrativo é obrigatório (RN-023).',
            'valor_global.required' => 'O valor global é obrigatório.',
            'valor_global.min' => 'O valor global deve ser maior que zero (RN-004).',
            'data_assinatura.before_or_equal' => 'A data de assinatura deve ser anterior ou igual à data de início.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_fim.required' => 'A data de fim é obrigatória.',
            'data_fim.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início (RN-003).',
            'link_transparencia.url' => 'O link da transparência deve ser uma URL válida.',
            'fundamento_legal.required_if' => 'O fundamento legal é obrigatório para esta modalidade (RN-025/RN-026).',
            'responsavel_tecnico.required_if' => 'O responsável técnico é obrigatório para contratos de obra (RN-028).',
            'fiscal_servidor_id.required' => 'Selecione o servidor que será o fiscal do contrato (RN-024).',
            'fiscal_servidor_id.exists' => 'O servidor selecionado não existe.',
            'fiscal_substituto_servidor_id.exists' => 'O servidor substituto selecionado não existe.',
            'fiscal_substituto_servidor_id.different' => 'O fiscal substituto deve ser diferente do fiscal titular.',
            'ano.required' => 'O ano é obrigatório.',
            'ano.size' => 'O ano deve ter 4 dígitos.',
        ];
    }
}
