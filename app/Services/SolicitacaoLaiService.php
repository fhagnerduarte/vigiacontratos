<?php

namespace App\Services;

use App\Enums\ClassificacaoRespostaLai;
use App\Enums\StatusSolicitacaoLai;
use App\Models\HistoricoSolicitacaoLai;
use App\Models\SolicitacaoLai;
use App\Models\User;

class SolicitacaoLaiService
{
    /**
     * Cria uma nova solicitacao LAI com protocolo unico.
     * LAI art. 11: prazo de 20 dias para resposta.
     */
    public static function criar(array $dados): SolicitacaoLai
    {
        $protocolo = self::gerarProtocolo();
        $prazoLegal = now()->addDays(20)->toDateString();

        $solicitacao = SolicitacaoLai::create(array_merge($dados, [
            'protocolo' => $protocolo,
            'status' => StatusSolicitacaoLai::Recebida->value,
            'prazo_legal' => $prazoLegal,
            'tenant_id' => app('tenant')->id,
        ]));

        self::registrarHistorico($solicitacao, null, StatusSolicitacaoLai::Recebida, 'Solicitacao registrada pelo cidadao');

        return $solicitacao;
    }

    /**
     * Marca solicitacao como em analise.
     */
    public static function analisar(SolicitacaoLai $solicitacao, User $user, string $ip): void
    {
        $statusAnterior = $solicitacao->status;

        $solicitacao->update(['status' => StatusSolicitacaoLai::EmAnalise->value]);

        self::registrarHistorico($solicitacao, $statusAnterior, StatusSolicitacaoLai::EmAnalise, null, $user);

        AuditoriaService::registrar(
            $solicitacao,
            'status',
            $statusAnterior->value,
            StatusSolicitacaoLai::EmAnalise->value,
            $user,
            $ip
        );
    }

    /**
     * Registra resposta a solicitacao.
     * LAI art. 11, §1o: a resposta deve conter classificacao.
     */
    public static function responder(
        SolicitacaoLai $solicitacao,
        string $resposta,
        ClassificacaoRespostaLai $classificacao,
        User $user,
        string $ip
    ): void {
        if (!$solicitacao->status->permiteResposta()) {
            throw new \RuntimeException('Solicitacao nao permite resposta no status atual.');
        }

        $statusAnterior = $solicitacao->status;

        $solicitacao->update([
            'status' => StatusSolicitacaoLai::Respondida->value,
            'resposta' => $resposta,
            'classificacao_resposta' => $classificacao->value,
            'respondido_por' => $user->id,
            'data_resposta' => now(),
        ]);

        self::registrarHistorico(
            $solicitacao,
            $statusAnterior,
            StatusSolicitacaoLai::Respondida,
            'Resposta registrada: ' . $classificacao->label(),
            $user
        );

        AuditoriaService::registrar(
            $solicitacao,
            'status',
            $statusAnterior->value,
            StatusSolicitacaoLai::Respondida->value,
            $user,
            $ip
        );
    }

    /**
     * Prorroga o prazo da solicitacao.
     * LAI art. 11, §2o: prorrogacao unica de +10 dias, com justificativa.
     */
    public static function prorrogar(SolicitacaoLai $solicitacao, string $justificativa, User $user, string $ip): void
    {
        if (!$solicitacao->is_prorrogavel) {
            throw new \RuntimeException('Solicitacao nao pode ser prorrogada. Limite de 1 prorrogacao (LAI art. 11, §2o).');
        }

        $statusAnterior = $solicitacao->status;
        $prazoEstendido = $solicitacao->prazo_legal->copy()->addDays(10)->toDateString();

        $solicitacao->update([
            'status' => StatusSolicitacaoLai::Prorrogada->value,
            'data_prorrogacao' => now(),
            'justificativa_prorrogacao' => $justificativa,
            'prazo_estendido' => $prazoEstendido,
        ]);

        self::registrarHistorico(
            $solicitacao,
            $statusAnterior,
            StatusSolicitacaoLai::Prorrogada,
            'Prazo prorrogado por +10 dias. Justificativa: ' . $justificativa,
            $user
        );

        AuditoriaService::registrar(
            $solicitacao,
            'status',
            $statusAnterior->value,
            StatusSolicitacaoLai::Prorrogada->value,
            $user,
            $ip
        );
    }

    /**
     * Indefere a solicitacao com justificativa obrigatoria.
     */
    public static function indeferir(
        SolicitacaoLai $solicitacao,
        string $justificativa,
        ClassificacaoRespostaLai $classificacao,
        User $user,
        string $ip
    ): void {
        if ($solicitacao->status->isFinalizado()) {
            throw new \RuntimeException('Solicitacao ja esta finalizada.');
        }

        $statusAnterior = $solicitacao->status;

        $solicitacao->update([
            'status' => StatusSolicitacaoLai::Indeferida->value,
            'resposta' => $justificativa,
            'classificacao_resposta' => $classificacao->value,
            'respondido_por' => $user->id,
            'data_resposta' => now(),
        ]);

        self::registrarHistorico(
            $solicitacao,
            $statusAnterior,
            StatusSolicitacaoLai::Indeferida,
            'Indeferida: ' . $justificativa,
            $user
        );

        AuditoriaService::registrar(
            $solicitacao,
            'status',
            $statusAnterior->value,
            StatusSolicitacaoLai::Indeferida->value,
            $user,
            $ip
        );
    }

    /**
     * Gera protocolo unico no formato LAI-{YYYY}-{SEQ}.
     */
    public static function gerarProtocolo(): string
    {
        $ano = now()->year;
        $prefixo = "LAI-{$ano}-";

        $ultimo = SolicitacaoLai::where('protocolo', 'like', $prefixo . '%')
            ->orderByDesc('protocolo')
            ->value('protocolo');

        if ($ultimo) {
            $seq = (int) substr($ultimo, strlen($prefixo)) + 1;
        } else {
            $seq = 1;
        }

        return $prefixo . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Retorna resumo estatistico das solicitacoes LAI.
     */
    public static function resumo(): array
    {
        $total = SolicitacaoLai::count();
        $pendentes = SolicitacaoLai::pendentes()->count();
        $respondidas = SolicitacaoLai::porStatus(StatusSolicitacaoLai::Respondida)->count();
        $vencidas = SolicitacaoLai::vencidas()->count();

        $tempoMedio = SolicitacaoLai::whereNotNull('data_resposta')
            ->selectRaw('AVG(DATEDIFF(data_resposta, created_at)) as media')
            ->value('media');

        return [
            'total' => $total,
            'pendentes' => $pendentes,
            'respondidas' => $respondidas,
            'vencidas' => $vencidas,
            'tempo_medio_resposta' => round((float) $tempoMedio, 1),
        ];
    }

    /**
     * Registra entrada no historico imutavel.
     */
    private static function registrarHistorico(
        SolicitacaoLai $solicitacao,
        ?StatusSolicitacaoLai $statusAnterior,
        StatusSolicitacaoLai $statusNovo,
        ?string $observacao = null,
        ?User $user = null
    ): void {
        HistoricoSolicitacaoLai::create([
            'solicitacao_lai_id' => $solicitacao->id,
            'status_anterior' => $statusAnterior?->value,
            'status_novo' => $statusNovo->value,
            'observacao' => $observacao,
            'user_id' => $user?->id,
            'created_at' => now(),
        ]);
    }
}
