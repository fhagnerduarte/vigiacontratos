<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $connection = 'tenant';

    protected $fillable = [
        'nome',
        'email',
        'password',
        'role_id',
        'is_ativo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_ativo' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function secretarias(): BelongsToMany
    {
        return $this->belongsToMany(Secretaria::class, 'user_secretarias');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot('expires_at', 'concedido_por');
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    /**
     * Verifica se o usuario possui uma permissao especifica.
     * Checa via role_permissions + user_permissions com verificacao real-time de expires_at.
     * (RN-303, RN-330)
     */
    public function hasPermission(string $permission): bool
    {
        // Administrador geral tem acesso total (RN-305)
        if ($this->hasRole('administrador_geral')) {
            return true;
        }

        // 1. Verificar via role (permissoes do perfil — sem expiracao)
        if ($this->role && $this->role->permissions()->where('nome', $permission)->exists()) {
            return true;
        }

        // 2. Verificar permissao individual — com verificacao de expiracao em tempo real
        return $this->permissions()
            ->where('nome', $permission)
            ->where(function ($query) {
                $query->whereNull('user_permissions.expires_at')
                      ->orWhere('user_permissions.expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Verifica se o usuario possui um role especifico pelo nome.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->nome === $roleName;
    }

    /**
     * Verifica se o usuario possui um perfil estrategico (acesso a todas secretarias).
     * (RN-327)
     */
    public function isPerfilEstrategico(): bool
    {
        if (! $this->role) {
            return false;
        }

        return in_array($this->role->nome, [
            'administrador_geral',
            'controladoria',
            'gabinete',
        ]);
    }
}
