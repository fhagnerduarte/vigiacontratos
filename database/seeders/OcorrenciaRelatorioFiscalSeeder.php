<?php

namespace Database\Seeders;

use App\Enums\TipoOcorrencia;
use App\Models\Contrato;
use App\Models\Ocorrencia;
use App\Models\RelatorioFiscal;
use App\Models\User;
use Illuminate\Database\Seeder;

class OcorrenciaRelatorioFiscalSeeder extends Seeder
{
    public function run(): void
    {
        $contratos = Contrato::withoutGlobalScopes()
            ->whereHas('fiscalAtual')
            ->take(5)
            ->get();

        if ($contratos->isEmpty()) {
            return;
        }

        $user = User::first();

        foreach ($contratos as $contrato) {
            $fiscal = $contrato->fiscalAtual;

            // 2-3 ocorrencias por contrato
            $tipos = fake()->randomElements(TipoOcorrencia::cases(), fake()->numberBetween(2, 3));
            foreach ($tipos as $tipo) {
                $resolvida = fake()->boolean(40);
                Ocorrencia::create([
                    'contrato_id' => $contrato->id,
                    'fiscal_id' => $fiscal->id,
                    'data_ocorrencia' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
                    'tipo_ocorrencia' => $tipo->value,
                    'descricao' => "Ocorrencia de {$tipo->label()} registrada durante fiscalizacao do contrato {$contrato->numero}.",
                    'providencia' => fake()->optional(0.6)->sentence(10),
                    'prazo_providencia' => fake()->optional(0.4)->dateTimeBetween('now', '+30 days')?->format('Y-m-d'),
                    'resolvida' => $resolvida,
                    'resolvida_em' => $resolvida ? now() : null,
                    'resolvida_por' => $resolvida ? $user->id : null,
                    'registrado_por' => $user->id,
                ]);
            }

            // 1-2 relatorios fiscais por contrato
            $mesesAtras = fake()->numberBetween(1, 2);
            for ($i = $mesesAtras; $i >= 1; $i--) {
                $inicio = now()->subMonths($i)->startOfMonth();
                $fim = now()->subMonths($i)->endOfMonth();

                RelatorioFiscal::create([
                    'contrato_id' => $contrato->id,
                    'fiscal_id' => $fiscal->id,
                    'periodo_inicio' => $inicio->format('Y-m-d'),
                    'periodo_fim' => $fim->format('Y-m-d'),
                    'descricao_atividades' => "Acompanhamento da execucao do contrato {$contrato->numero} no periodo de {$inicio->format('d/m/Y')} a {$fim->format('d/m/Y')}. Verificacao de conformidade, medicoes e fiscalizacao in loco.",
                    'conformidade_geral' => fake()->boolean(80),
                    'nota_desempenho' => fake()->numberBetween(5, 10),
                    'ocorrencias_no_periodo' => fake()->numberBetween(0, 3),
                    'registrado_por' => $user->id,
                ]);
            }
        }
    }
}
