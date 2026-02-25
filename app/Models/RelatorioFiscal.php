<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatorioFiscal extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'relatorios_fiscais';

    protected $fillable = [
        'contrato_id',
        'fiscal_id',
        'periodo_inicio',
        'periodo_fim',
        'descricao_atividades',
        'conformidade_geral',
        'nota_desempenho',
        'ocorrencias_no_periodo',
        'observacoes',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'periodo_inicio' => 'date',
            'periodo_fim' => 'date',
            'conformidade_geral' => 'boolean',
            'nota_desempenho' => 'integer',
            'ocorrencias_no_periodo' => 'integer',
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

    // Scopes

    public function scopeConformes($query)
    {
        return $query->where('conformidade_geral', true);
    }

    public function scopeNaoConformes($query)
    {
        return $query->where('conformidade_geral', false);
    }

    public function scopePorPeriodo($query, string $inicio, string $fim)
    {
        return $query->where('periodo_inicio', '>=', $inicio)
            ->where('periodo_fim', '<=', $fim);
    }
}
