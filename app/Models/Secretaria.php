<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Secretaria extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'nome',
        'sigla',
        'responsavel',
        'email',
        'telefone',
    ];

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    public function servidores(): HasMany
    {
        return $this->hasMany(Servidor::class);
    }
}
