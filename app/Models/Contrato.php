<?php

namespace App\Models;

use App\Enums\CategoriaContrato;
use App\Enums\CategoriaServico;
use App\Enums\ModalidadeContratacao;
use App\Enums\NivelRisco;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Enums\TipoPagamento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrato extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'contratos';

    protected $fillable = [
        'numero',
        'ano',
        'objeto',
        'tipo',
        'status',
        'modalidade_contratacao',
        'fornecedor_id',
        'secretaria_id',
        'unidade_gestora',
        'data_inicio',
        'data_fim',
        'prazo_meses',
        'prorrogacao_automatica',
        'valor_global',
        'valor_mensal',
        'tipo_pagamento',
        'fonte_recurso',
        'dotacao_orcamentaria',
        'numero_empenho',
        'numero_processo',
        'fundamento_legal',
        'categoria',
        'categoria_servico',
        'responsavel_tecnico',
        'gestor_nome',
        'score_risco',
        'nivel_risco',
        'percentual_executado',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoContrato::class,
            'status' => StatusContrato::class,
            'modalidade_contratacao' => ModalidadeContratacao::class,
            'tipo_pagamento' => TipoPagamento::class,
            'categoria' => CategoriaContrato::class,
            'categoria_servico' => CategoriaServico::class,
            'nivel_risco' => NivelRisco::class,
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'prorrogacao_automatica' => 'boolean',
            'valor_global' => 'decimal:2',
            'valor_mensal' => 'decimal:2',
            'percentual_executado' => 'decimal:2',
        ];
    }

    // Relacionamentos

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class);
    }

    public function fiscais(): HasMany
    {
        return $this->hasMany(Fiscal::class);
    }

    public function fiscalAtual(): HasOne
    {
        return $this->hasOne(Fiscal::class)->where('is_atual', true);
    }

    public function execucoesFinanceiras(): HasMany
    {
        return $this->hasMany(ExecucaoFinanceira::class);
    }

    public function historicoAlteracoes(): MorphMany
    {
        return $this->morphMany(HistoricoAlteracao::class, 'auditable');
    }

    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    // Accessors

    public function getDiasParaVencimentoAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->data_fim->startOfDay(), false);
    }
}
