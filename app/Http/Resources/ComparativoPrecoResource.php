<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComparativoPrecoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contrato' => $this->when($this->relationLoaded('contrato'), fn () => [
                'id' => $this->contrato?->id,
                'numero' => $this->contrato?->numero,
                'objeto' => $this->contrato?->objeto,
            ]),
            'preco_referencial' => $this->when($this->relationLoaded('precoReferencial'), fn () => [
                'id' => $this->precoReferencial?->id,
                'descricao' => $this->precoReferencial?->descricao,
                'categoria_servico' => $this->precoReferencial?->categoria_servico?->value,
            ]),
            'valor_contrato' => $this->valor_contrato,
            'valor_referencia' => $this->valor_referencia,
            'percentual_diferenca' => $this->percentual_diferenca,
            'status_comparativo' => [
                'value' => $this->status_comparativo->value,
                'label' => $this->status_comparativo->label(),
                'cor' => $this->status_comparativo->cor(),
            ],
            'observacoes' => $this->observacoes,
            'gerado_por' => $this->when($this->relationLoaded('geradoPor'), fn () => [
                'id' => $this->geradoPor?->id,
                'nome' => $this->geradoPor?->nome,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
