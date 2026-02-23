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
            'Esportes', 'Turismo', 'Assistência Social', 'Segurança',
            'Planejamento', 'Habitação', 'Comunicação', 'Tecnologia',
            'Agricultura', 'Desenvolvimento Econômico', 'Obras', 'Governo',
        ];

        return [
            'nome' => 'Secretaria de ' . fake()->randomElement($nomes) . ' ' . fake()->numerify('##'),
            'sigla' => strtoupper(fake()->lexify('???')) . fake()->numerify('##'),
            'responsavel' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'telefone' => fake()->numerify('(##) ####-####'),
        ];
    }
}
