<?php

namespace App\Models;

use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoChecklistDocumento extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'configuracoes_checklist_documento';

    protected $fillable = [
        'tipo_contrato',
        'tipo_documento',
        'is_ativo',
    ];

    protected function casts(): array
    {
        return [
            'tipo_contrato' => TipoContrato::class,
            'tipo_documento' => TipoDocumentoContratual::class,
            'is_ativo' => 'boolean',
        ];
    }
}
