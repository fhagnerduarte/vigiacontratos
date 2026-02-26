<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContratoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'ano' => $this->ano,
            'objeto' => $this->objeto,
            'tipo' => $this->formatEnum($this->tipo),
            'status' => $this->formatEnum($this->status),
            'is_irregular' => $this->is_irregular,
            'modalidade_contratacao' => $this->formatEnum($this->modalidade_contratacao),
            'regime_execucao' => $this->formatEnum($this->regime_execucao),
            'data_inicio' => $this->data_inicio?->toDateString(),
            'data_assinatura' => $this->data_assinatura?->toDateString(),
            'data_fim' => $this->data_fim?->toDateString(),
            'prazo_meses' => $this->prazo_meses,
            'prorrogacao_automatica' => $this->prorrogacao_automatica,
            'valor_global' => $this->valor_global,
            'valor_mensal' => $this->valor_mensal,
            'tipo_pagamento' => $this->formatEnum($this->tipo_pagamento),
            'fonte_recurso' => $this->fonte_recurso,
            'dotacao_orcamentaria' => $this->dotacao_orcamentaria,
            'numero_empenho' => $this->numero_empenho,
            'numero_processo' => $this->numero_processo,
            'fundamento_legal' => $this->fundamento_legal,
            'categoria' => $this->formatEnum($this->categoria),
            'categoria_servico' => $this->formatEnum($this->categoria_servico),
            'score_risco' => $this->score_risco,
            'nivel_risco' => $this->formatEnum($this->nivel_risco),
            'percentual_executado' => $this->percentual_executado,
            'valor_empenhado' => $this->valor_empenhado,
            'saldo_contratual' => $this->saldo_contratual,
            'classificacao_sigilo' => $this->formatEnum($this->classificacao_sigilo),
            'publicado_portal' => $this->publicado_portal,
            'data_publicacao' => $this->data_publicacao?->toDateString(),
            'observacoes' => $this->observacoes,
            'fornecedor' => new FornecedorResource($this->whenLoaded('fornecedor')),
            'secretaria' => new SecretariaResource($this->whenLoaded('secretaria')),
            'gestor' => new ServidorResource($this->whenLoaded('gestor')),
            'fiscal_atual' => new FiscalResource($this->whenLoaded('fiscalAtual')),
            'aditivos' => AditivoResource::collection($this->whenLoaded('aditivos')),
            'documentos' => DocumentoResource::collection($this->whenLoaded('documentosVersaoAtual')),
            'alertas_pendentes_count' => $this->whenLoaded('alertasPendentes', fn () => $this->alertasPendentes->count()),
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
