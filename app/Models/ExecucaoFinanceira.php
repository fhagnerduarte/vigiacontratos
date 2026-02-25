<?php

namespace App\Models;

use App\Enums\TipoExecucaoFinanceira;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecucaoFinanceira extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'execucoes_financeiras';

    protected $fillable = [
        'contrato_id',
        'tipo_execucao',
        'descricao',
        'valor',
        'data_execucao',
        'numero_nota_fiscal',
        'numero_empenho',
        'competencia',
        'observacoes',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo_execucao' => TipoExecucaoFinanceira::class,
            'valor' => 'decimal:2',
            'data_execucao' => 'date',
        ];
    }

    // --- Relacionamentos ---

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    // --- Scopes ---

    public function scopePagamentos($query)
    {
        return $query->where('tipo_execucao', TipoExecucaoFinanceira::Pagamento->value);
    }

    public function scopeLiquidacoes($query)
    {
        return $query->where('tipo_execucao', TipoExecucaoFinanceira::Liquidacao->value);
    }

    public function scopeEmpenhos($query)
    {
        return $query->where('tipo_execucao', TipoExecucaoFinanceira::EmpenhoAdicional->value);
    }

    public function scopePorCompetencia($query, string $competencia)
    {
        return $query->where('competencia', $competencia);
    }
}
