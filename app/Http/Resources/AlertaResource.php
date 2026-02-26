<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contrato_id' => $this->contrato_id,
            'tipo_evento' => $this->formatEnum($this->tipo_evento),
            'prioridade' => $this->formatEnum($this->prioridade),
            'status' => $this->formatEnum($this->status),
            'dias_para_vencimento' => $this->dias_para_vencimento,
            'data_vencimento' => $this->data_vencimento?->toDateString(),
            'data_disparo' => $this->data_disparo?->toIso8601String(),
            'mensagem' => $this->mensagem,
            'visualizado_em' => $this->visualizado_em?->toIso8601String(),
            'resolvido_em' => $this->resolvido_em?->toIso8601String(),
            'contrato' => new ContratoResource($this->whenLoaded('contrato')),
            'created_at' => $this->created_at?->toIso8601String(),
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
