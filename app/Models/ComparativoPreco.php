<?php

namespace App\Models;

use App\Enums\StatusComparativoPreco;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComparativoPreco extends Model
{
    protected $connection = 'tenant';

    protected $table = 'comparativos_preco';

    protected $fillable = [
        'contrato_id',
        'preco_referencial_id',
        'valor_contrato',
        'valor_referencia',
        'percentual_diferenca',
        'status_comparativo',
        'observacoes',
        'gerado_por',
    ];

    protected function casts(): array
    {
        return [
            'status_comparativo' => StatusComparativoPreco::class,
            'valor_contrato' => 'decimal:2',
            'valor_referencia' => 'decimal:2',
            'percentual_diferenca' => 'decimal:2',
        ];
    }

    // Relationships

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function precoReferencial(): BelongsTo
    {
        return $this->belongsTo(PrecoReferencial::class);
    }

    public function geradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gerado_por');
    }
}
