<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servidor extends Model
{
    protected $connection = 'tenant';

    protected $table = 'servidores';

    protected $fillable = [
        'nome',
        'cpf',
        'matricula',
        'cargo',
        'secretaria_id',
        'email',
        'telefone',
        'is_ativo',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'is_ativo' => 'boolean',
        ];
    }

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    public function fiscais(): HasMany
    {
        return $this->hasMany(Fiscal::class);
    }
}
