<?php

namespace App\Models;

use App\Casts\EncryptedWithFallback;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLoginLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_user_id',
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

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->created_at = $model->freshTimestamp();
        });

        static::updating(function () {
            throw new \RuntimeException('AdminLoginLog é append-only. Updates não são permitidos.');
        });

        static::deleting(function () {
            throw new \RuntimeException('AdminLoginLog é append-only. Deletes não são permitidos.');
        });
    }
}
