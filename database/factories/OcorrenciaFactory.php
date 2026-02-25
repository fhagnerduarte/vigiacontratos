<?php

namespace Database\Factories;

use App\Enums\TipoOcorrencia;
use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Ocorrencia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ocorrencia>
 */
class OcorrenciaFactory extends Factory
{
    protected $model = Ocorrencia::class;

    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'fiscal_id' => Fiscal::factory(),
            'data_ocorrencia' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'tipo_ocorrencia' => fake()->randomElement(TipoOcorrencia::cases())->value,
            'descricao' => fake()->paragraph(2),
            'providencia' => fake()->optional(0.6)->paragraph(),
            'prazo_providencia' => fake()->optional(0.4)->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
            'resolvida' => false,
            'resolvida_em' => null,
            'resolvida_por' => null,
            'observacoes' => null,
            'registrado_por' => User::factory(),
        ];
    }

    public function resolvida(): static
    {
        return $this->state(fn () => [
            'resolvida' => true,
            'resolvida_em' => now(),
            'resolvida_por' => User::factory(),
        ]);
    }

    public function atraso(): static
    {
        return $this->state(fn () => [
            'tipo_ocorrencia' => TipoOcorrencia::Atraso->value,
        ]);
    }

    public function inconformidade(): static
    {
        return $this->state(fn () => [
            'tipo_ocorrencia' => TipoOcorrencia::Inconformidade->value,
        ]);
    }

    public function vencida(): static
    {
        return $this->state(fn () => [
            'resolvida' => false,
            'prazo_providencia' => now()->subDays(5)->format('Y-m-d'),
        ]);
    }
}
