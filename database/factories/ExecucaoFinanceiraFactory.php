<?php

namespace Database\Factories;

use App\Enums\TipoExecucaoFinanceira;
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
            'tipo_execucao' => TipoExecucaoFinanceira::Pagamento->value,
            'descricao' => fake()->sentence(5),
            'valor' => fake()->randomFloat(2, 1000, 100000),
            'data_execucao' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'numero_nota_fiscal' => fake()->numerify('NF-######'),
            'numero_empenho' => null,
            'competencia' => null,
            'observacoes' => null,
            'registrado_por' => User::factory(),
        ];
    }

    public function pagamento(): static
    {
        return $this->state(fn () => [
            'tipo_execucao' => TipoExecucaoFinanceira::Pagamento->value,
        ]);
    }

    public function liquidacao(): static
    {
        return $this->state(fn () => [
            'tipo_execucao' => TipoExecucaoFinanceira::Liquidacao->value,
        ]);
    }

    public function empenhoAdicional(): static
    {
        return $this->state(fn () => [
            'tipo_execucao' => TipoExecucaoFinanceira::EmpenhoAdicional->value,
            'numero_empenho' => fake()->numerify('EMP-######'),
        ]);
    }

    public function comCompetencia(string $competencia): static
    {
        return $this->state(fn () => [
            'competencia' => $competencia,
        ]);
    }
}
