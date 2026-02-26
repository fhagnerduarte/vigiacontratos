<?php

namespace App\Models;

use App\Enums\FormatoExportacaoTce;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportacaoTce extends Model
{
    protected $connection = 'tenant';

    protected $table = 'exportacoes_tce';

    protected $fillable = [
        'formato',
        'filtros',
        'total_contratos',
        'total_pendencias',
        'arquivo_path',
        'arquivo_nome',
        'gerado_por',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'formato' => FormatoExportacaoTce::class,
            'filtros' => 'array',
            'total_contratos' => 'integer',
            'total_pendencias' => 'integer',
        ];
    }

    public function geradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gerado_por');
    }
}
