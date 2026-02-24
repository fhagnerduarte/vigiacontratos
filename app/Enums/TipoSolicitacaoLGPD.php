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
            self::Anonimizacao => 'Anonimizacao de dados',
            self::Exclusao => 'Exclusao de dados',
            self::Portabilidade => 'Portabilidade de dados',
            self::Retificacao => 'Retificacao de dados',
            self::Revogacao => 'Revogacao de consentimento',
        };
    }
}
