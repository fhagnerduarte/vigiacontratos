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

        $contratos = Contrato::withoutGlobalScopes()
            ->whereIn('status', ['vigente', 'suspenso', 'vencido'])
            ->get()
            ->keyBy('numero');

        if ($contratos->isEmpty()) {
            return;
        }

        $userId = $conn->table('users')->value('id');
        if (! $userId) {
            return;
        }

        $roles = $conn->table('roles')->pluck('id', 'nome')->toArray();

        // ── Contrato 001/2026 (limpeza, R$480k) — 2 aditivos ──────────
        $contrato = $contratos->get('001/2026');
        if ($contrato && ! $this->aditivoExiste($contrato->id, 1)) {
            // 1º Aditivo: Prazo (totalmente aprovado)
            $aditivo = Aditivo::create([
                'contrato_id'            => $contrato->id,
                'numero_sequencial'      => 1,
                'tipo'                   => 'prazo',
                'status'                 => 'vigente',
                'data_assinatura'        => '2026-06-15',
                'data_inicio_vigencia'   => '2026-06-15',
                'nova_data_fim'          => '2027-06-30',
                'valor_anterior_contrato' => 480000.00,
                'percentual_acumulado'   => 0,
                'fundamentacao_legal'    => 'Art. 107 da Lei 14.133/2021',
                'justificativa'          => 'Necessidade de continuidade dos serviços essenciais de limpeza predial.',
                'justificativa_tecnica'  => 'Os serviços de limpeza são essenciais e contínuos. A prorrogação garante a manutenção do padrão de higiene das instalações públicas.',
                'observacoes'            => 'Prorrogação por mais 6 meses após análise de desempenho satisfatório do contratado.',
            ]);
            $this->criarWorkflowCompleto($aditivo, $userId, $roles);
        }

        if ($contrato && ! $this->aditivoExiste($contrato->id, 2)) {
            // 2º Aditivo: Valor — acréscimo (em aprovação — etapa 3 pendente)
            $aditivo = Aditivo::create([
                'contrato_id'            => $contrato->id,
                'numero_sequencial'      => 2,
                'tipo'                   => 'valor',
                'status'                 => 'vigente',
                'data_assinatura'        => '2026-08-10',
                'data_inicio_vigencia'   => '2026-08-10',
                'valor_anterior_contrato' => 480000.00,
                'valor_acrescimo'        => 72000.00,
                'percentual_acumulado'   => 15.00,
                'fundamentacao_legal'    => 'Art. 125 da Lei 14.133/2021',
                'justificativa'          => 'Inclusão de área adicional (anexo da Secretaria de Educação) no escopo do contrato.',
                'justificativa_tecnica'  => 'Com a inauguração do anexo, a área total de limpeza aumentou em 600m², demandando acréscimo proporcional de 15% sobre o valor original.',
            ]);
            $this->criarWorkflowParcial($aditivo, $userId, $roles, 2);
        }

        // ── Contrato 003/2026 (obra saúde, R$2.8M) — 1 aditivo ───────
        $contrato = $contratos->get('003/2026');
        if ($contrato && ! $this->aditivoExiste($contrato->id, 1)) {
            $aditivo = Aditivo::create([
                'contrato_id'            => $contrato->id,
                'numero_sequencial'      => 1,
                'tipo'                   => 'prazo_e_valor',
                'status'                 => 'vigente',
                'data_assinatura'        => '2026-07-20',
                'data_inicio_vigencia'   => '2026-07-20',
                'nova_data_fim'          => '2028-07-14',
                'valor_anterior_contrato' => 2800000.00,
                'valor_acrescimo'        => 420000.00,
                'percentual_acumulado'   => 15.00,
                'fundamentacao_legal'    => 'Art. 125, I e II da Lei 14.133/2021',
                'justificativa'          => 'Identificação de patologias estruturais não previstas no projeto original durante a fase de demolição.',
                'justificativa_tecnica'  => 'Laudo técnico identificou necessidade de reforço estrutural em 3 pilares e na laje do 2º pavimento. O acréscimo de prazo (6 meses) e valor (15%) são indispensáveis para garantir a segurança da edificação.',
                'observacoes'            => 'Laudo técnico anexado ao processo. Parecer jurídico favorável emitido.',
            ]);
            $this->criarWorkflowCompleto($aditivo, $userId, $roles);
        }

        // ── Contrato 005/2026 (assessoria jurídica, R$240k) — reequilíbrio ─
        $contrato = $contratos->get('005/2026');
        if ($contrato && ! $this->aditivoExiste($contrato->id, 1)) {
            $aditivo = Aditivo::create([
                'contrato_id'               => $contrato->id,
                'numero_sequencial'          => 1,
                'tipo'                       => 'reequilibrio',
                'status'                     => 'vigente',
                'data_assinatura'            => '2026-09-01',
                'data_inicio_vigencia'       => '2026-09-01',
                'valor_anterior_contrato'    => 240000.00,
                'valor_acrescimo'            => 1200.00,
                'percentual_acumulado'       => 0.50,
                'motivo_reequilibrio'        => 'Variação do IPCA acumulado no período de 12 meses acima do previsto na cláusula de reajuste.',
                'indice_utilizado'           => 'IPCA',
                'valor_anterior_reequilibrio' => 20000.00,
                'valor_reajustado'           => 21200.00,
                'fundamentacao_legal'        => 'Art. 124, II, d) da Lei 14.133/2021',
                'justificativa'              => 'Reequilíbrio econômico-financeiro com base na variação do IPCA dos últimos 12 meses.',
                'justificativa_tecnica'      => 'O contrato prevê reajuste anual pelo IPCA. A variação acumulada de 6% no período justifica o reequilíbrio do valor mensal de R$20.000 para R$21.200.',
            ]);
            $this->criarWorkflowParcial($aditivo, $userId, $roles, 1);
        }

        // ── Contrato 006/2026 (merenda escolar, R$3.6M) — supressão ───
        $contrato = $contratos->get('006/2026');
        if ($contrato && ! $this->aditivoExiste($contrato->id, 1)) {
            $aditivo = Aditivo::create([
                'contrato_id'            => $contrato->id,
                'numero_sequencial'      => 1,
                'tipo'                   => 'supressao',
                'status'                 => 'vigente',
                'data_assinatura'        => '2026-05-15',
                'data_inicio_vigencia'   => '2026-05-15',
                'valor_anterior_contrato' => 3600000.00,
                'valor_supressao'        => 360000.00,
                'percentual_acumulado'   => -10.00,
                'fundamentacao_legal'    => 'Art. 125, §1º da Lei 14.133/2021',
                'justificativa'          => 'Redução do número de escolas atendidas de 45 para 40 devido ao fechamento temporário de 5 unidades para reforma.',
                'justificativa_tecnica'  => 'Com 5 escolas em reforma e sem alunos, a demanda mensal reduziu 10%. A supressão proporcional mantém o equilíbrio econômico do contrato.',
                'observacoes'            => 'As escolas devem retornar ao escopo após conclusão das reformas.',
            ]);
            $this->criarWorkflowCompleto($aditivo, $userId, $roles);
        }

        // ── Contrato 007/2026 (vigilância, R$1.92M) — prazo aprovado ──
        $contrato = $contratos->get('007/2026');
        if ($contrato && ! $this->aditivoExiste($contrato->id, 1)) {
            $aditivo = Aditivo::create([
                'contrato_id'            => $contrato->id,
                'numero_sequencial'      => 1,
                'tipo'                   => 'prazo',
                'status'                 => 'vigente',
                'data_assinatura'        => '2026-12-01',
                'data_inicio_vigencia'   => '2027-01-01',
                'nova_data_fim'          => '2028-12-31',
                'valor_anterior_contrato' => 1920000.00,
                'percentual_acumulado'   => 0,
                'fundamentacao_legal'    => 'Art. 107 da Lei 14.133/2021',
                'justificativa'          => 'Manutenção da segurança patrimonial dos prédios municipais — serviço essencial e contínuo.',
                'justificativa_tecnica'  => 'Avaliação de desempenho positiva. Pesquisa de preços comprova vantajosidade econômica da prorrogação em relação à nova licitação.',
            ]);
            $this->criarWorkflowCompleto($aditivo, $userId, $roles);
        }

        // ── Contrato 010/2026 (medicamentos, R$4.5M) — valor + reprovado ─
        $contrato = $contratos->get('010/2026');
        if ($contrato && ! $this->aditivoExiste($contrato->id, 1)) {
            $aditivo = Aditivo::create([
                'contrato_id'            => $contrato->id,
                'numero_sequencial'      => 1,
                'tipo'                   => 'valor',
                'status'                 => 'cancelado',
                'data_assinatura'        => '2026-06-01',
                'valor_anterior_contrato' => 4500000.00,
                'valor_acrescimo'        => 1125000.00,
                'percentual_acumulado'   => 25.00,
                'fundamentacao_legal'    => 'Art. 125 da Lei 14.133/2021',
                'justificativa'          => 'Aumento de demanda por medicamentos devido à epidemia de dengue no município.',
                'justificativa_tecnica'  => 'Acréscimo de 25% no valor global para atender aumento emergencial de demanda.',
                'observacoes'            => 'Reprovado pela Controladoria por ultrapassar o limite de 25% sem justificativa de excepcionalidade suficiente.',
            ]);
            $this->criarWorkflowReprovado($aditivo, $userId, $roles, 4); // Reprovado na etapa 4
        }

        // ── Contrato 016/2025 (ponte, R$8.5M, suspenso) — alteração de cláusula ─
        $contrato = $contratos->get('016/2025');
        if ($contrato && ! $this->aditivoExiste($contrato->id, 1)) {
            $aditivo = Aditivo::create([
                'contrato_id'            => $contrato->id,
                'numero_sequencial'      => 1,
                'tipo'                   => 'alteracao_clausula',
                'status'                 => 'vigente',
                'data_assinatura'        => '2025-10-01',
                'data_inicio_vigencia'   => '2025-10-01',
                'valor_anterior_contrato' => 8500000.00,
                'percentual_acumulado'   => 0,
                'fundamentacao_legal'    => 'Art. 124, I da Lei 14.133/2021',
                'justificativa'          => 'Alteração da cláusula de medição para incluir laudos ambientais obrigatórios em cada etapa.',
                'justificativa_tecnica'  => 'Exigência do órgão ambiental estadual para emissão da Licença de Instalação condicionada à apresentação de laudos em cada medição.',
            ]);
            $this->criarWorkflowCompleto($aditivo, $userId, $roles);
        }

        // ── Contrato 021/2026 (transporte coletivo, R$45M) — misto ────
        $contrato = $contratos->get('021/2026');
        if ($contrato && ! $this->aditivoExiste($contrato->id, 1)) {
            $aditivo = Aditivo::create([
                'contrato_id'                => $contrato->id,
                'numero_sequencial'          => 1,
                'tipo'                       => 'misto',
                'status'                     => 'vigente',
                'data_assinatura'            => '2026-07-01',
                'data_inicio_vigencia'       => '2026-07-01',
                'nova_data_fim'              => '2031-06-30',
                'valor_anterior_contrato'    => 45000000.00,
                'valor_acrescimo'            => 2250000.00,
                'percentual_acumulado'       => 5.00,
                'motivo_reequilibrio'        => 'Aumento do preço do diesel impactou os custos operacionais.',
                'indice_utilizado'           => 'Diesel ANP',
                'valor_anterior_reequilibrio' => 750000.00,
                'valor_reajustado'           => 787500.00,
                'fundamentacao_legal'        => 'Art. 124 e 125 da Lei 14.133/2021',
                'justificativa'              => 'Inclusão de 3 novas linhas de ônibus + reequilíbrio pelo aumento do diesel + extensão de prazo em 6 meses.',
                'justificativa_tecnica'      => 'Estudo de viabilidade comprova necessidade das novas linhas. Planilha de custos demonstra impacto de 12% no diesel. Extensão de prazo compensa período de adaptação.',
                'parecer_juridico_obrigatorio' => true,
                'observacoes'                => 'Aditivo de maior complexidade — envolve prazo, valor e reequilíbrio simultaneamente.',
            ]);
            $this->criarWorkflowParcial($aditivo, $userId, $roles, 3);
        }
    }

    private function aditivoExiste(int $contratoId, int $numeroSequencial): bool
    {
        return Aditivo::where('contrato_id', $contratoId)
            ->where('numero_sequencial', $numeroSequencial)
            ->exists();
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
                'aprovavel_type'      => Aditivo::class,
                'aprovavel_id'        => $aditivo->id,
                'etapa'               => $etapa->value,
                'etapa_ordem'         => $etapa->ordem(),
                'role_responsavel_id' => $roleId,
                'user_id'             => $userId,
                'status'              => StatusAprovacao::Aprovado->value,
                'parecer'             => $etapa === EtapaWorkflow::Solicitacao
                    ? 'Solicitação registrada automaticamente.'
                    : 'Aprovado conforme análise do setor responsável.',
                'decided_at'          => $agora->copy()->addDays($etapa->ordem()),
                'created_at'          => $agora,
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
                'aprovavel_type'      => Aditivo::class,
                'aprovavel_id'        => $aditivo->id,
                'etapa'               => $etapa->value,
                'etapa_ordem'         => $etapa->ordem(),
                'role_responsavel_id' => $roleId,
                'user_id'             => $aprovada ? $userId : null,
                'status'              => $aprovada ? StatusAprovacao::Aprovado->value : StatusAprovacao::Pendente->value,
                'parecer'             => $aprovada
                    ? ($etapa === EtapaWorkflow::Solicitacao ? 'Solicitação registrada automaticamente.' : 'Aprovado conforme análise.')
                    : null,
                'decided_at'          => $aprovada ? $agora->copy()->addDays($etapa->ordem()) : null,
                'created_at'          => $agora,
            ]);
        }
    }

    /**
     * Cria workflow com N-1 etapas aprovadas e a N-ésima reprovada.
     */
    private function criarWorkflowReprovado(Aditivo $aditivo, int $userId, array $roles, int $etapaReprovada): void
    {
        $agora = now();

        foreach (EtapaWorkflow::cases() as $etapa) {
            $roleNome = $etapa->roleResponsavel();
            $roleId = $roles[$roleNome] ?? null;
            $ordem = $etapa->ordem();

            if ($ordem < $etapaReprovada) {
                $status = StatusAprovacao::Aprovado->value;
                $parecer = $etapa === EtapaWorkflow::Solicitacao
                    ? 'Solicitação registrada automaticamente.'
                    : 'Aprovado conforme análise.';
                $decidedAt = $agora->copy()->addDays($ordem);
                $uid = $userId;
            } elseif ($ordem === $etapaReprovada) {
                $status = StatusAprovacao::Reprovado->value;
                $parecer = 'Reprovado. O acréscimo solicitado atinge o limite máximo de 25% sem apresentar justificativa técnica que demonstre excepcionalidade conforme art. 125, §2º.';
                $decidedAt = $agora->copy()->addDays($ordem);
                $uid = $userId;
            } else {
                $status = StatusAprovacao::Pendente->value;
                $parecer = null;
                $decidedAt = null;
                $uid = null;
            }

            WorkflowAprovacao::create([
                'aprovavel_type'      => Aditivo::class,
                'aprovavel_id'        => $aditivo->id,
                'etapa'               => $etapa->value,
                'etapa_ordem'         => $ordem,
                'role_responsavel_id' => $roleId,
                'user_id'             => $uid,
                'status'              => $status,
                'parecer'             => $parecer,
                'decided_at'          => $decidedAt,
                'created_at'          => $agora,
            ]);
        }
    }
}
