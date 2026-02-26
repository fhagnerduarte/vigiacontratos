<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServidorSeeder extends Seeder
{
    public function run(): void
    {
        $conn = DB::connection('tenant');

        $secretariaIds = $conn->table('secretarias')->pluck('id', 'sigla')->toArray();

        $servidores = [
            // SMS - Saúde
            [
                'nome'          => 'Ana Paula Ribeiro da Silva',
                'cpf'           => '529.982.247-25',
                'matricula'     => 'MAT-0001',
                'cargo'         => 'Gestora de Contratos',
                'secretaria_id' => $secretariaIds['SMS'] ?? null,
                'email'         => 'ana.silva@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7890',
                'is_ativo'      => true,
            ],
            [
                'nome'          => 'Carlos Eduardo Mendes',
                'cpf'           => '418.273.956-80',
                'matricula'     => 'MAT-0002',
                'cargo'         => 'Analista Administrativo',
                'secretaria_id' => $secretariaIds['SMS'] ?? null,
                'email'         => 'carlos.mendes@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7891',
                'is_ativo'      => true,
            ],
            [
                'nome'          => 'Renata Cristina Almeida',
                'cpf'           => '712.438.195-63',
                'matricula'     => 'MAT-0006',
                'cargo'         => 'Enfermeira Coordenadora',
                'secretaria_id' => $secretariaIds['SMS'] ?? null,
                'email'         => 'renata.almeida@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7896',
                'is_ativo'      => true,
            ],
            // SME - Educação
            [
                'nome'          => 'Maria Aparecida Santos',
                'cpf'           => '305.617.482-09',
                'matricula'     => 'MAT-0003',
                'cargo'         => 'Coordenadora de Licitações',
                'secretaria_id' => $secretariaIds['SME'] ?? null,
                'email'         => 'maria.santos@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7892',
                'is_ativo'      => true,
            ],
            [
                'nome'          => 'Lucas Fernando Barbosa',
                'cpf'           => '891.234.567-01',
                'matricula'     => 'MAT-0007',
                'cargo'         => 'Diretor Pedagógico',
                'secretaria_id' => $secretariaIds['SME'] ?? null,
                'email'         => 'lucas.barbosa@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7897',
                'is_ativo'      => true,
            ],
            // SMO - Obras
            [
                'nome'          => 'Patricia Lima Oliveira',
                'cpf'           => '623.845.971-44',
                'matricula'     => 'MAT-0005',
                'cargo'         => 'Engenheira Civil',
                'secretaria_id' => $secretariaIds['SMO'] ?? null,
                'email'         => 'patricia.oliveira@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7895',
                'is_ativo'      => true,
            ],
            [
                'nome'          => 'Fernando Augusto Pires',
                'cpf'           => '456.789.123-56',
                'matricula'     => 'MAT-0008',
                'cargo'         => 'Engenheiro de Fiscalização',
                'secretaria_id' => $secretariaIds['SMO'] ?? null,
                'email'         => 'fernando.pires@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7898',
                'is_ativo'      => true,
            ],
            // SMA - Administração
            [
                'nome'          => 'Roberto José Ferreira',
                'cpf'           => '234.567.890-12',
                'matricula'     => 'MAT-0004',
                'cargo'         => 'Assessor Jurídico',
                'secretaria_id' => $secretariaIds['SMA'] ?? null,
                'email'         => 'roberto.ferreira@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7894',
                'is_ativo'      => true,
            ],
            [
                'nome'          => 'Juliana Carvalho Freitas',
                'cpf'           => '678.901.234-78',
                'matricula'     => 'MAT-0009',
                'cargo'         => 'Chefe de Gabinete',
                'secretaria_id' => $secretariaIds['SMA'] ?? null,
                'email'         => 'juliana.freitas@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7899',
                'is_ativo'      => true,
            ],
            // SMMA - Meio Ambiente
            [
                'nome'          => 'Gustavo Henrique Nascimento',
                'cpf'           => '345.678.901-23',
                'matricula'     => 'MAT-0010',
                'cargo'         => 'Analista Ambiental',
                'secretaria_id' => $secretariaIds['SMMA'] ?? null,
                'email'         => 'gustavo.nascimento@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7900',
                'is_ativo'      => true,
            ],
            // SMF - Fazenda
            [
                'nome'          => 'Adriana Beatriz Campos',
                'cpf'           => '567.890.123-45',
                'matricula'     => 'MAT-0011',
                'cargo'         => 'Contadora Municipal',
                'secretaria_id' => $secretariaIds['SMF'] ?? null,
                'email'         => 'adriana.campos@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7901',
                'is_ativo'      => true,
            ],
            // SMAS - Assistência Social
            [
                'nome'          => 'Camila Rodrigues Martins',
                'cpf'           => '789.012.345-67',
                'matricula'     => 'MAT-0012',
                'cargo'         => 'Assistente Social',
                'secretaria_id' => $secretariaIds['SMAS'] ?? null,
                'email'         => 'camila.martins@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7902',
                'is_ativo'      => true,
            ],
            // SMSP - Segurança
            [
                'nome'          => 'Thiago Henrique Sousa',
                'cpf'           => '901.234.567-89',
                'matricula'     => 'MAT-0013',
                'cargo'         => 'Coordenador de Segurança',
                'secretaria_id' => $secretariaIds['SMSP'] ?? null,
                'email'         => 'thiago.sousa@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7903',
                'is_ativo'      => true,
            ],
            // SMCT - Cultura
            [
                'nome'          => 'Isabela Cristina Duarte',
                'cpf'           => '123.456.789-01',
                'matricula'     => 'MAT-0014',
                'cargo'         => 'Produtora Cultural',
                'secretaria_id' => $secretariaIds['SMCT'] ?? null,
                'email'         => 'isabela.duarte@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7904',
                'is_ativo'      => true,
            ],
            // SMTM - Transporte
            [
                'nome'          => 'Rafael Moreira da Costa',
                'cpf'           => '246.813.579-02',
                'matricula'     => 'MAT-0015',
                'cargo'         => 'Gestor de Frota',
                'secretaria_id' => $secretariaIds['SMTM'] ?? null,
                'email'         => 'rafael.costa@prefeitura.gov.br',
                'telefone'      => '(65) 3645-7905',
                'is_ativo'      => true,
            ],
            // Servidores inativos
            [
                'nome'          => 'Marcos Antônio Pereira',
                'cpf'           => '135.792.468-13',
                'matricula'     => 'MAT-0016',
                'cargo'         => 'Analista Administrativo',
                'secretaria_id' => $secretariaIds['SMA'] ?? null,
                'email'         => 'marcos.pereira@prefeitura.gov.br',
                'telefone'      => null,
                'is_ativo'      => false,
                'observacoes'   => 'Aposentado em 01/2026',
            ],
            [
                'nome'          => 'Vanessa Lopes Cardoso',
                'cpf'           => '864.213.579-46',
                'matricula'     => 'MAT-0017',
                'cargo'         => 'Fiscal de Obras',
                'secretaria_id' => $secretariaIds['SMO'] ?? null,
                'email'         => 'vanessa.cardoso@prefeitura.gov.br',
                'telefone'      => null,
                'is_ativo'      => false,
                'observacoes'   => 'Transferida para outro órgão',
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
