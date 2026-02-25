<?php

namespace Database\Seeders;

use App\Enums\ClassificacaoRespostaLai;
use App\Enums\StatusSolicitacaoLai;
use App\Models\HistoricoSolicitacaoLai;
use App\Models\SolicitacaoLai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolicitacaoLaiSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = DB::connection('tenant')->table('tenants')->value('id');
        if (!$tenantId) {
            $this->command->warn('Nenhum tenant encontrado. Pulando SolicitacaoLaiSeeder.');
            return;
        }

        $userId = DB::connection('tenant')->table('users')->value('id');

        $solicitacoes = [
            [
                'protocolo' => 'LAI-' . now()->year . '-000001',
                'nome_solicitante' => 'Maria Silva Santos',
                'email_solicitante' => 'maria.silva@email.com',
                'cpf_solicitante' => '123.456.789-00',
                'telefone_solicitante' => '(11) 99999-0001',
                'assunto' => 'Contratos de limpeza publica 2025',
                'descricao' => 'Solicito informacoes sobre todos os contratos de limpeza publica firmados no exercicio de 2025, incluindo valores, empresas contratadas e vigencia.',
                'status' => StatusSolicitacaoLai::Recebida->value,
                'prazo_legal' => now()->addDays(18)->toDateString(),
            ],
            [
                'protocolo' => 'LAI-' . now()->year . '-000002',
                'nome_solicitante' => 'Joao Pedro Oliveira',
                'email_solicitante' => 'joao.oliveira@email.com',
                'cpf_solicitante' => '987.654.321-00',
                'telefone_solicitante' => '(21) 98888-0002',
                'assunto' => 'Gastos com merenda escolar',
                'descricao' => 'Solicito relatorio detalhado dos gastos com merenda escolar nos ultimos 12 meses, discriminando por escola e fornecedor.',
                'status' => StatusSolicitacaoLai::EmAnalise->value,
                'prazo_legal' => now()->addDays(10)->toDateString(),
            ],
            [
                'protocolo' => 'LAI-' . now()->year . '-000003',
                'nome_solicitante' => 'Ana Carolina Ferreira',
                'email_solicitante' => 'ana.ferreira@email.com',
                'cpf_solicitante' => '111.222.333-44',
                'assunto' => 'Obras de pavimentacao do bairro Centro',
                'descricao' => 'Solicito copia do contrato de pavimentacao do bairro Centro, incluindo cronograma fisico-financeiro e empresa responsavel pela execucao da obra.',
                'status' => StatusSolicitacaoLai::Respondida->value,
                'classificacao_resposta' => ClassificacaoRespostaLai::Deferida->value,
                'resposta' => 'Segue em anexo o contrato solicitado (Contrato 045/2025) firmado com a empresa ABC Pavimentacao Ltda, no valor de R$ 2.500.000,00, com prazo de 180 dias.',
                'respondido_por' => $userId,
                'data_resposta' => now()->subDays(5),
                'prazo_legal' => now()->subDays(3)->toDateString(),
            ],
            [
                'protocolo' => 'LAI-' . now()->year . '-000004',
                'nome_solicitante' => 'Carlos Eduardo Lima',
                'email_solicitante' => 'carlos.lima@email.com',
                'cpf_solicitante' => '555.666.777-88',
                'telefone_solicitante' => '(31) 97777-0004',
                'assunto' => 'Contratos de tecnologia da informacao',
                'descricao' => 'Solicito lista de todos os contratos de TI vigentes, com valores mensais, empresas e descricao dos servicos prestados ao municipio.',
                'status' => StatusSolicitacaoLai::Prorrogada->value,
                'prazo_legal' => now()->addDays(2)->toDateString(),
                'prazo_estendido' => now()->addDays(12)->toDateString(),
                'data_prorrogacao' => now()->subDays(3),
                'justificativa_prorrogacao' => 'Necessidade de consolidar informacoes de multiplas secretarias para atendimento completo da solicitacao.',
            ],
            [
                'protocolo' => 'LAI-' . now()->year . '-000005',
                'nome_solicitante' => 'Patricia Souza Mendes',
                'email_solicitante' => 'patricia.mendes@email.com',
                'cpf_solicitante' => '999.888.777-66',
                'assunto' => 'Informacoes sobre contrato de seguranca',
                'descricao' => 'Solicito informacoes detalhadas sobre contrato de seguranca patrimonial dos predios publicos, incluindo efetivo por unidade e custos.',
                'status' => StatusSolicitacaoLai::Indeferida->value,
                'classificacao_resposta' => ClassificacaoRespostaLai::Indeferida->value,
                'resposta' => 'Informacao classificada como reservada por envolver seguranca publica, conforme art. 23, inciso II, da Lei 12.527/2011.',
                'respondido_por' => $userId,
                'data_resposta' => now()->subDays(8),
                'prazo_legal' => now()->subDays(10)->toDateString(),
            ],
            [
                'protocolo' => 'LAI-' . now()->year . '-000006',
                'nome_solicitante' => 'Roberto Alves Costa',
                'email_solicitante' => 'roberto.costa@email.com',
                'cpf_solicitante' => '444.333.222-11',
                'assunto' => 'Contratos de manutencao predial',
                'descricao' => 'Gostaria de obter informacoes sobre os contratos de manutencao predial vigentes, incluindo valores e empresas responsaveis pela manutencao dos predios publicos municipais.',
                'status' => StatusSolicitacaoLai::Recebida->value,
                'prazo_legal' => now()->subDays(5)->toDateString(),
            ],
        ];

        foreach ($solicitacoes as $data) {
            $data['tenant_id'] = $tenantId;
            $data['created_at'] = now()->subDays(rand(5, 30));
            $data['updated_at'] = now();

            $solicitacao = SolicitacaoLai::create($data);

            // Historico inicial
            HistoricoSolicitacaoLai::create([
                'solicitacao_lai_id' => $solicitacao->id,
                'status_anterior' => null,
                'status_novo' => StatusSolicitacaoLai::Recebida->value,
                'observacao' => 'Solicitacao registrada pelo cidadao',
                'created_at' => $solicitacao->created_at,
            ]);

            // Historicos adicionais conforme status
            if ($data['status'] !== StatusSolicitacaoLai::Recebida->value) {
                HistoricoSolicitacaoLai::create([
                    'solicitacao_lai_id' => $solicitacao->id,
                    'status_anterior' => StatusSolicitacaoLai::Recebida->value,
                    'status_novo' => $data['status'] === StatusSolicitacaoLai::Prorrogada->value
                        ? StatusSolicitacaoLai::EmAnalise->value
                        : $data['status'],
                    'observacao' => 'Transicao de status',
                    'user_id' => $userId,
                    'created_at' => $solicitacao->created_at->addDays(2),
                ]);
            }

            if ($data['status'] === StatusSolicitacaoLai::Prorrogada->value) {
                HistoricoSolicitacaoLai::create([
                    'solicitacao_lai_id' => $solicitacao->id,
                    'status_anterior' => StatusSolicitacaoLai::EmAnalise->value,
                    'status_novo' => StatusSolicitacaoLai::Prorrogada->value,
                    'observacao' => $data['justificativa_prorrogacao'] ?? 'Prazo prorrogado',
                    'user_id' => $userId,
                    'created_at' => $solicitacao->created_at->addDays(5),
                ]);
            }
        }

        $this->command->info('SolicitacaoLaiSeeder: ' . count($solicitacoes) . ' solicitacoes criadas.');
    }
}
