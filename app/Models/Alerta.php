<?php

namespace App\Models;

use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\TipoEventoAlerta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alerta extends Model
{
    protected $connection = 'tenant';

    protected $table = 'alertas';

    protected $fillable = [
        'contrato_id',
        'tipo_evento',
        'prioridade',
        'status',
        'dias_para_vencimento',
        'dias_antecedencia_config',
        'data_vencimento',
        'data_disparo',
        'mensagem',
        'tentativas_envio',
        'visualizado_por',
        'visualizado_em',
        'resolvido_por',
        'resolvido_em',
    ];

    protected function casts(): array
    {
        return [
            'tipo_evento' => TipoEventoAlerta::class,
            'prioridade' => PrioridadeAlerta::class,
            'status' => StatusAlerta::class,
            'data_vencimento' => 'date',
            'data_disparo' => 'datetime',
            'visualizado_em' => 'datetime',
            'resolvido_em' => 'datetime',
        ];
    }

    // --- Relacionamentos ---

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function visualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visualizado_por');
    }

    public function resolvidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolvido_por');
    }

    public function logNotificacoes(): HasMany
    {
        return $this->hasMany(LogNotificacao::class);
    }

    // --- Scopes ---

    public function scopePendentes($query)
    {
        return $query->whereIn('status', [
            StatusAlerta::Pendente->value,
            StatusAlerta::Enviado->value,
            StatusAlerta::Visualizado->value,
        ]);
    }

    public function scopeNaoResolvidos($query)
    {
        return $query->where('status', '!=', StatusAlerta::Resolvido->value);
    }
}
