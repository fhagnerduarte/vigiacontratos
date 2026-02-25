<?php

namespace App\Models;

use App\Enums\EtapaEncerramento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Encerramento extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'encerramentos';

    protected $fillable = [
        'contrato_id',
        'etapa_atual',
        'data_inicio',
        'verificacao_financeira_ok',
        'verificacao_financeira_por',
        'verificacao_financeira_em',
        'verificacao_financeira_obs',
        'termo_provisorio_em',
        'termo_provisorio_por',
        'termo_provisorio_prazo_dias',
        'avaliacao_fiscal_nota',
        'avaliacao_fiscal_obs',
        'avaliacao_fiscal_por',
        'avaliacao_fiscal_em',
        'termo_definitivo_em',
        'termo_definitivo_por',
        'quitacao_em',
        'quitacao_por',
        'quitacao_obs',
        'data_encerramento_efetivo',
    ];

    protected function casts(): array
    {
        return [
            'etapa_atual' => EtapaEncerramento::class,
            'verificacao_financeira_ok' => 'boolean',
            'verificacao_financeira_em' => 'datetime',
            'termo_provisorio_em' => 'datetime',
            'termo_provisorio_prazo_dias' => 'integer',
            'avaliacao_fiscal_nota' => 'decimal:1',
            'avaliacao_fiscal_em' => 'datetime',
            'termo_definitivo_em' => 'datetime',
            'quitacao_em' => 'datetime',
            'data_inicio' => 'datetime',
            'data_encerramento_efetivo' => 'date',
        ];
    }

    // Relacionamentos

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function verificadorFinanceiro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificacao_financeira_por');
    }

    public function registradorTermoProvisorio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'termo_provisorio_por');
    }

    public function avaliadorFiscal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'avaliacao_fiscal_por');
    }

    public function registradorTermoDefinitivo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'termo_definitivo_por');
    }

    public function registradorQuitacao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quitacao_por');
    }

    // Accessors

    public function getEtapaConcluidaAttribute(): bool
    {
        return $this->etapa_atual === EtapaEncerramento::Encerrado;
    }

    public function getPercentualProgressoAttribute(): float
    {
        $ordem = $this->etapa_atual->ordem();

        // Se encerrado, 100%. Senao, (etapa - 1) / 6 * 100
        if ($this->etapa_atual === EtapaEncerramento::Encerrado) {
            return 100.0;
        }

        return round(($ordem - 1) / 6 * 100, 1);
    }
}
