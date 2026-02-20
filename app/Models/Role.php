<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'nome',
        'descricao',
        'is_padrao',
        'is_ativo',
    ];

    protected function casts(): array
    {
        return [
            'is_padrao' => 'boolean',
            'is_ativo' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
