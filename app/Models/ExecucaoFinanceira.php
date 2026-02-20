<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecucaoFinanceira extends Model
{
    protected $connection = 'tenant';

    protected $table = 'execucoes_financeiras';

    protected $fillable = [
        'contrato_id',
        'descricao',
        'valor',
        'data_execucao',
        'numero_nota_fiscal',
        'observacoes',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data_execucao' => 'date',
        ];
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
