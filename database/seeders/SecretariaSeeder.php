<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecretariaSeeder extends Seeder
{
    public function run(): void
    {
        $secretarias = [
            [
                'nome'        => 'Secretaria Municipal de Saúde',
                'sigla'       => 'SMS',
                'responsavel' => 'Maria da Silva',
                'email'       => 'saude@prefeitura.gov.br',
                'telefone'    => '(65) 3321-0001',
            ],
            [
                'nome'        => 'Secretaria Municipal de Educação',
                'sigla'       => 'SME',
                'responsavel' => 'João Santos',
                'email'       => 'educacao@prefeitura.gov.br',
                'telefone'    => '(65) 3321-0002',
            ],
            [
                'nome'        => 'Secretaria Municipal de Obras',
                'sigla'       => 'SMO',
                'responsavel' => 'Carlos Oliveira',
                'email'       => 'obras@prefeitura.gov.br',
                'telefone'    => '(65) 3321-0003',
            ],
            [
                'nome'        => 'Secretaria Municipal de Administração',
                'sigla'       => 'SMA',
                'responsavel' => 'Ana Souza',
                'email'       => 'administracao@prefeitura.gov.br',
                'telefone'    => '(65) 3321-0004',
            ],
            [
                'nome'        => 'Secretaria Municipal de Meio Ambiente',
                'sigla'       => 'SMMA',
                'responsavel' => 'Pedro Lima',
                'email'       => 'meioambiente@prefeitura.gov.br',
                'telefone'    => '(65) 3321-0005',
            ],
        ];

        foreach ($secretarias as $secretaria) {
            DB::connection('tenant')->table('secretarias')->updateOrInsert(
                ['nome' => $secretaria['nome']],
                array_merge($secretaria, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
