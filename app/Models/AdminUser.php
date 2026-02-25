<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $guard = 'admin';

    protected $fillable = [
        'nome',
        'email',
        'password',
        'is_ativo',
        'mfa_secret',
        'mfa_enabled_at',
        'mfa_recovery_codes',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'mfa_secret',
        'mfa_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'is_ativo' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'mfa_enabled_at' => 'datetime',
            'mfa_recovery_codes' => 'encrypted',
        ];
    }

    public function isMfaEnabled(): bool
    {
        return $this->mfa_enabled_at !== null;
    }

    public function isMfaRequired(): bool
    {
        return false;
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(AdminLoginLog::class);
    }
}
