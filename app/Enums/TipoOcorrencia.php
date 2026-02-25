<?php

namespace App\Enums;

enum TipoOcorrencia: string
{
    case Atraso = 'atraso';
    case Inconformidade = 'inconformidade';
    case Notificacao = 'notificacao';
    case Medicao = 'medicao';
    case Penalidade = 'penalidade';
    case Outros = 'outros';

    public function label(): string
    {
        return match ($this) {
            self::Atraso => 'Atraso na Execucao',
            self::Inconformidade => 'Inconformidade Contratual',
            self::Notificacao => 'Notificacao ao Contratado',
            self::Medicao => 'Medicao/Avaliacao',
            self::Penalidade => 'Aplicacao de Penalidade',
            self::Outros => 'Outros',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Atraso => 'solar:clock-circle-bold',
            self::Inconformidade => 'solar:danger-triangle-bold',
            self::Notificacao => 'solar:letter-bold',
            self::Medicao => 'solar:ruler-bold',
            self::Penalidade => 'solar:shield-warning-bold',
            self::Outros => 'solar:document-text-bold',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Atraso => 'warning',
            self::Inconformidade => 'danger',
            self::Notificacao => 'info',
            self::Medicao => 'primary',
            self::Penalidade => 'danger',
            self::Outros => 'secondary',
        };
    }
}
