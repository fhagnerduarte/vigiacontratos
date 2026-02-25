<?php

namespace App\Models;

use App\Enums\TipoOcorrencia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ocorrencia extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'ocorrencias';

    protected $fillable = [
        'contrato_id',
        'fiscal_id',
        'data_ocorrencia',
        'tipo_ocorrencia',
        'descricao',
        'providencia',
        'prazo_providencia',
        'resolvida',
        'resolvida_em',
        'resolvida_por',
        'observacoes',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo_ocorrencia' => TipoOcorrencia::class,
            'data_ocorrencia' => 'date',
            'prazo_providencia' => 'date',
            'resolvida' => 'boolean',
            'resolvida_em' => 'datetime',
        ];
    }

    // Relacionamentos

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function fiscal(): BelongsTo
    {
        return $this->belongsTo(Fiscal::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function resolvidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolvida_por');
    }

    // Scopes

    public function scopePendentes($query)
    {
        return $query->where('resolvida', false);
    }

    public function scopeResolvidas($query)
    {
        return $query->where('resolvida', true);
    }

    public function scopePorTipo($query, TipoOcorrencia $tipo)
    {
        return $query->where('tipo_ocorrencia', $tipo->value);
    }

    public function scopeVencidas($query)
    {
        return $query->where('resolvida', false)
            ->whereNotNull('prazo_providencia')
            ->where('prazo_providencia', '<', now()->toDateString());
    }
}
