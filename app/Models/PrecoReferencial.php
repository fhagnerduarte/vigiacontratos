<?php

namespace App\Models;

use App\Enums\CategoriaServico;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrecoReferencial extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'precos_referenciais';

    protected $fillable = [
        'descricao',
        'categoria_servico',
        'unidade_medida',
        'preco_minimo',
        'preco_mediano',
        'preco_maximo',
        'fonte',
        'data_referencia',
        'vigencia_ate',
        'observacoes',
        'registrado_por',
        'is_ativo',
    ];

    protected function casts(): array
    {
        return [
            'categoria_servico' => CategoriaServico::class,
            'preco_minimo' => 'decimal:2',
            'preco_mediano' => 'decimal:2',
            'preco_maximo' => 'decimal:2',
            'data_referencia' => 'date',
            'vigencia_ate' => 'date',
            'is_ativo' => 'boolean',
        ];
    }

    // Relationships

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function comparativos(): HasMany
    {
        return $this->hasMany(ComparativoPreco::class);
    }

    // Scopes

    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true);
    }

    public function scopePorCategoria($query, CategoriaServico $categoria)
    {
        return $query->where('categoria_servico', $categoria->value);
    }

    public function scopeVigentes($query)
    {
        return $query->where('is_ativo', true)
            ->where(function ($q) {
                $q->whereNull('vigencia_ate')
                    ->orWhere('vigencia_ate', '>=', now()->toDateString());
            });
    }

    // Accessors

    public function getIsVigenteAttribute(): bool
    {
        if (! $this->is_ativo) {
            return false;
        }

        return $this->vigencia_ate === null || $this->vigencia_ate->gte(now()->startOfDay());
    }
}
