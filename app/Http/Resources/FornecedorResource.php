<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FornecedorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'razao_social' => $this->razao_social,
            'nome_fantasia' => $this->nome_fantasia,
            'cnpj' => $this->cnpj,
            'endereco' => $this->endereco,
            'cidade' => $this->cidade,
            'uf' => $this->uf,
            'cep' => $this->cep,
            'observacoes' => $this->observacoes,
            'contratos_count' => $this->whenCounted('contratos'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
