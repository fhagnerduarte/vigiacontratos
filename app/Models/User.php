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
        'mfa_secret',
        'mfa_enabled_at',
        'mfa_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
        'mfa_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_ativo' => 'boolean',
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
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        // Se o tenant desativou MFA, nunca é obrigatório
        if ($tenant && !$tenant->isMfaHabilitado()) {
            return false;
        }

        // Se o tenant tem modo obrigatório global, todos os perfis suportados são obrigatórios
        if ($tenant && $tenant->isMfaObrigatorioGlobal()) {
            return true;
        }

        // Verifica se o perfil está na lista de perfis obrigatórios do tenant
        if ($tenant && $this->role) {
            return $tenant->isMfaObrigatorioParaPerfil($this->role->nome);
        }

        // Fallback: comportamento padrão se não houver tenant no contexto (ex: testes)
        return $this->role && in_array($this->role->nome, [
            'administrador_geral',
            'controladoria',
        ]);
    }

    public function isMfaOptional(): bool
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        // Se o tenant desativou MFA, ninguém tem MFA
        if ($tenant && !$tenant->isMfaHabilitado()) {
            return false;
        }

        // Se MFA está habilitado como opcional no tenant, perfis não-obrigatórios podem usar
        if ($tenant && $tenant->isMfaHabilitado()) {
            // Se já é obrigatório para este perfil, não é "opcional"
            if ($this->isMfaRequired()) {
                return false;
            }

            // Modo opcional: todos os perfis podem configurar MFA
            if ($tenant->mfa_modo === 'opcional') {
                return true;
            }

            // Modo obrigatório: quem não é obrigatório, é opcional
            return $tenant->mfa_modo === 'obrigatorio' ? false : true;
        }

        // Fallback: comportamento padrão
        return $this->role && in_array($this->role->nome, [
            'secretario',
            'gestor_contrato',
            'procuradoria',
            'financeiro',
        ]);
    }

    public function isMfaSupported(): bool
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        // Se o tenant desativou MFA, ninguém suporta MFA
        if ($tenant && !$tenant->isMfaHabilitado()) {
            return false;
        }

        return $this->isMfaRequired() || $this->isMfaOptional();
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
