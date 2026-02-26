<?php

namespace Database\Factories;

use App\Enums\CategoriaServico;
use App\Models\PrecoReferencial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrecoReferencial>
 */
class PrecoReferencialFactory extends Factory
{
    protected $model = PrecoReferencial::class;

    public function definition(): array
    {
        $precoMinimo = fake()->randomFloat(2, 1000, 50000);
        $precoMediano = $precoMinimo * fake()->randomFloat(2, 1.1, 1.3);
        $precoMaximo = $precoMediano * fake()->randomFloat(2, 1.1, 1.3);

        return [
            'descricao' => fake()->sentence(5),
            'categoria_servico' => fake()->randomElement(CategoriaServico::cases())->value,
            'unidade_medida' => fake()->randomElement(['mes', 'm2', 'hora', 'unidade', 'km', 'diaria']),
            'preco_minimo' => round($precoMinimo, 2),
            'preco_mediano' => round($precoMediano, 2),
            'preco_maximo' => round($precoMaximo, 2),
            'fonte' => fake()->randomElement(['PNP 2026', 'Pesquisa de mercado', 'Ata RP', 'Banco de Precos']),
            'data_referencia' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'vigencia_ate' => fake()->dateTimeBetween('+1 month', '+12 months')->format('Y-m-d'),
            'observacoes' => null,
            'registrado_por' => User::factory(),
            'is_ativo' => true,
        ];
    }

    public function vigente(): static
    {
        return $this->state(fn () => [
            'is_ativo' => true,
            'vigencia_ate' => now()->addMonths(6)->format('Y-m-d'),
        ]);
    }

    public function expirado(): static
    {
        return $this->state(fn () => [
            'is_ativo' => true,
            'vigencia_ate' => now()->subDays(30)->format('Y-m-d'),
        ]);
    }

    public function porCategoria(CategoriaServico $categoria): static
    {
        return $this->state(fn () => [
            'categoria_servico' => $categoria->value,
        ]);
    }

    public function semVigencia(): static
    {
        return $this->state(fn () => [
            'vigencia_ate' => null,
        ]);
    }
}
