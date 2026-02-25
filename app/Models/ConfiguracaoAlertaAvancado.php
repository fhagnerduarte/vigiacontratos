<?php

namespace App\Models;

use App\Enums\TipoEventoAlerta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoAlertaAvancado extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'configuracoes_alerta_avancado';

    protected $fillable = [
        'tipo_evento',
        'dias_inatividade',
        'dias_sem_relatorio',
        'percentual_limite_valor',
        'is_ativo',
    ];

    protected function casts(): array
    {
        return [
            'tipo_evento' => TipoEventoAlerta::class,
            'is_ativo' => 'boolean',
            'percentual_limite_valor' => 'decimal:2',
        ];
    }

    // Scopes

    public function scopeAtivos($query)
    {
        return $query->where('is_ativo', true);
    }

    public function scopePorTipo($query, TipoEventoAlerta $tipo)
    {
        return $query->where('tipo_evento', $tipo->value);
    }
}
