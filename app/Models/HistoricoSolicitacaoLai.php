<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoSolicitacaoLai extends Model
{
    protected $connection = 'tenant';
    protected $table = 'historico_solicitacoes_lai';

    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'solicitacao_lai_id',
        'status_anterior',
        'status_novo',
        'observacao',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Histórico de solicitações LAI é imutável (LAI 12.527/2011).');
        });

        static::deleting(function () {
            throw new \RuntimeException('Histórico de solicitações LAI não pode ser excluído (LAI 12.527/2011).');
        });
    }

    // ── Relationships ──────────────────────────────────────────────

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoLai::class, 'solicitacao_lai_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
