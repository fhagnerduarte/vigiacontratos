<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportacaoDadosAbertosResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dataset' => [
                'value' => $this->dataset->value,
                'label' => $this->dataset->label(),
            ],
            'formato' => [
                'value' => $this->formato->value,
                'label' => $this->formato->label(),
            ],
            'filtros' => $this->filtros,
            'total_registros' => $this->total_registros,
            'solicitante' => $this->whenLoaded('solicitante', fn () => [
                'id' => $this->solicitante->id,
                'nome' => $this->solicitante->nome,
            ]),
            'ip_solicitante' => $this->ip_solicitante,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
