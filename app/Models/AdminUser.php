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
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'mfa_secret',
    ];

    protected function casts(): array
    {
        return [
            'is_ativo' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(AdminLoginLog::class);
    }
}
