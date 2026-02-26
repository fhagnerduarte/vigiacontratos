<?php

namespace App\Models;

use App\Enums\CanalNotificacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogNotificacao extends Model
{
    protected $connection = 'tenant';

    protected $table = 'log_notificacoes';

    // Append-only: sem updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'alerta_id',
        'canal',
        'destinatario',
        'data_envio',
        'sucesso',
        'resposta_gateway',
        'tentativa_numero',
    ];

    protected function casts(): array
    {
        return [
            'canal' => CanalNotificacao::class,
            'data_envio' => 'datetime',
            'sucesso' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Tabela imutável — append-only (RN-049)
        static::updating(function () {
            throw new \RuntimeException('Registros de log de notificação são imutáveis.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Registros de log de notificação não podem ser excluídos.');
        });
    }

    public function alerta(): BelongsTo
    {
        return $this->belongsTo(Alerta::class);
    }
}
