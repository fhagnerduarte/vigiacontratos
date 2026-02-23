<?php

namespace App\Models;

use App\Enums\CategoriaContrato;
use App\Enums\CategoriaServico;
use App\Enums\ModalidadeContratacao;
use App\Enums\NivelRisco;
use App\Enums\StatusCompletudeDocumental;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Enums\TipoPagamento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrato extends Model
{
    use HasFactory, SoftDeletes;

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
        'servidor_id',
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

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(Servidor::class, 'servidor_id');
    }

    public function fiscais(): HasMany
    {
        return $this->hasMany(Fiscal::class);
    }

    public function fiscalAtual(): HasOne
    {
        return $this->hasOne(Fiscal::class)->where('is_atual', true);
    }

    public function aditivos(): HasMany
    {
        return $this->hasMany(Aditivo::class);
    }

    public function aditivosVigentes(): HasMany
    {
        return $this->hasMany(Aditivo::class)->where('status', 'vigente');
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

    public function documentosVersaoAtual(): MorphMany
    {
        return $this->documentos()->versaoAtual();
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    public function alertasPendentes(): HasMany
    {
        return $this->hasMany(Alerta::class)->whereIn('status', ['pendente', 'enviado', 'visualizado']);
    }

    // Accessors

    public function getDiasParaVencimentoAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->data_fim->startOfDay(), false);
    }

    /**
     * Calcula a completude documental do contrato (RN-128).
     *
     * Checklist obrigatorio padrao: contrato_original, publicacao_oficial, parecer_juridico, nota_empenho (RN-129).
     */
    public function getStatusCompletudeAttribute(): StatusCompletudeDocumental
    {
        $checklistObrigatorio = [
            TipoDocumentoContratual::ContratoOriginal,
            TipoDocumentoContratual::PublicacaoOficial,
            TipoDocumentoContratual::ParecerJuridico,
            TipoDocumentoContratual::NotaEmpenho,
        ];

        $tiposPresentes = $this->documentos
            ->where('is_versao_atual', true)
            ->whereNull('deleted_at')
            ->pluck('tipo_documento')
            ->unique()
            ->values();

        $tiposObrigatoriosPresentes = collect($checklistObrigatorio)->filter(
            fn (TipoDocumentoContratual $tipo) => $tiposPresentes->contains($tipo)
        );

        if ($tiposObrigatoriosPresentes->count() === count($checklistObrigatorio)) {
            return StatusCompletudeDocumental::Completo;
        }

        if ($tiposPresentes->contains(TipoDocumentoContratual::ContratoOriginal)) {
            return StatusCompletudeDocumental::Parcial;
        }

        return StatusCompletudeDocumental::Incompleto;
    }
}
