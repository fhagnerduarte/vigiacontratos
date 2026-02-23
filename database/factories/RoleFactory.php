<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->slug(2),
            'descricao' => fake()->sentence(),
            'is_padrao' => false,
            'is_ativo' => true,
        ];
    }

    public function padrao(): static
    {
        return $this->state(fn () => ['is_padrao' => true]);
    }
}
