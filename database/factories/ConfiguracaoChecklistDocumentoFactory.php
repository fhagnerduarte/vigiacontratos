<?php

namespace Database\Factories;

use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Models\ConfiguracaoChecklistDocumento;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfiguracaoChecklistDocumentoFactory extends Factory
{
    protected $model = ConfiguracaoChecklistDocumento::class;

    public function definition(): array
    {
        return [
            'tipo_contrato' => $this->faker->randomElement(TipoContrato::cases()),
            'tipo_documento' => $this->faker->randomElement(TipoDocumentoContratual::cases()),
            'is_ativo' => true,
        ];
    }

    public function inativo(): self
    {
        return $this->state(['is_ativo' => false]);
    }
}
