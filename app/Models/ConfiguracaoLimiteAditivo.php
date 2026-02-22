<?php

namespace App\Models;

use App\Enums\TipoContrato;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoLimiteAditivo extends Model
{
    protected $connection = 'tenant';

    protected $table = 'configuracoes_limite_aditivo';

    protected $fillable = [
        'tipo_contrato',
        'percentual_limite',
        'is_bloqueante',
        'is_ativo',
    ];

    protected function casts(): array
    {
        return [
            'tipo_contrato' => TipoContrato::class,
            'percentual_limite' => 'decimal:2',
            'is_bloqueante' => 'boolean',
            'is_ativo' => 'boolean',
        ];
    }
}
