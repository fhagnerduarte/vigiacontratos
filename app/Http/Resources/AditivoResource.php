<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AditivoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contrato_id' => $this->contrato_id,
            'numero_sequencial' => $this->numero_sequencial,
            'tipo' => $this->formatEnum($this->tipo),
            'status' => $this->formatEnum($this->status),
            'data_assinatura' => $this->data_assinatura?->toDateString(),
            'data_inicio_vigencia' => $this->data_inicio_vigencia?->toDateString(),
            'nova_data_fim' => $this->nova_data_fim?->toDateString(),
            'valor_anterior_contrato' => $this->valor_anterior_contrato,
            'valor_acrescimo' => $this->valor_acrescimo,
            'valor_supressao' => $this->valor_supressao,
            'percentual_acumulado' => $this->percentual_acumulado,
            'fundamentacao_legal' => $this->fundamentacao_legal,
            'justificativa' => $this->justificativa,
            'parecer_juridico_obrigatorio' => $this->parecer_juridico_obrigatorio,
            'workflow_aprovado' => $this->workflow_aprovado,
            'contrato' => new ContratoResource($this->whenLoaded('contrato')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function formatEnum($enum): ?array
    {
        if ($enum === null) {
            return null;
        }

        $result = ['value' => $enum->value];

        if (method_exists($enum, 'label')) {
            $result['label'] = $enum->label();
        }

        return $result;
    }
}
