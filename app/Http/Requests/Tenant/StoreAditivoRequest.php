<?php

namespace App\Http\Requests\Tenant;

use App\Enums\StatusContrato;
use App\Enums\TipoAditivo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAditivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('aditivo.criar');
    }

    public function rules(): array
    {
        $contrato = $this->route('contrato');
        $dataFimAtual = $contrato?->data_fim?->toDateString();

        return [
            // Obrigatorios para todos os tipos (RN-088 a RN-090)
            'tipo' => ['required', 'string', Rule::in(array_column(TipoAditivo::cases(), 'value'))],
            'data_assinatura' => ['required', 'date'],
            'data_inicio_vigencia' => [
                'nullable',
                'required_if:tipo,prazo',
                'required_if:tipo,valor',
                'required_if:tipo,prazo_e_valor',
                'required_if:tipo,misto',
                'required_if:tipo,reequilibrio',
                'date',
                'after_or_equal:data_assinatura',
            ],
            'fundamentacao_legal' => ['required', 'string'],
            'justificativa' => ['required', 'string'],
            'justificativa_tecnica' => ['required', 'string'],

            // Condicionais por tipo — Prazo (RN-010)
            'nova_data_fim' => array_filter([
                'nullable',
                'required_if:tipo,prazo',
                'required_if:tipo,prazo_e_valor',
                'required_if:tipo,misto',
                'date',
                $dataFimAtual ? 'after:' . $dataFimAtual : null,
            ]),

            // Condicionais por tipo — Valor (RN-093)
            // Para reequilibrio, valor_acrescimo e calculado automaticamente (RN-095)
            'valor_acrescimo' => ['nullable', 'required_if:tipo,valor', 'required_if:tipo,prazo_e_valor', 'required_if:tipo,misto', 'numeric', 'min:0.01'],

            // Condicionais por tipo — Supressao (RN-094)
            'valor_supressao' => ['nullable', 'required_if:tipo,supressao', 'required_if:tipo,misto', 'numeric', 'min:0.01'],

            // Condicionais por tipo — Reequilibrio (RN-095)
            'motivo_reequilibrio' => ['nullable', 'required_if:tipo,reequilibrio', 'string'],
            'indice_utilizado' => ['nullable', 'required_if:tipo,reequilibrio', 'string', 'max:50'],
            'valor_anterior_reequilibrio' => ['nullable', 'required_if:tipo,reequilibrio', 'numeric', 'min:0.01'],
            'valor_reajustado' => ['nullable', 'required_if:tipo,reequilibrio', 'numeric', 'min:0.01'],

            // Justificativa de excesso de limite (RN-102)
            'justificativa_excesso_limite' => ['nullable', 'string'],

            // Justificativa retroativa obrigatoria se contrato vencido (RN-052)
            'justificativa_retroativa' => [
                'nullable',
                Rule::requiredIf(fn () => $contrato?->status === StatusContrato::Vencido),
                'string',
                'min:50',
            ],

            // Outros
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'O tipo do aditivo é obrigatório (RN-088).',
            'tipo.in' => 'Tipo de aditivo inválido.',
            'data_assinatura.required' => 'A data de assinatura é obrigatória.',
            'data_inicio_vigencia.required_if' => 'A data de início de vigência é obrigatória para este tipo de aditivo (RN-092).',
            'data_inicio_vigencia.after_or_equal' => 'A data de início de vigência deve ser igual ou posterior à data de assinatura (RN-092).',
            'fundamentacao_legal.required' => 'A fundamentação legal é obrigatória em todos os aditivos (RN-089).',
            'justificativa.required' => 'A justificativa geral é obrigatória.',
            'justificativa_tecnica.required' => 'A justificativa técnica é obrigatória (RN-090).',
            'nova_data_fim.required_if' => 'A nova data de fim é obrigatória para este tipo de aditivo (RN-010).',
            'nova_data_fim.after' => 'A nova data de fim deve ser posterior à data de fim atual do contrato (RN-010).',
            'valor_acrescimo.required_if' => 'O valor de acréscimo é obrigatório para este tipo de aditivo (RN-093).',
            'valor_acrescimo.min' => 'O valor de acréscimo deve ser maior que zero.',
            'valor_supressao.required_if' => 'O valor de supressão é obrigatório para este tipo de aditivo (RN-094).',
            'valor_supressao.min' => 'O valor de supressão deve ser maior que zero.',
            'motivo_reequilibrio.required_if' => 'O motivo do reequilíbrio é obrigatório (RN-095).',
            'indice_utilizado.required_if' => 'O índice utilizado é obrigatório para reequilíbrio (RN-095).',
            'valor_anterior_reequilibrio.required_if' => 'O valor anterior ao reequilíbrio é obrigatório (RN-095).',
            'valor_reajustado.required_if' => 'O valor reajustado é obrigatório (RN-095).',
            'justificativa_retroativa.required' => 'A justificativa retroativa é obrigatória para aditivos em contrato vencido (RN-052).',
            'justificativa_retroativa.min' => 'A justificativa retroativa deve ter no mínimo 50 caracteres para garantir fundamentação adequada.',
        ];
    }
}
