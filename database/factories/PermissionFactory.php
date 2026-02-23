<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $recurso = fake()->unique()->slug(1);

        return [
            'nome' => $recurso . '.visualizar',
            'descricao' => 'Visualizar ' . $recurso,
            'grupo' => $recurso,
        ];
    }
}
