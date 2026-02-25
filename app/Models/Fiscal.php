<?php

namespace App\Models;

use App\Casts\EncryptedWithFallback;
use App\Enums\TipoFiscal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fiscal extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'fiscais';

    protected $fillable = [
        'contrato_id',
        'servidor_id',
        'nome',
        'matricula',
        'cargo',
        'email',
        'data_inicio',
        'data_fim',
        'is_atual',
        'tipo_fiscal',
        'portaria_designacao',
        'data_ultimo_relatorio',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'data_ultimo_relatorio' => 'date',
            'is_atual' => 'boolean',
            'email' => EncryptedWithFallback::class,
            'tipo_fiscal' => TipoFiscal::class,
        ];
    }

    // Scopes

    public function scopeTitular(Builder $query): Builder
    {
        return $query->where('tipo_fiscal', 'titular');
    }

    public function scopeSubstituto(Builder $query): Builder
    {
        return $query->where('tipo_fiscal', 'substituto');
    }

    // Relacionamentos

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function servidor(): BelongsTo
    {
        return $this->belongsTo(Servidor::class);
    }
}
