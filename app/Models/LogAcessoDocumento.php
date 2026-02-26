<?php

namespace App\Models;

use App\Enums\AcaoLogDocumento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAcessoDocumento extends Model
{
    protected $connection = 'tenant';

    protected $table = 'log_acesso_documentos';

    // Append-only: sem updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'documento_id',
        'user_id',
        'acao',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'acao' => AcaoLogDocumento::class,
        ];
    }

    protected static function booted(): void
    {
        // Tabela imutável — append-only (RN-122, ADR-035)
        static::updating(function () {
            throw new \RuntimeException('Registros de log de acesso a documentos são imutáveis.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Registros de log de acesso a documentos não podem ser excluídos.');
        });
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
