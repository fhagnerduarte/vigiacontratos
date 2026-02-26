<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrecoReferencialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'categoria_servico' => [
                'value' => $this->categoria_servico->value,
                'label' => $this->categoria_servico->label(),
            ],
            'unidade_medida' => $this->unidade_medida,
            'preco_minimo' => $this->preco_minimo,
            'preco_mediano' => $this->preco_mediano,
            'preco_maximo' => $this->preco_maximo,
            'fonte' => $this->fonte,
            'data_referencia' => $this->data_referencia?->format('Y-m-d'),
            'vigencia_ate' => $this->vigencia_ate?->format('Y-m-d'),
            'is_vigente' => $this->is_vigente,
            'is_ativo' => $this->is_ativo,
            'observacoes' => $this->observacoes,
            'registrador' => $this->when($this->relationLoaded('registrador'), fn () => [
                'id' => $this->registrador?->id,
                'nome' => $this->registrador?->nome,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
