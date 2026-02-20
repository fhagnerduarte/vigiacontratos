<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
