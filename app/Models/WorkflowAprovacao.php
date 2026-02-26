<?php

namespace App\Models;

use App\Enums\EtapaWorkflow;
use App\Enums\StatusAprovacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowAprovacao extends Model
{
    protected $connection = 'tenant';

    protected $table = 'workflow_aprovacoes';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'aprovavel_type',
        'aprovavel_id',
        'etapa',
        'etapa_ordem',
        'role_responsavel_id',
        'user_id',
        'status',
        'parecer',
        'decided_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'etapa' => EtapaWorkflow::class,
            'status' => StatusAprovacao::class,
            'decided_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (WorkflowAprovacao $modelo) {
            // Permite atualizar apenas se status atual é pendente (para registrar decisão)
            if ($modelo->getRawOriginal('status') !== 'pendente') {
                throw new \RuntimeException('Registros de workflow aprovados/reprovados são imutáveis (RN-336).');
            }
        });

        static::deleting(function () {
            throw new \RuntimeException('Registros de workflow não podem ser excluídos (RN-336).');
        });
    }

    // Relacionamentos

    public function aprovavel(): MorphTo
    {
        return $this->morphTo();
    }

    public function roleResponsavel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_responsavel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
