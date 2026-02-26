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
                'razao_social'        => 'Tech Solutions Ltda',
                'nome_fantasia'       => 'TechSol',
                'cnpj'                => '11.222.333/0001-81',
                'representante_legal' => 'Roberto Almeida',
                'email'               => 'contato@techsol.com.br',
                'telefone'            => '(65) 3025-0001',
                'endereco'            => 'Av. Historiador Rubens de Mendonça, 1000',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78050-000',
            ],
            [
                'razao_social'        => 'Construtora Planalto S.A.',
                'nome_fantasia'       => 'Planalto Engenharia',
                'cnpj'                => '22.333.444/0001-40',
                'representante_legal' => 'Fernando Costa',
                'email'               => 'licitacao@planalto.eng.br',
                'telefone'            => '(65) 3025-0002',
                'endereco'            => 'Rua Barão de Melgaço, 500',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78005-300',
            ],
            [
                'razao_social'        => 'Distribuidora Medicamentos Central Ltda',
                'nome_fantasia'       => 'MedCentral',
                'cnpj'                => '33.444.555/0001-09',
                'representante_legal' => 'Lucia Ferreira',
                'email'               => 'vendas@medcentral.com.br',
                'telefone'            => '(65) 3025-0003',
                'endereco'            => 'Rua 13 de Junho, 250',
                'cidade'              => 'Várzea Grande',
                'uf'                  => 'MT',
                'cep'                 => '78110-000',
            ],
            [
                'razao_social'        => 'Papelaria e Informática Progresso Eireli',
                'nome_fantasia'       => 'Progresso Info',
                'cnpj'                => '44.555.666/0001-60',
                'representante_legal' => 'Marcos Pereira',
                'email'               => 'comercial@progressoinfo.com.br',
                'telefone'            => '(65) 3025-0004',
                'endereco'            => 'Av. CPA, 1500',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78055-500',
            ],
            [
                'razao_social'        => 'Serviços de Limpeza Pantanal ME',
                'nome_fantasia'       => 'Pantanal Limpeza',
                'cnpj'                => '55.666.777/0001-29',
                'representante_legal' => 'Juliana Ribeiro',
                'email'               => 'contato@pantanallimpeza.com.br',
                'telefone'            => '(65) 3025-0005',
                'endereco'            => 'Rua Joaquim Murtinho, 800',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78020-040',
            ],
            [
                'razao_social'        => 'Transportadora Cerrado Express Ltda',
                'nome_fantasia'       => 'Cerrado Express',
                'cnpj'                => '66.777.888/0001-88',
                'representante_legal' => 'Anderson Souza',
                'email'               => 'operacional@cerradoexpress.com.br',
                'telefone'            => '(65) 3025-0006',
                'endereco'            => 'Rod. BR-364, Km 12',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78048-000',
            ],
            [
                'razao_social'        => 'Engenharia e Manutenção Predial Norte S.A.',
                'nome_fantasia'       => 'Norte Engenharia',
                'cnpj'                => '77.888.999/0001-47',
                'representante_legal' => 'Claudio Nogueira',
                'email'               => 'contrato@norteengenharia.com.br',
                'telefone'            => '(65) 3025-0007',
                'endereco'            => 'Av. Miguel Sutil, 3200',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78043-400',
            ],
            [
                'razao_social'        => 'Alimentos e Nutrição Sabor do Cerrado Ltda',
                'nome_fantasia'       => 'Sabor do Cerrado',
                'cnpj'                => '88.999.111/0001-06',
                'representante_legal' => 'Teresa Gonçalves',
                'email'               => 'vendas@sabordocerrado.com.br',
                'telefone'            => '(65) 3025-0008',
                'endereco'            => 'Rua Antônio Maria Coelho, 450',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78015-180',
            ],
            [
                'razao_social'        => 'Segurança Patrimonial Forte Ltda',
                'nome_fantasia'       => 'Forte Segurança',
                'cnpj'                => '99.111.222/0001-65',
                'representante_legal' => 'Edilson Ramos',
                'email'               => 'licitacao@forteseguranca.com.br',
                'telefone'            => '(65) 3025-0009',
                'endereco'            => 'Av. Fernando Corrêa da Costa, 2100',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78060-900',
            ],
            [
                'razao_social'        => 'Consultoria e Assessoria Pública Ltda',
                'nome_fantasia'       => 'ConsultPub',
                'cnpj'                => '10.222.333/0001-24',
                'representante_legal' => 'Dra. Mariana Vasconcelos',
                'email'               => 'contato@consultpub.adv.br',
                'telefone'            => '(65) 3025-0010',
                'endereco'            => 'Rua Comandante Costa, 780',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78020-400',
            ],
            [
                'razao_social'        => 'Locadora de Veículos Capital Ltda',
                'nome_fantasia'       => 'Capital Locações',
                'cnpj'                => '20.333.444/0001-83',
                'representante_legal' => 'Paulo Henrique Matos',
                'email'               => 'frotas@capitallocacoes.com.br',
                'telefone'            => '(65) 3025-0011',
                'endereco'            => 'Av. Tenente Coronel Duarte, 1200',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78015-500',
            ],
            [
                'razao_social'        => 'Elétrica e Hidráulica Pantanal Eireli',
                'nome_fantasia'       => 'Pantanal Elétrica',
                'cnpj'                => '30.444.555/0001-42',
                'representante_legal' => 'Jorge Luiz Tavares',
                'email'               => 'orcamento@pantanaleletrica.com.br',
                'telefone'            => '(65) 3025-0012',
                'endereco'            => 'Rua Dom Aquino, 340',
                'cidade'              => 'Cuiabá',
                'uf'                  => 'MT',
                'cep'                 => '78015-200',
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
