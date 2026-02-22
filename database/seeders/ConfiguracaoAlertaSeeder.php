<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracaoAlertaSeeder extends Seeder
{
    public function run(): void
    {
        $conn = DB::connection('tenant');

        $configs = [
            ['dias_antecedencia' => 120, 'prioridade_padrao' => 'informativo', 'is_ativo' => true],
            ['dias_antecedencia' => 90,  'prioridade_padrao' => 'informativo', 'is_ativo' => true],
            ['dias_antecedencia' => 60,  'prioridade_padrao' => 'atencao',     'is_ativo' => true],
            ['dias_antecedencia' => 30,  'prioridade_padrao' => 'atencao',     'is_ativo' => true],
            ['dias_antecedencia' => 15,  'prioridade_padrao' => 'urgente',     'is_ativo' => true],
            ['dias_antecedencia' => 7,   'prioridade_padrao' => 'urgente',     'is_ativo' => true],
        ];

        foreach ($configs as $config) {
            $conn->table('configuracoes_alerta')->updateOrInsert(
                ['dias_antecedencia' => $config['dias_antecedencia']],
                array_merge($config, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
