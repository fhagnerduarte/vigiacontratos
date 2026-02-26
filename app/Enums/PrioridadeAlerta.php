<?php

namespace App\Enums;

enum PrioridadeAlerta: string
{
    case Informativo = 'informativo';
    case Atencao = 'atencao';
    case Urgente = 'urgente';

    public function label(): string
    {
        return match ($this) {
            self::Informativo => 'Informativo',
            self::Atencao => 'Atenção',
            self::Urgente => 'Urgente',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Informativo => 'info',
            self::Atencao => 'warning',
            self::Urgente => 'danger',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Informativo => 'solar:info-circle-bold',
            self::Atencao => 'solar:danger-triangle-bold',
            self::Urgente => 'solar:alarm-bold',
        };
    }
}
