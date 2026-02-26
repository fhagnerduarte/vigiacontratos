<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecretariaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'sigla' => $this->sigla,
            'responsavel' => $this->responsavel,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'contratos_count' => $this->whenCounted('contratos'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
