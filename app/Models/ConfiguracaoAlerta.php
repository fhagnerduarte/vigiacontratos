<?php

namespace App\Models;

use App\Enums\PrioridadeAlerta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoAlerta extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'configuracoes_alerta';

    protected $fillable = [
        'dias_antecedencia',
        'prioridade_padrao',
        'is_ativo',
    ];

    protected function casts(): array
    {
        return [
            'prioridade_padrao' => PrioridadeAlerta::class,
            'is_ativo' => 'boolean',
        ];
    }
}
