<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracaoLimiteAditivoSeeder extends Seeder
{
    public function run(): void
    {
        $conn = DB::connection('tenant');

        $limites = [
            ['tipo_contrato' => 'servico', 'percentual_limite' => 25.00, 'is_bloqueante' => true, 'is_ativo' => true],
            ['tipo_contrato' => 'obra', 'percentual_limite' => 50.00, 'is_bloqueante' => true, 'is_ativo' => true],
            ['tipo_contrato' => 'compra', 'percentual_limite' => 25.00, 'is_bloqueante' => true, 'is_ativo' => true],
            ['tipo_contrato' => 'locacao', 'percentual_limite' => 25.00, 'is_bloqueante' => true, 'is_ativo' => true],
        ];

        foreach ($limites as $limite) {
            $conn->table('configuracoes_limite_aditivo')->updateOrInsert(
                ['tipo_contrato' => $limite['tipo_contrato']],
                array_merge($limite, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
