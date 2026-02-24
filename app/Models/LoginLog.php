<?php

namespace App\Models;

use App\Casts\EncryptedWithFallback;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'ip_address',
        'user_agent',
        'success',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'created_at' => 'datetime',
            'ip_address' => EncryptedWithFallback::class,
            'user_agent' => EncryptedWithFallback::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->created_at = $model->freshTimestamp();
        });

        static::updating(function () {
            throw new \RuntimeException('LoginLog é append-only. Updates não são permitidos.');
        });

        static::deleting(function () {
            throw new \RuntimeException('LoginLog é append-only. Deletes não são permitidos.');
        });
    }
}
