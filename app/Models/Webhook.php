<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'url',
        'eventos',
        'secret',
        'is_ativo',
        'descricao',
        'ultimo_disparo_em',
        'ultimo_status_code',
        'falhas_consecutivas',
    ];

    protected function casts(): array
    {
        return [
            'eventos' => 'array',
            'is_ativo' => 'boolean',
            'ultimo_disparo_em' => 'datetime',
            'falhas_consecutivas' => 'integer',
        ];
    }

    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true);
    }

    public function scopeParaEvento($query, string $evento)
    {
        return $query->whereJsonContains('eventos', $evento);
    }

    public function escutaEvento(string $evento): bool
    {
        return in_array($evento, $this->eventos ?? []);
    }
}
