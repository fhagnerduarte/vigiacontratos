<?php

namespace App\Enums;

enum TipoSolicitacaoLGPD: string
{
    case Anonimizacao = 'anonimizacao';
    case Exclusao = 'exclusao';
    case Portabilidade = 'portabilidade';
    case Retificacao = 'retificacao';
    case Revogacao = 'revogacao';

    public function label(): string
    {
        return match ($this) {
            self::Anonimizacao => 'Anonimização de dados',
            self::Exclusao => 'Exclusão de dados',
            self::Portabilidade => 'Portabilidade de dados',
            self::Retificacao => 'Retificação de dados',
            self::Revogacao => 'Revogação de consentimento',
        };
    }
}
