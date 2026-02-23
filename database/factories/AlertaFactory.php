<?php

namespace Database\Factories;

use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\TipoEventoAlerta;
use App\Models\Alerta;
use App\Models\Contrato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alerta>
 */
class AlertaFactory extends Factory
{
    protected $model = Alerta::class;

    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'tipo_evento' => TipoEventoAlerta::VencimentoVigencia,
            'prioridade' => PrioridadeAlerta::Informativo,
            'status' => StatusAlerta::Pendente,
            'dias_para_vencimento' => 60,
            'dias_antecedencia_config' => 60,
            'data_vencimento' => now()->addDays(60)->format('Y-m-d'),
            'data_disparo' => now(),
            'mensagem' => 'Contrato vencendo em 60 dias.',
            'tentativas_envio' => 0,
            'visualizado_por' => null,
            'visualizado_em' => null,
            'resolvido_por' => null,
            'resolvido_em' => null,
        ];
    }

    public function urgente(): static
    {
        return $this->state(fn () => [
            'prioridade' => PrioridadeAlerta::Urgente,
            'dias_para_vencimento' => 7,
            'dias_antecedencia_config' => 7,
            'data_vencimento' => now()->addDays(7)->format('Y-m-d'),
        ]);
    }

    public function resolvido(): static
    {
        return $this->state(fn () => [
            'status' => StatusAlerta::Resolvido,
            'resolvido_em' => now(),
        ]);
    }

    public function enviado(): static
    {
        return $this->state(fn () => [
            'status' => StatusAlerta::Enviado,
            'tentativas_envio' => 1,
        ]);
    }
}
