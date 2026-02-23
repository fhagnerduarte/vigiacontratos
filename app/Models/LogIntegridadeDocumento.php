<?php

namespace App\Models;

use App\Enums\StatusIntegridade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogIntegridadeDocumento extends Model
{
    protected $connection = 'tenant';

    protected $table = 'log_integridade_documentos';

    public $timestamps = false;

    protected $fillable = [
        'documento_id',
        'hash_esperado',
        'hash_calculado',
        'status',
        'detectado_em',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusIntegridade::class,
            'detectado_em' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->created_at = $model->freshTimestamp();
        });

        static::updating(function () {
            throw new \RuntimeException(
                'Registros de log de integridade são imutáveis.'
            );
        });

        static::deleting(function () {
            throw new \RuntimeException(
                'Registros de log de integridade não podem ser excluídos.'
            );
        });
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }
}
