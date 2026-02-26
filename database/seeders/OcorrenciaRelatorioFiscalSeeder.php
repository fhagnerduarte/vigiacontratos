<?php

namespace Database\Seeders;

use App\Enums\TipoOcorrencia;
use App\Models\Contrato;
use App\Models\Ocorrencia;
use App\Models\RelatorioFiscal;
use App\Models\User;
use Illuminate\Database\Seeder;

class OcorrenciaRelatorioFiscalSeeder extends Seeder
{
    public function run(): void
    {
        $contratos = Contrato::withoutGlobalScopes()
            ->whereHas('fiscalAtual')
            ->whereIn('status', ['vigente', 'vencido', 'suspenso'])
            ->get();

        if ($contratos->isEmpty()) {
            return;
        }

        $user = User::first();
        if (! $user) {
            return;
        }

        foreach ($contratos as $contrato) {
            $fiscal = $contrato->fiscalAtual;

            $this->criarOcorrencias($contrato, $fiscal->id, $user->id);
            $this->criarRelatoriosFiscais($contrato, $fiscal->id, $user->id);
        }
    }

    private function criarOcorrencias(Contrato $contrato, int $fiscalId, int $userId): void
    {
        $ocorrencias = $this->getOcorrenciasParaContrato($contrato);

        foreach ($ocorrencias as $ocorrencia) {
            $existing = Ocorrencia::where('contrato_id', $contrato->id)
                ->where('tipo_ocorrencia', $ocorrencia['tipo_ocorrencia'])
                ->where('data_ocorrencia', $ocorrencia['data_ocorrencia'])
                ->exists();

            if (! $existing) {
                Ocorrencia::create(array_merge($ocorrencia, [
                    'contrato_id'    => $contrato->id,
                    'fiscal_id'      => $fiscalId,
                    'registrado_por' => $userId,
                    'resolvida_por'  => $ocorrencia['resolvida'] ? $userId : null,
                    'resolvida_em'   => $ocorrencia['resolvida'] ? now()->subDays(rand(1, 10)) : null,
                ]));
            }
        }
    }

    private function getOcorrenciasParaContrato(Contrato $contrato): array
    {
        $inicio = $contrato->data_inicio;

        // Base: todos os contratos recebem ao menos 1 ocorrência
        $ocorrencias = [
            [
                'data_ocorrencia'   => $inicio->copy()->addDays(30)->format('Y-m-d'),
                'tipo_ocorrencia'   => TipoOcorrencia::Medicao->value,
                'descricao'         => "Medição/avaliação mensal do contrato {$contrato->numero}. Serviços executados conforme previsto no cronograma.",
                'providencia'       => null,
                'prazo_providencia' => null,
                'resolvida'         => true,
                'observacoes'       => null,
            ],
        ];

        // Contratos de alto risco recebem mais ocorrências
        if ($contrato->score_risco >= 50) {
            $ocorrencias[] = [
                'data_ocorrencia'   => $inicio->copy()->addDays(45)->format('Y-m-d'),
                'tipo_ocorrencia'   => TipoOcorrencia::Atraso->value,
                'descricao'         => "Atraso de 5 dias úteis na entrega do relatório mensal de atividades pelo contratado.",
                'providencia'       => "Notificação formal enviada ao representante legal da empresa contratada.",
                'prazo_providencia' => $inicio->copy()->addDays(55)->format('Y-m-d'),
                'resolvida'         => true,
                'observacoes'       => 'Contratado apresentou justificativa aceita pela fiscalização.',
            ];

            $ocorrencias[] = [
                'data_ocorrencia'   => $inicio->copy()->addDays(75)->format('Y-m-d'),
                'tipo_ocorrencia'   => TipoOcorrencia::Inconformidade->value,
                'descricao'         => "Material entregue em desacordo com as especificações do termo de referência.",
                'providencia'       => "Solicitada substituição dos itens não conformes no prazo de 10 dias úteis.",
                'prazo_providencia' => $inicio->copy()->addDays(90)->format('Y-m-d'),
                'resolvida'         => false,
                'observacoes'       => 'Aguardando substituição pelo fornecedor.',
            ];
        }

        // Contratos de obra recebem ocorrência de notificação
        if ($contrato->tipo === 'obra') {
            $ocorrencias[] = [
                'data_ocorrencia'   => $inicio->copy()->addDays(60)->format('Y-m-d'),
                'tipo_ocorrencia'   => TipoOcorrencia::Notificacao->value,
                'descricao'         => "Notificação ao contratado sobre necessidade de adequação do canteiro de obras às normas de segurança (NR-18).",
                'providencia'       => "Empresa notificada formalmente. Prazo de 15 dias para regularização.",
                'prazo_providencia' => $inicio->copy()->addDays(75)->format('Y-m-d'),
                'resolvida'         => true,
                'observacoes'       => 'Canteiro regularizado conforme vistoria de 10 dias após notificação.',
            ];
        }

        // Contrato suspenso recebe penalidade
        if ($contrato->status === 'suspenso') {
            $ocorrencias[] = [
                'data_ocorrencia'   => $inicio->copy()->addDays(90)->format('Y-m-d'),
                'tipo_ocorrencia'   => TipoOcorrencia::Penalidade->value,
                'descricao'         => "Aplicação de multa contratual de 2% sobre o valor global por descumprimento de cláusula contratual.",
                'providencia'       => "Processo administrativo instaurado para apuração. Defesa prévia concedida ao contratado.",
                'prazo_providencia' => $inicio->copy()->addDays(120)->format('Y-m-d'),
                'resolvida'         => false,
                'observacoes'       => 'Aguardando decisão do processo administrativo.',
            ];
        }

        // Alguns contratos vigentes com ocorrência "outros" pendente
        if (in_array($contrato->numero, ['009/2026', '012/2026', '022/2026'])) {
            $ocorrencias[] = [
                'data_ocorrencia'   => now()->subDays(15)->format('Y-m-d'),
                'tipo_ocorrencia'   => TipoOcorrencia::Outros->value,
                'descricao'         => "Solicitação de esclarecimento sobre divergência entre valores da nota fiscal e o cronograma de desembolso.",
                'providencia'       => "Ofício encaminhado ao setor financeiro para conferência.",
                'prazo_providencia' => now()->addDays(10)->format('Y-m-d'),
                'resolvida'         => false,
                'observacoes'       => null,
            ];
        }

        return $ocorrencias;
    }

