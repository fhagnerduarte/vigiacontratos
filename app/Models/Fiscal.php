<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fiscal extends Model
{
    protected $connection = 'tenant';

    protected $table = 'fiscais';

    protected $fillable = [
        'contrato_id',
        'nome',
        'matricula',
        'cargo',
        'email',
        'data_inicio',
        'data_fim',
        'is_atual',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'is_atual' => 'boolean',
        ];
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }
}
