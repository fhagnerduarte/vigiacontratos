<?php

namespace Database\Factories;

use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\RelatorioFiscal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RelatorioFiscal>
 */
class RelatorioFiscalFactory extends Factory
{
    protected $model = RelatorioFiscal::class;

    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-3 months', '-1 month');
        $fim = (clone $inicio)->modify('+30 days');

        return [
            'contrato_id' => Contrato::factory(),
            'fiscal_id' => Fiscal::factory(),
            'periodo_inicio' => $inicio->format('Y-m-d'),
            'periodo_fim' => $fim->format('Y-m-d'),
            'descricao_atividades' => fake()->paragraph(3),
            'conformidade_geral' => true,
            'nota_desempenho' => fake()->numberBetween(5, 10),
            'ocorrencias_no_periodo' => fake()->numberBetween(0, 3),
            'observacoes' => null,
            'registrado_por' => User::factory(),
        ];
    }

    public function naoConforme(): static
    {
        return $this->state(fn () => [
            'conformidade_geral' => false,
            'nota_desempenho' => fake()->numberBetween(1, 4),
        ]);
    }

    public function comPeriodo(string $inicio, string $fim): static
    {
        return $this->state(fn () => [
            'periodo_inicio' => $inicio,
            'periodo_fim' => $fim,
        ]);
    }
}
