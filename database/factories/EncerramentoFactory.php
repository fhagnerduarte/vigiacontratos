<?php

namespace Database\Factories;

use App\Enums\EtapaEncerramento;
use App\Models\Contrato;
use App\Models\Encerramento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Encerramento>
 */
class EncerramentoFactory extends Factory
{
    protected $model = Encerramento::class;

    public function definition(): array
    {
        return [
            'contrato_id' => Contrato::factory(),
            'etapa_atual' => EtapaEncerramento::VerificacaoFinanceira,
            'data_inicio' => now(),
        ];
    }

    public function etapa(EtapaEncerramento $etapa): static
    {
        return $this->state(fn () => [
            'etapa_atual' => $etapa,
        ]);
    }
}
