<?php

namespace App\Http\Requests\Tenant;

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

            // Outros
            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'O tipo do aditivo e obrigatorio (RN-088).',
            'tipo.in' => 'Tipo de aditivo invalido.',
            'data_assinatura.required' => 'A data de assinatura e obrigatoria.',
            'data_inicio_vigencia.required_if' => 'A data de inicio de vigencia e obrigatoria para este tipo de aditivo (RN-092).',
            'data_inicio_vigencia.after_or_equal' => 'A data de inicio de vigencia deve ser igual ou posterior a data de assinatura (RN-092).',
            'fundamentacao_legal.required' => 'A fundamentacao legal e obrigatoria em todos os aditivos (RN-089).',
            'justificativa.required' => 'A justificativa geral e obrigatoria.',
            'justificativa_tecnica.required' => 'A justificativa tecnica e obrigatoria (RN-090).',
            'nova_data_fim.required_if' => 'A nova data de fim e obrigatoria para este tipo de aditivo (RN-010).',
            'nova_data_fim.after' => 'A nova data de fim deve ser posterior a data de fim atual do contrato (RN-010).',
            'valor_acrescimo.required_if' => 'O valor de acrescimo e obrigatorio para este tipo de aditivo (RN-093).',
            'valor_acrescimo.min' => 'O valor de acrescimo deve ser maior que zero.',
            'valor_supressao.required_if' => 'O valor de supressao e obrigatorio para este tipo de aditivo (RN-094).',
            'valor_supressao.min' => 'O valor de supressao deve ser maior que zero.',
            'motivo_reequilibrio.required_if' => 'O motivo do reequilibrio e obrigatorio (RN-095).',
            'indice_utilizado.required_if' => 'O indice utilizado e obrigatorio para reequilibrio (RN-095).',
            'valor_anterior_reequilibrio.required_if' => 'O valor anterior ao reequilibrio e obrigatorio (RN-095).',
            'valor_reajustado.required_if' => 'O valor reajustado e obrigatorio (RN-095).',
        ];
    }
}
