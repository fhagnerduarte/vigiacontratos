<?php

namespace App\Models;

use App\Enums\FaseContratual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContratoConformidadeFase extends Model
{
    protected $connection = 'tenant';

    protected $table = 'contrato_conformidade_fases';

    protected $fillable = [
        'contrato_id',
        'fase',
        'percentual_conformidade',
        'total_obrigatorios',
        'total_presentes',
        'nivel_semaforo',
    ];

    protected function casts(): array
    {
        return [
            'fase' => FaseContratual::class,
            'percentual_conformidade' => 'decimal:2',
            'total_obrigatorios' => 'integer',
            'total_presentes' => 'integer',
        ];
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }
}
