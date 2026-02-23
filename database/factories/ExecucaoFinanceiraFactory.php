<?php

namespace Database\Factories;

use App\Models\Contrato;
use App\Models\ExecucaoFinanceira;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExecucaoFinanceira>
 */
class ExecucaoFinanceiraFactory extends Factory
{
    protected $model = ExecucaoFinanceira::class;

    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'descricao' => fake()->sentence(5),
            'valor' => fake()->randomFloat(2, 1000, 100000),
            'data_execucao' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'numero_nota_fiscal' => fake()->numerify('NF-######'),
            'observacoes' => null,
            'registrado_por' => User::factory(),
        ];
    }
}
