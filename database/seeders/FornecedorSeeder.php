<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FornecedorSeeder extends Seeder
{
    public function run(): void
    {
        $fornecedores = [
            [
                'razao_social'       => 'Tech Solutions Ltda',
                'nome_fantasia'      => 'TechSol',
                'cnpj'               => '11.222.333/0001-81',
                'representante_legal' => 'Roberto Almeida',
                'email'              => 'contato@techsol.com.br',
                'telefone'           => '(65) 3025-0001',
                'endereco'           => 'Av. Historiador Rubens de Mendonça, 1000',
                'cidade'             => 'Cuiabá',
                'uf'                 => 'MT',
                'cep'                => '78050-000',
            ],
            [
                'razao_social'       => 'Construtora Planalto S.A.',
                'nome_fantasia'      => 'Planalto Engenharia',
                'cnpj'               => '22.333.444/0001-40',
                'representante_legal' => 'Fernando Costa',
                'email'              => 'licitacao@planalto.eng.br',
                'telefone'           => '(65) 3025-0002',
                'endereco'           => 'Rua Barão de Melgaço, 500',
                'cidade'             => 'Cuiabá',
                'uf'                 => 'MT',
                'cep'                => '78005-300',
            ],
            [
                'razao_social'       => 'Distribuidora Medicamentos Central Ltda',
                'nome_fantasia'      => 'MedCentral',
                'cnpj'               => '33.444.555/0001-09',
                'representante_legal' => 'Lucia Ferreira',
                'email'              => 'vendas@medcentral.com.br',
                'telefone'           => '(65) 3025-0003',
                'endereco'           => 'Rua 13 de Junho, 250',
                'cidade'             => 'Várzea Grande',
                'uf'                 => 'MT',
                'cep'                => '78110-000',
            ],
            [
                'razao_social'       => 'Papelaria e Informática Progresso Eireli',
                'nome_fantasia'      => 'Progresso Info',
                'cnpj'               => '44.555.666/0001-60',
                'representante_legal' => 'Marcos Pereira',
                'email'              => 'comercial@progressoinfo.com.br',
                'telefone'           => '(65) 3025-0004',
                'endereco'           => 'Av. CPA, 1500',
                'cidade'             => 'Cuiabá',
                'uf'                 => 'MT',
                'cep'                => '78055-500',
            ],
            [
                'razao_social'       => 'Serviços de Limpeza Pantanal ME',
                'nome_fantasia'      => 'Pantanal Limpeza',
                'cnpj'               => '55.666.777/0001-29',
                'representante_legal' => 'Juliana Ribeiro',
                'email'              => 'contato@pantanallimpeza.com.br',
                'telefone'           => '(65) 3025-0005',
                'endereco'           => 'Rua Joaquim Murtinho, 800',
                'cidade'             => 'Cuiabá',
                'uf'                 => 'MT',
                'cep'                => '78020-040',
            ],
        ];

        foreach ($fornecedores as $fornecedor) {
            DB::connection('tenant')->table('fornecedores')->updateOrInsert(
                ['cnpj' => $fornecedor['cnpj']],
                array_merge($fornecedor, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
