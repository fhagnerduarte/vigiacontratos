<?php

namespace App\Models;

use App\Casts\EncryptedWithFallback;
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
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'is_atual' => 'boolean',
            'email' => EncryptedWithFallback::class,
        ];
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function servidor(): BelongsTo
    {
        return $this->belongsTo(Servidor::class);
    }
}
