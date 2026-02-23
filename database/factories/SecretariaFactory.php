<?php

namespace Database\Factories;

use App\Models\Secretaria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Secretaria>
 */
class SecretariaFactory extends Factory
{
    protected $model = Secretaria::class;

    public function definition(): array
    {
        $nomes = [
            'Educação', 'Saúde', 'Infraestrutura', 'Administração',
            'Finanças', 'Transporte', 'Meio Ambiente', 'Cultura',
        ];

        return [
            'nome' => 'Secretaria de ' . fake()->unique()->randomElement($nomes),
            'sigla' => strtoupper(fake()->unique()->lexify('???')),
            'responsavel' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'telefone' => fake()->numerify('(##) ####-####'),
        ];
    }
}
