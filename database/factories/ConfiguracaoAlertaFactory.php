<?php

namespace Database\Factories;

use App\Enums\PrioridadeAlerta;
use App\Models\ConfiguracaoAlerta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConfiguracaoAlerta>
 */
class ConfiguracaoAlertaFactory extends Factory
{
    protected $model = ConfiguracaoAlerta::class;

    public function definition(): array
    {
        return [
            'dias_antecedencia' => fake()->unique()->randomElement([7, 15, 30, 60, 90, 120]),
            'prioridade_padrao' => PrioridadeAlerta::Informativo,
            'is_ativo' => true,
        ];
    }
}
