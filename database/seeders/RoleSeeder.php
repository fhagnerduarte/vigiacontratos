<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nome'      => 'administrador_geral',
                'descricao' => 'Administrador Geral',
            ],
            [
                'nome'      => 'controladoria',
                'descricao' => 'Controladoria Interna',
            ],
            [
                'nome'      => 'secretario',
                'descricao' => 'Secretário Municipal',
            ],
            [
                'nome'      => 'gestor_contrato',
                'descricao' => 'Gestor de Contrato',
            ],
            [
                'nome'      => 'fiscal_contrato',
                'descricao' => 'Fiscal de Contrato',
            ],
            [
                'nome'      => 'financeiro',
                'descricao' => 'Financeiro / Contabilidade',
            ],
            [
                'nome'      => 'procuradoria',
                'descricao' => 'Procuradoria Jurídica',
            ],
            [
                'nome'      => 'gabinete',
                'descricao' => 'Gabinete / Prefeito',
            ],
        ];

        foreach ($roles as $role) {
            DB::connection('tenant')->table('roles')->updateOrInsert(
                ['nome' => $role['nome']],
                array_merge($role, [
                    'is_padrao'  => true,
                    'is_ativo'   => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
