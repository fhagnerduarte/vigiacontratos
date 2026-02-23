<?php

namespace Database\Factories;

use App\Models\DashboardAgregado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DashboardAgregado>
 */
class DashboardAgregadoFactory extends Factory
{
    protected $model = DashboardAgregado::class;

    public function definition(): array
    {
        return [
            'data_agregacao' => now()->format('Y-m-d'),
            'total_contratos_ativos' => fake()->numberBetween(10, 200),
            'valor_total_contratado' => fake()->randomFloat(2, 500000, 50000000),
            'valor_total_executado' => fake()->randomFloat(2, 100000, 30000000),
            'saldo_remanescente' => fake()->randomFloat(2, 100000, 20000000),
            'ticket_medio' => fake()->randomFloat(2, 50000, 500000),
            'risco_baixo' => fake()->numberBetween(5, 100),
            'risco_medio' => fake()->numberBetween(2, 50),
            'risco_alto' => fake()->numberBetween(0, 20),
            'vencendo_0_30d' => fake()->numberBetween(0, 15),
            'vencendo_31_60d' => fake()->numberBetween(0, 20),
            'vencendo_61_90d' => fake()->numberBetween(0, 25),
            'vencendo_91_120d' => fake()->numberBetween(0, 30),
            'vencendo_120p' => fake()->numberBetween(0, 100),
            'score_gestao' => fake()->numberBetween(50, 100),
            'dados_completos' => [],
        ];
    }
}
