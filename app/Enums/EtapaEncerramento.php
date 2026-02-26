<?php

namespace App\Enums;

enum EtapaEncerramento: string
{
    case VerificacaoFinanceira = 'verificacao_financeira';
    case TermoProvisorio = 'termo_provisorio';
    case AvaliacaoFiscal = 'avaliacao_fiscal';
    case TermoDefinitivo = 'termo_definitivo';
    case Quitacao = 'quitacao';
    case Encerrado = 'encerrado';

    public function label(): string
    {
        return match ($this) {
            self::VerificacaoFinanceira => 'Verificação Financeira',
            self::TermoProvisorio => 'Termo de Recebimento Provisório',
            self::AvaliacaoFiscal => 'Avaliação do Fiscal',
            self::TermoDefinitivo => 'Termo de Recebimento Definitivo',
            self::Quitacao => 'Quitação',
            self::Encerrado => 'Encerrado',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::VerificacaoFinanceira => 'solar:wallet-check-bold',
            self::TermoProvisorio => 'solar:clipboard-check-bold',
            self::AvaliacaoFiscal => 'solar:user-check-bold',
            self::TermoDefinitivo => 'solar:document-add-bold',
            self::Quitacao => 'solar:check-circle-bold',
            self::Encerrado => 'solar:lock-bold',
        };
    }

    public function ordem(): int
    {
        return match ($this) {
            self::VerificacaoFinanceira => 1,
            self::TermoProvisorio => 2,
            self::AvaliacaoFiscal => 3,
            self::TermoDefinitivo => 4,
            self::Quitacao => 5,
            self::Encerrado => 6,
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::VerificacaoFinanceira => 'primary',
            self::TermoProvisorio => 'info',
            self::AvaliacaoFiscal => 'warning',
            self::TermoDefinitivo => 'info',
            self::Quitacao => 'success',
            self::Encerrado => 'secondary',
        };
    }

    public function proxima(): ?self
    {
        return match ($this) {
            self::VerificacaoFinanceira => self::TermoProvisorio,
            self::TermoProvisorio => self::AvaliacaoFiscal,
            self::AvaliacaoFiscal => self::TermoDefinitivo,
            self::TermoDefinitivo => self::Quitacao,
            self::Quitacao => self::Encerrado,
            self::Encerrado => null,
        };
    }

    /**
     * Retorna todas as etapas anteriores (já concluídas para chegar aqui).
     */
    public function etapasAnteriores(): array
    {
        $todas = self::cases();
        $result = [];

        foreach ($todas as $etapa) {
            if ($etapa === $this) {
                break;
            }
            $result[] = $etapa;
        }

        return $result;
    }
}
