<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardAgregado extends Model
{
    protected $connection = 'tenant';

    protected $table = 'dashboard_agregados';

    protected $fillable = [
        'data_agregacao',
        'total_contratos_ativos',
        'valor_total_contratado',
        'valor_total_executado',
        'saldo_remanescente',
        'ticket_medio',
        'risco_baixo',
        'risco_medio',
        'risco_alto',
        'vencendo_0_30d',
        'vencendo_31_60d',
        'vencendo_61_90d',
        'vencendo_91_120d',
        'vencendo_120p',
        'score_gestao',
        'dados_completos',
    ];

    protected function casts(): array
    {
        return [
            'data_agregacao' => 'date',
            'valor_total_contratado' => 'decimal:2',
            'valor_total_executado' => 'decimal:2',
            'saldo_remanescente' => 'decimal:2',
            'ticket_medio' => 'decimal:2',
            'dados_completos' => 'array',
        ];
    }
}
