<?php

namespace Database\Factories;

use App\Enums\StatusAditivo;
use App\Enums\TipoAditivo;
use App\Models\Aditivo;
use App\Models\Contrato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aditivo>
 */
class AditivoFactory extends Factory
{
    protected $model = Aditivo::class;

    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'numero_sequencial' => 1,
            'tipo' => TipoAditivo::Prazo,
            'status' => StatusAditivo::Vigente,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addMonths(6)->format('Y-m-d'),
            'valor_anterior_contrato' => 100000.00,
            'valor_acrescimo' => 0,
            'valor_supressao' => 0,
            'percentual_acumulado' => 0,
            'fundamentacao_legal' => 'Art. 107 da Lei 14.133/2021',
            'justificativa' => fake()->sentence(15),
            'justificativa_tecnica' => fake()->sentence(10),
            'justificativa_excesso_limite' => null,
            'parecer_juridico_obrigatorio' => false,
            'motivo_reequilibrio' => null,
            'indice_utilizado' => null,
            'valor_anterior_reequilibrio' => null,
            'valor_reajustado' => null,
            'observacoes' => null,
        ];
    }

    public function deValor(float $acrescimo = 25000): static
    {
        return $this->state(fn () => [
            'tipo' => TipoAditivo::Valor,
            'valor_acrescimo' => $acrescimo,
            'nova_data_fim' => null,
        ]);
    }

    public function dePrazo(): static
    {
        return $this->state(fn () => [
            'tipo' => TipoAditivo::Prazo,
            'nova_data_fim' => now()->addYear()->format('Y-m-d'),
        ]);
    }

    public function cancelado(): static
    {
        return $this->state(fn () => [
            'status' => StatusAditivo::Cancelado,
        ]);
    }

    public function reequilibrio(): static
    {
        return $this->state(fn () => [
            'tipo' => TipoAditivo::Reequilibrio,
            'motivo_reequilibrio' => 'Variação cambial significativa',
            'indice_utilizado' => 'IPCA',
            'valor_anterior_reequilibrio' => 100000.00,
            'valor_reajustado' => 112000.00,
            'valor_acrescimo' => 12000.00,
        ]);
    }
}
