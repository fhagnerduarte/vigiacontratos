<?php

namespace App\Models;

use App\Enums\StatusAditivo;
use App\Enums\TipoAditivo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aditivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'aditivos';

    protected $fillable = [
        'contrato_id',
        'numero_sequencial',
        'tipo',
        'status',
        'data_assinatura',
        'data_inicio_vigencia',
        'nova_data_fim',
        'valor_anterior_contrato',
        'valor_acrescimo',
        'valor_supressao',
        'percentual_acumulado',
        'fundamentacao_legal',
        'justificativa',
        'justificativa_tecnica',
        'justificativa_excesso_limite',
        'justificativa_retroativa',
        'parecer_juridico_obrigatorio',
        'motivo_reequilibrio',
        'indice_utilizado',
        'valor_anterior_reequilibrio',
        'valor_reajustado',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoAditivo::class,
            'status' => StatusAditivo::class,
            'data_assinatura' => 'date',
            'data_inicio_vigencia' => 'date',
            'nova_data_fim' => 'date',
            'valor_anterior_contrato' => 'decimal:2',
            'valor_acrescimo' => 'decimal:2',
            'valor_supressao' => 'decimal:2',
            'percentual_acumulado' => 'decimal:2',
            'valor_anterior_reequilibrio' => 'decimal:2',
            'valor_reajustado' => 'decimal:2',
            'parecer_juridico_obrigatorio' => 'boolean',
        ];
    }

    // Relacionamentos

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    public function documentosVersaoAtual(): MorphMany
    {
        return $this->documentos()->versaoAtual();
    }

    public function historicoAlteracoes(): MorphMany
    {
        return $this->morphMany(HistoricoAlteracao::class, 'auditable');
    }

    public function workflowAprovacoes(): MorphMany
    {
        return $this->morphMany(WorkflowAprovacao::class, 'aprovavel');
    }

    // Accessors

    public function getEtapaAtualWorkflowAttribute(): ?WorkflowAprovacao
    {
        return $this->workflowAprovacoes
            ->where('status', 'pendente')
            ->sortBy('etapa_ordem')
            ->first();
    }

    public function getWorkflowAprovadoAttribute(): bool
    {
        if ($this->workflowAprovacoes->isEmpty()) {
            return false;
        }

        return $this->workflowAprovacoes->every(fn ($etapa) => $etapa->status->value === 'aprovado');
    }
}
