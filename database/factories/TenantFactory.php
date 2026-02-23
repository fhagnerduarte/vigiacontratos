<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $nome = fake()->city() . ' Municipal';
        $slug = \Illuminate\Support\Str::slug($nome);

        return [
            'nome' => $nome,
            'slug' => $slug . '-' . fake()->unique()->numberBetween(1, 9999),
            'database_name' => 'vigiacontratos_' . $slug,
            'database_host' => null,
            'is_ativo' => true,
            'plano' => 'basico',
        ];
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['is_ativo' => false]);
    }
}
