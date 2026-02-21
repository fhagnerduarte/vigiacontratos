<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServidorSeeder extends Seeder
{
    public function run(): void
    {
        $conn = DB::connection('tenant');

        $secretariaIds = $conn->table('secretarias')->pluck('id')->toArray();
        $primeiraSecretaria = $secretariaIds[0] ?? null;
        $segundaSecretaria = $secretariaIds[1] ?? $primeiraSecretaria;

        $servidores = [
            [
                'nome' => 'Ana Paula Ribeiro da Silva',
                'cpf' => '529.982.247-25',
                'matricula' => 'MAT-0001',
                'cargo' => 'Gestora de Contratos',
                'secretaria_id' => $primeiraSecretaria,
                'email' => 'ana.silva@prefeitura.gov.br',
                'telefone' => '(65) 3645-7890',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Carlos Eduardo Mendes',
                'cpf' => '418.273.956-80',
                'matricula' => 'MAT-0002',
                'cargo' => 'Analista Administrativo',
                'secretaria_id' => $primeiraSecretaria,
                'email' => 'carlos.mendes@prefeitura.gov.br',
                'telefone' => '(65) 3645-7891',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Maria Aparecida Santos',
                'cpf' => '305.617.482-09',
                'matricula' => 'MAT-0003',
                'cargo' => 'Coordenadora de Licitacoes',
                'secretaria_id' => $segundaSecretaria,
                'email' => 'maria.santos@prefeitura.gov.br',
                'telefone' => '(65) 3645-7892',
                'is_ativo' => true,
            ],
            [
                'nome' => 'Roberto Jose Ferreira',
                'cpf' => null,
                'matricula' => 'MAT-0004',
                'cargo' => 'Assessor Juridico',
                'secretaria_id' => $primeiraSecretaria,
                'email' => 'roberto.ferreira@prefeitura.gov.br',
                'telefone' => null,
                'is_ativo' => true,
            ],
            [
                'nome' => 'Patricia Lima Oliveira',
                'cpf' => null,
                'matricula' => 'MAT-0005',
                'cargo' => 'Engenheira Civil',
                'secretaria_id' => $segundaSecretaria,
                'email' => 'patricia.oliveira@prefeitura.gov.br',
                'telefone' => null,
                'is_ativo' => false,
            ],
        ];

        foreach ($servidores as $servidor) {
            $conn->table('servidores')->updateOrInsert(
                ['matricula' => $servidor['matricula']],
                array_merge($servidor, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
