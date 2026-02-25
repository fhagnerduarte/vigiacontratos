<?php

namespace App\Models;

use App\Enums\ClassificacaoRespostaLai;
use App\Enums\StatusSolicitacaoLai;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolicitacaoLai extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'solicitacoes_lai';

    protected $fillable = [
        'protocolo',
        'nome_solicitante',
        'email_solicitante',
        'cpf_solicitante',
        'telefone_solicitante',
        'assunto',
        'descricao',
        'status',
        'classificacao_resposta',
        'resposta',
        'respondido_por',
        'data_resposta',
        'data_prorrogacao',
        'justificativa_prorrogacao',
        'prazo_legal',
        'prazo_estendido',
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusSolicitacaoLai::class,
            'classificacao_resposta' => ClassificacaoRespostaLai::class,
            'cpf_solicitante' => 'encrypted',
            'data_resposta' => 'datetime',
            'data_prorrogacao' => 'datetime',
            'prazo_legal' => 'date',
            'prazo_estendido' => 'date',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoSolicitacaoLai::class);
    }

    public function respondente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondido_por');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopePendentes($query)
    {
        return $query->whereNotIn('status', [
            StatusSolicitacaoLai::Respondida->value,
            StatusSolicitacaoLai::Indeferida->value,
        ]);
    }

    public function scopeVencidas($query)
    {
        return $query->pendentes()
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('prazo_estendido')
                        ->where('prazo_legal', '<', now()->toDateString());
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('prazo_estendido')
                        ->where('prazo_estendido', '<', now()->toDateString());
                });
            });
    }

    public function scopePorStatus($query, StatusSolicitacaoLai $status)
    {
        return $query->where('status', $status->value);
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getDiasRestantesAttribute(): int
    {
        $prazo = $this->prazo_estendido ?? $this->prazo_legal;

        if (!$prazo) {
            return 0;
        }

        return (int) now()->startOfDay()->diffInDays($prazo->startOfDay(), false);
    }

    public function getIsVencidaAttribute(): bool
    {
        if ($this->status?->isFinalizado()) {
            return false;
        }

        return $this->dias_restantes < 0;
    }

    public function getIsProrrogavelAttribute(): bool
    {
        if (!$this->status?->permiteProrrogacao()) {
            return false;
        }

        return is_null($this->prazo_estendido);
    }
}
