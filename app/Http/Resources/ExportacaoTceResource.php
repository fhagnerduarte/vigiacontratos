<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportacaoTceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'formato' => [
                'value' => $this->formato->value,
                'label' => $this->formato->label(),
            ],
            'filtros' => $this->filtros,
            'total_contratos' => $this->total_contratos,
            'total_pendencias' => $this->total_pendencias,
            'arquivo_nome' => $this->arquivo_nome,
            'gerado_por' => [
                'id' => $this->geradoPor?->id,
                'nome' => $this->geradoPor?->nome,
            ],
            'observacoes' => $this->observacoes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
