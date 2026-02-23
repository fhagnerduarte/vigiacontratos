<?php

namespace Database\Factories;

use App\Models\Fornecedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fornecedor>
 */
class FornecedorFactory extends Factory
{
    protected $model = Fornecedor::class;

    public function definition(): array
    {
        return [
            'razao_social' => fake()->company(),
            'nome_fantasia' => fake()->company(),
            'cnpj' => self::gerarCnpjValido(),
            'representante_legal' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'telefone' => fake()->numerify('(##) ####-####'),
            'endereco' => fake()->streetAddress(),
            'cidade' => fake()->city(),
            'uf' => fake()->stateAbbr(),
            'cep' => fake()->numerify('#####-###'),
            'observacoes' => null,
        ];
    }

    /**
     * Gera CNPJ com digitos verificadores validos.
     */
    public static function gerarCnpjValido(): string
    {
        $n = [];
        for ($i = 0; $i < 12; $i++) {
            $n[$i] = rand(0, 9);
        }

        // Evitar sequencias repetidas
        if (count(array_unique($n)) === 1) {
            $n[0] = ($n[0] + 1) % 10;
        }

        // Primeiro digito verificador
        $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += $n[$i] * $pesos1[$i];
        }
        $resto = $soma % 11;
        $n[12] = $resto < 2 ? 0 : 11 - $resto;

        // Segundo digito verificador
        $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += $n[$i] * $pesos2[$i];
        }
        $resto = $soma % 11;
        $n[13] = $resto < 2 ? 0 : 11 - $resto;

        return implode('', $n);
    }
}
