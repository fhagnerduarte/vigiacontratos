<?php

namespace App\Enums;

enum FaseContratual: string
{
    case Planejamento = 'planejamento';
    case Formalizacao = 'formalizacao';
    case Publicacao = 'publicacao';
    case Fiscalizacao = 'fiscalizacao';
    case ExecucaoFinanceira = 'execucao_financeira';
    case GestaoAditivos = 'gestao_aditivos';
    case Encerramento = 'encerramento';

    public function label(): string
    {
        return match ($this) {
            self::Planejamento => 'Planejamento',
            self::Formalizacao => 'Formalização',
            self::Publicacao => 'Publicação',
            self::Fiscalizacao => 'Fiscalização',
            self::ExecucaoFinanceira => 'Execução Financeira',
            self::GestaoAditivos => 'Gestão de Aditivos',
            self::Encerramento => 'Encerramento',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Planejamento => 'solar:clipboard-list-bold',
            self::Formalizacao => 'solar:document-bold',
            self::Publicacao => 'solar:global-bold',
            self::Fiscalizacao => 'solar:shield-check-bold',
            self::ExecucaoFinanceira => 'solar:wallet-money-bold',
            self::GestaoAditivos => 'solar:add-circle-bold',
            self::Encerramento => 'solar:check-circle-bold',
        };
    }

    public function ordem(): int
    {
        return match ($this) {
            self::Planejamento => 1,
            self::Formalizacao => 2,
            self::Publicacao => 3,
            self::Fiscalizacao => 4,
            self::ExecucaoFinanceira => 5,
            self::GestaoAditivos => 6,
            self::Encerramento => 7,
        };
    }
}
