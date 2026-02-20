<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'database_name',
        'database_host',
        'is_ativo',
        'plano',
    ];

    protected function casts(): array
    {
        return [
            'is_ativo' => 'boolean',
        ];
    }
}
