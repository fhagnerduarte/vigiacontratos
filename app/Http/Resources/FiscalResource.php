<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FiscalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contrato_id' => $this->contrato_id,
            'servidor_id' => $this->servidor_id,
            'nome' => $this->nome,
            'matricula' => $this->matricula,
            'cargo' => $this->cargo,
            'tipo_fiscal' => $this->formatEnum($this->tipo_fiscal),
            'portaria_designacao' => $this->portaria_designacao,
            'data_inicio' => $this->data_inicio?->toDateString(),
            'data_fim' => $this->data_fim?->toDateString(),
            'data_ultimo_relatorio' => $this->data_ultimo_relatorio?->toDateString(),
            'is_atual' => $this->is_atual,
            'servidor' => new ServidorResource($this->whenLoaded('servidor')),
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
