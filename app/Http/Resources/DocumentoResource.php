<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_documento' => $this->formatEnum($this->tipo_documento),
            'nome_original' => $this->nome_original,
            'descricao' => $this->descricao,
            'mime_type' => $this->mime_type,
            'tamanho' => $this->tamanho,
            'versao' => $this->versao,
            'is_versao_atual' => $this->is_versao_atual,
            'integridade_comprometida' => $this->integridade_comprometida,
            'classificacao_sigilo' => $this->formatEnum($this->classificacao_sigilo),
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
