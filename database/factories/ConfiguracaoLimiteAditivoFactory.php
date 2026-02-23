<?php

namespace Database\Factories;

use App\Enums\TipoContrato;
use App\Models\ConfiguracaoLimiteAditivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConfiguracaoLimiteAditivo>
 */
class ConfiguracaoLimiteAditivoFactory extends Factory
{
    protected $model = ConfiguracaoLimiteAditivo::class;

    public function definition(): array
    {
        return [
            'tipo_contrato' => fake()->unique()->randomElement(TipoContrato::cases()),
            'percentual_limite' => 25.00,
            'is_bloqueante' => false,
            'is_ativo' => true,
        ];
    }

    public function bloqueante(): static
    {
        return $this->state(fn () => ['is_bloqueante' => true]);
    }

    public function obra(): static
    {
        return $this->state(fn () => [
            'tipo_contrato' => TipoContrato::Obra,
            'percentual_limite' => 50.00,
        ]);
    }
}
