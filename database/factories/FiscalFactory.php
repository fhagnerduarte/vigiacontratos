<?php

namespace Database\Factories;

use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Servidor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fiscal>
 */
class FiscalFactory extends Factory
{
    protected $model = Fiscal::class;

    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'servidor_id' => Servidor::factory(),
            'nome' => fake()->name(),
            'matricula' => fake()->numerify('MAT-######'),
            'cargo' => 'Fiscal de Contrato',
            'email' => fake()->safeEmail(),
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => null,
            'is_atual' => true,
        ];
    }

    public function inativo(): static
    {
        return $this->state(fn () => [
            'is_atual' => false,
            'data_fim' => now()->format('Y-m-d'),
        ]);
    }
}
