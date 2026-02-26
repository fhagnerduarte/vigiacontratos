<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HistoricoAlteracao extends Model
{
    protected $connection = 'tenant';

    protected $table = 'historico_alteracoes';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'campo_alterado',
        'valor_anterior',
        'valor_novo',
        'user_id',
        'role_nome',
        'ip_address',
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
            throw new \RuntimeException('Registros de auditoria são imutáveis (RN-037).');
        });

        static::deleting(function () {
            throw new \RuntimeException('Registros de auditoria não podem ser excluídos (RN-037).');
        });
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
