<?php

namespace Database\Factories;

use App\Models\Secretaria;
use App\Models\Servidor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Servidor>
 */
class ServidorFactory extends Factory
{
    protected $model = Servidor::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'cpf' => self::gerarCpfValido(),
            'matricula' => fake()->unique()->numerify('MAT-######'),
            'cargo' => fake()->randomElement([
                'Analista Administrativo', 'Engenheiro Civil', 'Advogado',
                'Contador', 'Gestor Público', 'Técnico Administrativo',
            ]),
            'secretaria_id' => Secretaria::factory(),
            'email' => fake()->unique()->safeEmail(),
            'telefone' => fake()->numerify('(##) #####-####'),
            'is_ativo' => true,
            'observacoes' => null,
        ];
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['is_ativo' => false]);
    }

    /**
     * Gera CPF com digitos verificadores validos.
     */
    public static function gerarCpfValido(): string
    {
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[$i] = rand(0, 9);
        }

        // Evitar sequencias repetidas
        if (count(array_unique($n)) === 1) {
            $n[0] = ($n[0] + 1) % 10;
        }

        // Primeiro digito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += $n[$i] * (10 - $i);
        }
        $resto = $soma % 11;
        $n[9] = $resto < 2 ? 0 : 11 - $resto;

        // Segundo digito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += $n[$i] * (11 - $i);
        }
        $resto = $soma % 11;
        $n[10] = $resto < 2 ? 0 : 11 - $resto;

        return implode('', $n);
    }
}
