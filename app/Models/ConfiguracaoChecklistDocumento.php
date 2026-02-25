<?php

namespace App\Models;

use App\Enums\FaseContratual;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use Illuminate\Database\Eloquent\Builder;
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
        'fase',
        'descricao',
        'ordem',
        'is_ativo',
    ];

    protected function casts(): array
    {
        return [
            'tipo_contrato' => TipoContrato::class,
            'tipo_documento' => TipoDocumentoContratual::class,
            'fase' => FaseContratual::class,
            'is_ativo' => 'boolean',
            'ordem' => 'integer',
        ];
    }

    public function scopeFase(Builder $query, FaseContratual $fase): Builder
    {
        return $query->where('fase', $fase->value);
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('is_ativo', true);
    }
}
