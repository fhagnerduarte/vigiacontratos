<?php

namespace Database\Factories;

use App\Enums\CategoriaContrato;
use App\Enums\CategoriaServico;
use App\Enums\ModalidadeContratacao;
use App\Enums\NivelRisco;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Enums\TipoPagamento;
use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Models\Servidor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contrato>
 */
class ContratoFactory extends Factory
{
    protected $model = Contrato::class;

    public function definition(): array
    {
        $dataInicio = fake()->dateTimeBetween('-1 year', 'now');
        $dataFim = fake()->dateTimeBetween('+1 month', '+2 years');
        $valorGlobal = fake()->randomFloat(2, 10000, 5000000);

        return [
            'numero' => str_pad(fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT) . '/' . date('Y'),
            'ano' => date('Y'),
            'objeto' => fake()->sentence(10),
            'tipo' => fake()->randomElement(TipoContrato::cases()),
            'status' => StatusContrato::Vigente,
            'modalidade_contratacao' => fake()->randomElement(ModalidadeContratacao::cases()),
            'fornecedor_id' => Fornecedor::factory(),
            'secretaria_id' => Secretaria::factory(),
            'unidade_gestora' => 'Prefeitura Municipal',
            'data_inicio' => $dataInicio->format('Y-m-d'),
            'data_fim' => $dataFim->format('Y-m-d'),
            'prazo_meses' => 12,
            'prorrogacao_automatica' => false,
            'valor_global' => $valorGlobal,
            'valor_mensal' => round($valorGlobal / 12, 2),
            'tipo_pagamento' => TipoPagamento::Mensal,
            'fonte_recurso' => 'Recursos Próprios',
            'dotacao_orcamentaria' => fake()->numerify('##.##.##.###.####.#.###.##'),
            'numero_empenho' => fake()->numerify('####/####'),
            'numero_processo' => fake()->numerify('#####/####'),
            'fundamento_legal' => null,
            'categoria' => CategoriaContrato::NaoEssencial,
            'categoria_servico' => fake()->randomElement(CategoriaServico::cases()),
            'responsavel_tecnico' => null,
            'gestor_nome' => null,
            'servidor_id' => Servidor::factory(),
            'score_risco' => 0,
            'nivel_risco' => NivelRisco::Baixo,
            'percentual_executado' => 0,
            'observacoes' => null,
        ];
    }

    public function vigente(): static
    {
        return $this->state(fn () => [
            'status' => StatusContrato::Vigente,
            'data_fim' => now()->addMonths(6)->format('Y-m-d'),
        ]);
    }

    public function vencido(): static
    {
        return $this->state(fn () => [
            'status' => StatusContrato::Vencido,
            'data_fim' => now()->subDays(10)->format('Y-m-d'),
        ]);
    }

    public function essencial(): static
    {
        return $this->state(fn () => [
            'categoria' => CategoriaContrato::Essencial,
        ]);
    }

    public function altoRisco(): static
    {
        return $this->state(fn () => [
            'score_risco' => 75,
            'nivel_risco' => NivelRisco::Alto,
        ]);
    }

    public function vencendoEm(int $dias): static
    {
        return $this->state(fn () => [
            'status' => StatusContrato::Vigente,
            'data_fim' => now()->addDays($dias)->format('Y-m-d'),
        ]);
    }

    public function dispensa(): static
    {
        return $this->state(fn () => [
            'modalidade_contratacao' => ModalidadeContratacao::Dispensa,
            'fundamento_legal' => 'Art. 75, II da Lei 14.133/2021',
        ]);
    }
}
