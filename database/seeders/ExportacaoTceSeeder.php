<?php

namespace Database\Seeders;

use App\Models\ExportacaoTce;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExportacaoTceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            return;
        }

        $exportacoes = [
            [
                'formato' => 'xml',
                'filtros' => ['status' => 'vigente'],
                'total_contratos' => 45,
                'total_pendencias' => 3,
                'arquivo_nome' => 'relatorio-tce-2026-02-01.xml',
                'gerado_por' => $user->id,
                'observacoes' => 'Exportacao mensal para TCE - Janeiro/2026',
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ],
            [
                'formato' => 'csv',
                'filtros' => ['status' => 'vigente', 'nivel_risco' => 'alto'],
                'total_contratos' => 8,
                'total_pendencias' => 5,
                'arquivo_nome' => 'relatorio-tce-criticos-2026-02-10.csv',
                'gerado_por' => $user->id,
                'observacoes' => 'Contratos criticos para analise TCE',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ],
            [
                'formato' => 'excel',
                'filtros' => null,
                'total_contratos' => 52,
                'total_pendencias' => 7,
                'arquivo_nome' => 'relatorio-tce-completo-2026-02-20.xlsx',
                'gerado_por' => $user->id,
                'observacoes' => 'Relatorio completo para prestacao de contas',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
        ];

        foreach ($exportacoes as $exportacao) {
            ExportacaoTce::create($exportacao);
        }
    }
}
