<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServidorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'matricula' => $this->matricula,
            'cargo' => $this->cargo,
            'secretaria_id' => $this->secretaria_id,
            'is_ativo' => $this->is_ativo,
            'observacoes' => $this->observacoes,
            'secretaria' => new SecretariaResource($this->whenLoaded('secretaria')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
