<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'database_name',
        'database_host',
        'is_ativo',
        'plano',
    ];

    protected $casts = [
        'is_ativo' => 'boolean',
    ];

    public function tenantUsers(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }
}
