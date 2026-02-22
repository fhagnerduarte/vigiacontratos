<?php

namespace Database\Seeders;

use App\Enums\EtapaWorkflow;
use App\Enums\StatusAprovacao;
use App\Models\Aditivo;
use App\Models\Contrato;
use App\Models\WorkflowAprovacao;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AditivoSeeder extends Seeder
{
    public function run(): void
    {
        $conn = DB::connection('tenant');

        // Busca contratos vigentes para adicionar aditivos
        $contratos = Contrato::where('status', 'vigente')->get();

        if ($contratos->isEmpty()) {
            return;
        }

        $userId = $conn->table('users')->value('id');
        if (! $userId) {
            return;
        }

        // Busca roles para workflow
        $roles = $conn->table('roles')->pluck('id', 'nome')->toArray();

        // Contrato 001/2026 (servico, R$480.000) — 2 aditivos: prazo + valor
        $contrato1 = $contratos->firstWhere('numero', '001/2026');
        if ($contrato1) {
            // 1o Aditivo: Prazo (totalmente aprovado)
            $aditivo1 = Aditivo::create([
                'contrato_id' => $contrato1->id,
                'numero_sequencial' => 1,
                'tipo' => 'prazo',
                'status' => 'vigente',
                'data_assinatura' => '2026-06-15',
                'data_inicio_vigencia' => '2026-06-15',
                'nova_data_fim' => '2027-06-30',
                'valor_anterior_contrato' => 480000.00,
                'percentual_acumulado' => 0,
                'fundamentacao_legal' => 'Art. 107 da Lei 14.133/2021',
                'justificativa' => 'Necessidade de continuidade dos servicos essenciais de limpeza predial.',
                'justificativa_tecnica' => 'Os servicos de limpeza sao essenciais e continuos. A prorrogacao garante a manutencao do padrao de higiene das instalacoes publicas.',
                'observacoes' => 'Prorrogacao por mais 6 meses apos analise de desempenho satisfatorio do contratado.',
            ]);
            $this->criarWorkflowCompleto($aditivo1, $userId, $roles);

            // 2o Aditivo: Valor — acrescimo (em aprovacao — etapa 3 pendente)
            $aditivo2 = Aditivo::create([
                'contrato_id' => $contrato1->id,
                'numero_sequencial' => 2,
                'tipo' => 'valor',
                'status' => 'vigente',
                'data_assinatura' => '2026-08-10',
                'data_inicio_vigencia' => '2026-08-10',
                'valor_anterior_contrato' => 480000.00,
                'valor_acrescimo' => 72000.00,
                'percentual_acumulado' => 15.00,
                'fundamentacao_legal' => 'Art. 125 da Lei 14.133/2021',
                'justificativa' => 'Inclusao de area adicional (anexo da Secretaria de Educacao) no escopo do contrato.',
                'justificativa_tecnica' => 'Com a inauguracao do anexo, a area total de limpeza aumentou em 600m2, demandando acrescimo proporcional de 15% sobre o valor original.',
            ]);
            $this->criarWorkflowParcial($aditivo2, $userId, $roles, 2);
        }

        // Contrato 003/2026 (obra, R$2.800.000) — 1 aditivo: prazo_e_valor (totalmente aprovado)
        $contrato3 = $contratos->firstWhere('numero', '003/2026');
        if ($contrato3) {
            $aditivo3 = Aditivo::create([
                'contrato_id' => $contrato3->id,
                'numero_sequencial' => 1,
                'tipo' => 'prazo_e_valor',
                'status' => 'vigente',
                'data_assinatura' => '2026-07-20',
                'data_inicio_vigencia' => '2026-07-20',
                'nova_data_fim' => '2028-07-14',
                'valor_anterior_contrato' => 2800000.00,
                'valor_acrescimo' => 420000.00,
                'percentual_acumulado' => 15.00,
                'fundamentacao_legal' => 'Art. 125, I e II da Lei 14.133/2021',
                'justificativa' => 'Identificacao de patologias estruturais nao previstas no projeto original durante a fase de demolicao.',
                'justificativa_tecnica' => 'Laudo tecnico do engenheiro responsavel identificou necessidade de reforco estrutural em 3 pilares e na laje do 2o pavimento. O acrescimo de prazo (6 meses) e valor (15%) sao indispensaveis para garantir a seguranca da edificacao.',
                'observacoes' => 'Laudo tecnico anexado ao processo. Parecer juridico favoravel emitido.',
            ]);
            $this->criarWorkflowCompleto($aditivo3, $userId, $roles);
        }

        // Contrato 005/2026 (servico, R$240.000) — 1 aditivo: reequilibrio (em aprovacao — etapa 2 pendente)
        $contrato5 = $contratos->firstWhere('numero', '005/2026');
        if ($contrato5) {
            $valorAnterior = 20000.00;
            $valorReajustado = 21200.00;
            $acrescimo = $valorReajustado - $valorAnterior;

            $aditivo4 = Aditivo::create([
                'contrato_id' => $contrato5->id,
                'numero_sequencial' => 1,
                'tipo' => 'reequilibrio',
                'status' => 'vigente',
                'data_assinatura' => '2026-09-01',
                'data_inicio_vigencia' => '2026-09-01',
                'valor_anterior_contrato' => 240000.00,
                'valor_acrescimo' => $acrescimo,
                'percentual_acumulado' => round(($acrescimo / 240000) * 100, 2),
                'motivo_reequilibrio' => 'Variacao do IPCA acumulado no periodo de 12 meses acima do previsto na clausula de reajuste.',
                'indice_utilizado' => 'IPCA',
                'valor_anterior_reequilibrio' => $valorAnterior,
                'valor_reajustado' => $valorReajustado,
                'fundamentacao_legal' => 'Art. 124, II, d) da Lei 14.133/2021',
                'justificativa' => 'Reequilibrio economico-financeiro com base na variacao do IPCA dos ultimos 12 meses.',
                'justificativa_tecnica' => 'O contrato de assessoria juridica preve reajuste anual pelo IPCA. A variacao acumulada de 6% no periodo justifica o reequilibrio do valor mensal de R$20.000 para R$21.200.',
            ]);
            $this->criarWorkflowParcial($aditivo4, $userId, $roles, 1);
        }
    }

    /**
     * Cria workflow com todas as 5 etapas aprovadas.
     */
    private function criarWorkflowCompleto(Aditivo $aditivo, int $userId, array $roles): void
    {
        $agora = now();

        foreach (EtapaWorkflow::cases() as $etapa) {
            $roleNome = $etapa->roleResponsavel();
            $roleId = $roles[$roleNome] ?? null;

            WorkflowAprovacao::create([
                'aprovavel_type' => Aditivo::class,
                'aprovavel_id' => $aditivo->id,
                'etapa' => $etapa->value,
                'etapa_ordem' => $etapa->ordem(),
                'role_responsavel_id' => $roleId,
                'user_id' => $userId,
                'status' => StatusAprovacao::Aprovado->value,
                'parecer' => $etapa === EtapaWorkflow::Solicitacao
                    ? 'Solicitacao registrada automaticamente.'
                    : 'Aprovado conforme analise do setor responsavel.',
                'decided_at' => $agora->copy()->addDays($etapa->ordem()),
                'created_at' => $agora,
            ]);
        }
    }

    /**
     * Cria workflow com N etapas aprovadas e o restante pendente.
     */
    private function criarWorkflowParcial(Aditivo $aditivo, int $userId, array $roles, int $etapasAprovadas): void
    {
        $agora = now();

        foreach (EtapaWorkflow::cases() as $etapa) {
            $roleNome = $etapa->roleResponsavel();
            $roleId = $roles[$roleNome] ?? null;
            $aprovada = $etapa->ordem() <= $etapasAprovadas;

            WorkflowAprovacao::create([
                'aprovavel_type' => Aditivo::class,
                'aprovavel_id' => $aditivo->id,
                'etapa' => $etapa->value,
                'etapa_ordem' => $etapa->ordem(),
                'role_responsavel_id' => $roleId,
                'user_id' => $aprovada ? $userId : null,
                'status' => $aprovada ? StatusAprovacao::Aprovado->value : StatusAprovacao::Pendente->value,
                'parecer' => $aprovada
                    ? ($etapa === EtapaWorkflow::Solicitacao ? 'Solicitacao registrada automaticamente.' : 'Aprovado conforme analise.')
                    : null,
                'decided_at' => $aprovada ? $agora->copy()->addDays($etapa->ordem()) : null,
                'created_at' => $agora,
            ]);
        }
    }
}