    private function criarRelatoriosFiscais(Contrato $contrato, int $fiscalId, int $userId): void
    {
        $inicio = $contrato->data_inicio;
        $mesesAtivos = min($inicio->diffInMonths(now()), 4); // Até 4 relatórios

        if ($mesesAtivos < 1) {
            return;
        }

        for ($i = 1; $i <= $mesesAtivos; $i++) {
            $periodoInicio = $inicio->copy()->addMonths($i - 1)->startOfMonth();
            $periodoFim = $inicio->copy()->addMonths($i - 1)->endOfMonth();

            $existing = RelatorioFiscal::where('contrato_id', $contrato->id)
                ->where('periodo_inicio', $periodoInicio->format('Y-m-d'))
                ->exists();

            if ($existing) {
                continue;
            }

            // Contratos de alto risco têm notas menores e não-conformidades
            $altoRisco = $contrato->score_risco >= 50;
            $conforme = $altoRisco ? ($i % 3 !== 0) : true; // A cada 3 meses, não conforme
            $nota = $altoRisco ? rand(4, 7) : rand(7, 10);

            RelatorioFiscal::create([
                'contrato_id'            => $contrato->id,
                'fiscal_id'              => $fiscalId,
                'periodo_inicio'         => $periodoInicio->format('Y-m-d'),
                'periodo_fim'            => $periodoFim->format('Y-m-d'),
                'descricao_atividades'   => $this->getDescricaoRelatorio($contrato, $periodoInicio, $periodoFim),
                'conformidade_geral'     => $conforme,
                'nota_desempenho'        => $nota,
                'ocorrencias_no_periodo' => $altoRisco ? rand(1, 3) : rand(0, 1),
                'observacoes'            => $conforme ? null : 'Identificadas pendências que demandam atenção no próximo período.',
                'registrado_por'         => $userId,
            ]);
        }
    }

    private function getDescricaoRelatorio(Contrato $contrato, $inicio, $fim): string
    {
        $periodo = $inicio->format('d/m/Y') . ' a ' . $fim->format('d/m/Y');

        return match ($contrato->tipo) {
            'servico' => "Acompanhamento da prestação de serviços do contrato {$contrato->numero} no período de {$periodo}. Verificação de conformidade com SLA, qualidade dos serviços e cumprimento de obrigações contratuais.",
            'obra' => "Fiscalização da execução da obra — contrato {$contrato->numero} — período {$periodo}. Verificação do cronograma físico-financeiro, qualidade dos materiais e conformidade com o projeto executivo.",
            'compra' => "Acompanhamento das entregas do contrato {$contrato->numero} no período de {$periodo}. Conferência de quantidades, especificações técnicas e prazos de entrega.",
            'locacao' => "Fiscalização dos bens locados — contrato {$contrato->numero} — período {$periodo}. Verificação do estado de conservação e disponibilidade dos veículos/equipamentos.",
            default => "Relatório de fiscalização do contrato {$contrato->numero} referente ao período de {$periodo}.",
        };
    }
}
