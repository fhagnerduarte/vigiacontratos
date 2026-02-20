<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContratoSeeder extends Seeder
{
    public function run(): void
    {
        $fornecedorId = DB::connection('tenant')->table('fornecedores')->value('id');
        $secretariaId = DB::connection('tenant')->table('secretarias')->value('id');

        if (! $fornecedorId || ! $secretariaId) {
            return;
        }

        $contratos = [
            [
                'numero' => '001/2026',
                'ano' => '2026',
                'objeto' => 'Prestacao de servicos de limpeza e conservacao predial para a Secretaria de Administracao',
                'tipo' => 'servico',
                'status' => 'vigente',
                'modalidade_contratacao' => 'pregao_eletronico',
                'fornecedor_id' => $fornecedorId,
                'secretaria_id' => $secretariaId,
                'data_inicio' => '2026-01-01',
                'data_fim' => '2026-12-31',
                'prazo_meses' => 12,
                'prorrogacao_automatica' => true,
                'valor_global' => 480000.00,
                'valor_mensal' => 40000.00,
                'tipo_pagamento' => 'mensal',
                'numero_processo' => '2025.012345',
                'categoria' => 'essencial',
                'categoria_servico' => 'limpeza',
                'score_risco' => 20,
                'nivel_risco' => 'baixo',
                'percentual_executado' => 8.33,
            ],
            [
                'numero' => '002/2026',
                'ano' => '2026',
                'objeto' => 'Aquisicao de equipamentos de informatica para modernizacao do parque tecnologico municipal',
                'tipo' => 'compra',
                'status' => 'vigente',
                'modalidade_contratacao' => 'pregao_eletronico',
                'fornecedor_id' => $fornecedorId,
                'secretaria_id' => $secretariaId,
                'data_inicio' => '2026-02-01',
                'data_fim' => '2026-08-01',
                'prazo_meses' => 6,
                'prorrogacao_automatica' => false,
                'valor_global' => 1500000.00,
                'tipo_pagamento' => 'parcelado',
                'numero_processo' => '2025.023456',
                'categoria' => 'nao_essencial',
                'categoria_servico' => 'tecnologia',
                'score_risco' => 40,
                'nivel_risco' => 'medio',
                'percentual_executado' => 0,
            ],
            [
                'numero' => '003/2026',
                'ano' => '2026',
                'objeto' => 'Reforma e adequacao do predio da Secretaria de Saude — Unidade Central',
                'tipo' => 'obra',
                'status' => 'vigente',
                'modalidade_contratacao' => 'concorrencia',
                'fornecedor_id' => $fornecedorId,
                'secretaria_id' => $secretariaId,
                'data_inicio' => '2026-01-15',
                'data_fim' => '2028-01-14',
                'prazo_meses' => 24,
                'prorrogacao_automatica' => false,
                'valor_global' => 2800000.00,
                'tipo_pagamento' => 'por_medicao',
                'numero_processo' => '2025.034567',
                'responsavel_tecnico' => 'Eng. Carlos Alberto Silva — CREA 12345/SP',
                'categoria' => 'essencial',
                'categoria_servico' => 'obras',
                'score_risco' => 30,
                'nivel_risco' => 'medio',
                'percentual_executado' => 15.50,
            ],
            [
                'numero' => '004/2025',
                'ano' => '2025',
                'objeto' => 'Locacao de veiculos para transporte escolar — rota urbana',
                'tipo' => 'locacao',
                'status' => 'vencido',
                'modalidade_contratacao' => 'pregao_presencial',
                'fornecedor_id' => $fornecedorId,
                'secretaria_id' => $secretariaId,
                'data_inicio' => '2025-02-01',
                'data_fim' => '2025-12-20',
                'prazo_meses' => 10,
                'prorrogacao_automatica' => false,
                'valor_global' => 360000.00,
                'valor_mensal' => 36000.00,
                'tipo_pagamento' => 'mensal',
                'numero_processo' => '2024.056789',
                'categoria' => 'essencial',
                'categoria_servico' => 'transporte',
                'score_risco' => 60,
                'nivel_risco' => 'alto',
                'percentual_executado' => 100.00,
            ],
            [
                'numero' => '005/2026',
                'ano' => '2026',
                'objeto' => 'Contratacao de servicos de assessoria juridica especializada em licitacoes',
                'tipo' => 'servico',
                'status' => 'vigente',
                'modalidade_contratacao' => 'inexigibilidade',
                'fornecedor_id' => $fornecedorId,
                'secretaria_id' => $secretariaId,
                'data_inicio' => '2026-01-10',
                'data_fim' => '2027-01-09',
                'prazo_meses' => 12,
                'prorrogacao_automatica' => true,
                'valor_global' => 240000.00,
                'valor_mensal' => 20000.00,
                'tipo_pagamento' => 'mensal',
                'numero_processo' => '2025.067890',
                'fundamento_legal' => 'Art. 74, III da Lei 14.133/2021',
                'categoria' => 'nao_essencial',
                'score_risco' => 10,
                'nivel_risco' => 'baixo',
                'percentual_executado' => 0,
            ],
        ];

        foreach ($contratos as $contrato) {
            $existing = DB::connection('tenant')->table('contratos')
                ->where('numero', $contrato['numero'])
                ->exists();

            if (! $existing) {
                $contratoId = DB::connection('tenant')->table('contratos')->insertGetId(
                    array_merge($contrato, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );

                // Cria fiscal para contratos vigentes
                if ($contrato['status'] === 'vigente') {
                    DB::connection('tenant')->table('fiscais')->insert([
                        'contrato_id' => $contratoId,
                        'nome' => 'Maria Aparecida Santos',
                        'matricula' => 'MAT-' . str_pad($contratoId, 4, '0', STR_PAD_LEFT),
                        'cargo' => 'Analista Administrativo',
                        'email' => 'fiscal' . $contratoId . '@prefeitura.gov.br',
                        'data_inicio' => $contrato['data_inicio'],
                        'is_atual' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
