<?php

namespace App\Models;

use App\Enums\TipoSolicitacaoLGPD;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogLgpdSolicitacao extends Model
{
    protected $connection = 'tenant';

    protected $table = 'log_lgpd_solicitacoes';

    public $timestamps = false;

    protected $fillable = [
        'tipo_solicitacao',
        'entidade_tipo',
        'entidade_id',
        'solicitante',
        'justificativa',
        'status',
        'campos_anonimizados',
        'executado_por',
        'data_solicitacao',
        'data_execucao',
    ];

    protected function casts(): array
    {
        return [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::class,
            'campos_anonimizados' => 'array',
            'data_solicitacao' => 'datetime',
            'data_execucao' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Tabela log_lgpd_solicitacoes é imutável. UPDATE não permitido.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Tabela log_lgpd_solicitacoes é imutável. DELETE não permitido.');
        });
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executado_por');
    }
}
