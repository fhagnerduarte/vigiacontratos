<?php

namespace App\Models;

use App\Enums\DatasetDadosAbertos;
use App\Enums\FormatoDadosAbertos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportacaoDadosAbertos extends Model
{
    protected $connection = 'tenant';

    protected $table = 'exportacoes_dados_abertos';

    protected $fillable = [
        'dataset',
        'formato',
        'filtros',
        'total_registros',
        'solicitado_por',
        'ip_solicitante',
    ];

    protected function casts(): array
    {
        return [
            'dataset' => DatasetDadosAbertos::class,
            'formato' => FormatoDadosAbertos::class,
            'filtros' => 'array',
            'total_registros' => 'integer',
        ];
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }
}
