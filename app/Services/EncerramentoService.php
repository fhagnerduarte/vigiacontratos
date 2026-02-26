<?php

namespace App\Services;

use App\Enums\EtapaEncerramento;
use App\Enums\StatusAlerta;
use App\Enums\StatusContrato;
use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\Encerramento;
use App\Models\User;

class EncerramentoService
{
    /**
     * Inicia o processo de encerramento de um contrato.
     * Valida que o contrato pode ser encerrado (vigente ou vencido).
     */
    public static function iniciar(Contrato $contrato, User $user, string $ip): Encerramento
    {
        if ($contrato->encerramento) {
            throw new \RuntimeException('Processo de encerramento já iniciado para este contrato.');
        }

        if (!in_array($contrato->status, [StatusContrato::Vigente, StatusContrato::Vencido])) {
            throw new \RuntimeException(
                'Apenas contratos vigentes ou vencidos podem ser encerrados. Status atual: ' . $contrato->status->label()
            );
        }

        $encerramento = Encerramento::create([
            'contrato_id' => $contrato->id,
            'etapa_atual' => EtapaEncerramento::VerificacaoFinanceira->value,
            'data_inicio' => now(),
        ]);

        AuditoriaService::registrar(
            $contrato,
            'encerramento_iniciado',
            null,
            'Processo de encerramento iniciado',
            $user,
            $ip
        );

        return $encerramento;
    }

    /**
     * Etapa 1: Verificação Financeira.
     * Verifica se todas as obrigações financeiras foram cumpridas.
     */
    public static function verificarFinanceiro(
        Encerramento $encerramento,
        bool $ok,
        ?string $observacoes,
        User $user,
        string $ip
    ): Encerramento {
        self::validarEtapa($encerramento, EtapaEncerramento::VerificacaoFinanceira);

        $encerramento->update([
            'verificacao_financeira_ok' => $ok,
            'verificacao_financeira_por' => $user->id,
            'verificacao_financeira_em' => now(),
            'verificacao_financeira_obs' => $observacoes,
            'etapa_atual' => EtapaEncerramento::TermoProvisorio->value,
        ]);

        AuditoriaService::registrar(
            $encerramento->contrato,
            'encerramento_verificacao_financeira',
            null,
            $ok ? 'Verificação financeira aprovada' : 'Verificação financeira com ressalvas',
            $user,
            $ip
        );

        return $encerramento->fresh();
    }

    /**
     * Etapa 2: Registro do Termo de Recebimento Provisório.
     */
    public static function registrarTermoProvisorio(
        Encerramento $encerramento,
        int $prazoDias,
        User $user,
        string $ip
    ): Encerramento {
        self::validarEtapa($encerramento, EtapaEncerramento::TermoProvisorio);

        $encerramento->update([
            'termo_provisorio_em' => now(),
            'termo_provisorio_por' => $user->id,
            'termo_provisorio_prazo_dias' => $prazoDias,
            'etapa_atual' => EtapaEncerramento::AvaliacaoFiscal->value,
        ]);

        AuditoriaService::registrar(
            $encerramento->contrato,
            'encerramento_termo_provisorio',
            null,
            "Termo de recebimento provisório registrado. Prazo: {$prazoDias} dias.",
            $user,
            $ip
        );

        return $encerramento->fresh();
    }

    /**
     * Etapa 3: Avaliação do Fiscal.
     * Nota de 1 a 10 sobre o desempenho do fornecedor.
     */
    public static function registrarAvaliacaoFiscal(
        Encerramento $encerramento,
        float $nota,
        ?string $observacoes,
        User $user,
        string $ip
    ): Encerramento {
        self::validarEtapa($encerramento, EtapaEncerramento::AvaliacaoFiscal);

        if ($nota < 1 || $nota > 10) {
            throw new \RuntimeException('Nota de avaliação deve ser entre 1 e 10.');
        }

        $encerramento->update([
            'avaliacao_fiscal_nota' => $nota,
            'avaliacao_fiscal_obs' => $observacoes,
            'avaliacao_fiscal_por' => $user->id,
            'avaliacao_fiscal_em' => now(),
            'etapa_atual' => EtapaEncerramento::TermoDefinitivo->value,
        ]);

        AuditoriaService::registrar(
            $encerramento->contrato,
            'encerramento_avaliacao_fiscal',
            null,
            "Avaliação fiscal registrada. Nota: {$nota}/10.",
            $user,
            $ip
        );

        return $encerramento->fresh();
    }

    /**
     * Etapa 4: Registro do Termo de Recebimento Definitivo.
     */
    public static function registrarTermoDefinitivo(
        Encerramento $encerramento,
        User $user,
        string $ip
    ): Encerramento {
        self::validarEtapa($encerramento, EtapaEncerramento::TermoDefinitivo);

        $encerramento->update([
            'termo_definitivo_em' => now(),
            'termo_definitivo_por' => $user->id,
            'etapa_atual' => EtapaEncerramento::Quitacao->value,
        ]);

        AuditoriaService::registrar(
            $encerramento->contrato,
            'encerramento_termo_definitivo',
            null,
            'Termo de recebimento definitivo registrado.',
            $user,
            $ip
        );

        return $encerramento->fresh();
    }

    /**
     * Etapa 5: Quitação.
     */
    public static function registrarQuitacao(
        Encerramento $encerramento,
        ?string $observacoes,
        User $user,
        string $ip
    ): Encerramento {
        self::validarEtapa($encerramento, EtapaEncerramento::Quitacao);

        $encerramento->update([
            'quitacao_em' => now(),
            'quitacao_por' => $user->id,
            'quitacao_obs' => $observacoes,
            'etapa_atual' => EtapaEncerramento::Encerrado->value,
            'data_encerramento_efetivo' => now()->toDateString(),
        ]);

        // Etapa final: atualiza o contrato para Encerrado e resolve todos os alertas
        self::concluirEncerramento($encerramento->contrato, $user, $ip);

        AuditoriaService::registrar(
            $encerramento->contrato,
            'encerramento_quitacao',
            null,
            'Quitação registrada. Contrato encerrado formalmente.',
            $user,
            $ip
        );

        return $encerramento->fresh();
    }

    /**
     * Conclui o encerramento: muda status do contrato e resolve alertas pendentes.
     */
    private static function concluirEncerramento(Contrato $contrato, User $user, string $ip): void
    {
        $statusAnterior = $contrato->status->value;

        $contrato->updateQuietly([
            'status' => StatusContrato::Encerrado->value,
            'is_irregular' => false,
        ]);

        AuditoriaService::registrar(
            $contrato,
            'status',
            $statusAnterior,
            StatusContrato::Encerrado->value,
            $user,
            $ip
        );

        // Resolver todos os alertas pendentes do contrato
        Alerta::where('contrato_id', $contrato->id)
            ->whereIn('status', [
                StatusAlerta::Pendente->value,
                StatusAlerta::Enviado->value,
                StatusAlerta::Visualizado->value,
            ])
            ->update([
                'status' => StatusAlerta::Resolvido->value,
                'resolvido_em' => now(),
                'resolvido_por' => $user->id,
            ]);
    }

    /**
     * Valida que o encerramento está na etapa correta para a ação solicitada.
     */
    private static function validarEtapa(Encerramento $encerramento, EtapaEncerramento $esperada): void
    {
        if ($encerramento->etapa_atual !== $esperada) {
            throw new \RuntimeException(
                "Etapa incorreta. Esperada: {$esperada->label()}. " .
                "Atual: {$encerramento->etapa_atual->label()}."
            );
        }
    }
}
