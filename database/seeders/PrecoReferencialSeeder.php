<?php

namespace Database\Seeders;

use App\Enums\CategoriaServico;
use App\Models\PrecoReferencial;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrecoReferencialSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (! $user) {
            return;
        }

        $precos = [
            [
                'descricao' => 'Servico de transporte escolar - veiculo/mes',
                'categoria_servico' => CategoriaServico::Transporte->value,
                'unidade_medida' => 'mes',
                'preco_minimo' => 8500.00,
                'preco_mediano' => 12000.00,
                'preco_maximo' => 15000.00,
                'fonte' => 'PNP 2026',
            ],
            [
                'descricao' => 'Fornecimento de alimentacao escolar - refeicao/aluno',
                'categoria_servico' => CategoriaServico::Alimentacao->value,
                'unidade_medida' => 'mes',
                'preco_minimo' => 25000.00,
                'preco_mediano' => 35000.00,
                'preco_maximo' => 45000.00,
                'fonte' => 'PNP 2026',
            ],
            [
                'descricao' => 'Servico de TI - suporte e manutencao',
                'categoria_servico' => CategoriaServico::Tecnologia->value,
                'unidade_medida' => 'mes',
                'preco_minimo' => 15000.00,
                'preco_mediano' => 22000.00,
                'preco_maximo' => 30000.00,
                'fonte' => 'Pesquisa de mercado',
            ],
            [
                'descricao' => 'Servico de engenharia - obra civil',
                'categoria_servico' => CategoriaServico::Obras->value,
                'unidade_medida' => 'm2',
                'preco_minimo' => 180000.00,
                'preco_mediano' => 250000.00,
                'preco_maximo' => 350000.00,
                'fonte' => 'SINAPI 2026',
            ],
            [
                'descricao' => 'Servico de limpeza predial',
                'categoria_servico' => CategoriaServico::Limpeza->value,
                'unidade_medida' => 'mes',
                'preco_minimo' => 18000.00,
                'preco_mediano' => 25000.00,
                'preco_maximo' => 32000.00,
                'fonte' => 'PNP 2026',
            ],
            [
                'descricao' => 'Servico de vigilancia patrimonial',
                'categoria_servico' => CategoriaServico::Seguranca->value,
                'unidade_medida' => 'mes',
                'preco_minimo' => 22000.00,
                'preco_mediano' => 30000.00,
                'preco_maximo' => 40000.00,
                'fonte' => 'PNP 2026',
            ],
            [
                'descricao' => 'Servico de manutencao predial',
                'categoria_servico' => CategoriaServico::Manutencao->value,
                'unidade_medida' => 'mes',
                'preco_minimo' => 12000.00,
                'preco_mediano' => 18000.00,
                'preco_maximo' => 25000.00,
                'fonte' => 'Pesquisa de mercado',
            ],
            [
                'descricao' => 'Fornecimento de insumos hospitalares',
                'categoria_servico' => CategoriaServico::Saude->value,
                'unidade_medida' => 'mes',
                'preco_minimo' => 50000.00,
                'preco_mediano' => 75000.00,
                'preco_maximo' => 100000.00,
                'fonte' => 'Banco de Precos Saude',
            ],
            [
                'descricao' => 'Material didatico e pedagogico',
                'categoria_servico' => CategoriaServico::Educacao->value,
                'unidade_medida' => 'unidade',
                'preco_minimo' => 30000.00,
                'preco_mediano' => 45000.00,
                'preco_maximo' => 60000.00,
                'fonte' => 'PNP 2026',
            ],
            [
                'descricao' => 'Servicos diversos - apoio administrativo',
                'categoria_servico' => CategoriaServico::Outros->value,
                'unidade_medida' => 'mes',
                'preco_minimo' => 10000.00,
                'preco_mediano' => 15000.00,
                'preco_maximo' => 20000.00,
                'fonte' => 'Pesquisa de mercado',
            ],
        ];

        foreach ($precos as $preco) {
            PrecoReferencial::firstOrCreate(
                [
                    'descricao' => $preco['descricao'],
                    'categoria_servico' => $preco['categoria_servico'],
                ],
                array_merge($preco, [
                    'data_referencia' => now()->subMonths(1)->format('Y-m-d'),
                    'vigencia_ate' => now()->addYear()->format('Y-m-d'),
                    'registrado_por' => $user->id,
                    'is_ativo' => true,
                ])
            );
        }
    }
}
