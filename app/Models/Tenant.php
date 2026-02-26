<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'slug',
        'database_name',
        'database_host',
        'is_ativo',
        'plano',
        'logo_path',
        'cor_primaria',
        'cor_secundaria',
        'endereco',
        'telefone',
        'email_contato',
        'horario_atendimento',
        'cnpj',
        'gestor_nome',
        'mfa_habilitado',
        'mfa_modo',
        'mfa_perfis_obrigatorios',
    ];

    protected function casts(): array
    {
        return [
            'is_ativo' => 'boolean',
            'mfa_habilitado' => 'boolean',
            'mfa_perfis_obrigatorios' => 'array',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk('s3')->url($this->logo_path);
    }

    /**
     * Verifica se o MFA está habilitado para este tenant.
     */
    public function isMfaHabilitado(): bool
    {
        return $this->mfa_habilitado;
    }

    /**
     * Verifica se o MFA é obrigatório para todos os perfis suportados.
     */
    public function isMfaObrigatorioGlobal(): bool
    {
        return $this->mfa_habilitado && $this->mfa_modo === 'obrigatorio';
    }

    /**
     * Verifica se o MFA é obrigatório para um perfil específico.
     */
    public function isMfaObrigatorioParaPerfil(string $perfil): bool
    {
        if (!$this->mfa_habilitado) {
            return false;
        }

        if ($this->mfa_modo === 'obrigatorio') {
            return true;
        }

        $perfisObrigatorios = $this->mfa_perfis_obrigatorios ?? [];

        return in_array($perfil, $perfisObrigatorios);
    }

    /**
     * Verifica se o MFA é suportado (habilitado, opcional ou obrigatório) para o tenant.
     */
    public function isMfaDisponivel(): bool
    {
        return $this->mfa_habilitado && $this->mfa_modo !== 'desativado';
    }
}
