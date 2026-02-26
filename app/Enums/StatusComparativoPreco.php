<?php

namespace App\Enums;

enum StatusComparativoPreco: string
{
    case Adequado = 'adequado';
    case Atencao = 'atencao';
    case Sobrepreco = 'sobrepreco';

    public function label(): string
    {
        return match ($this) {
            self::Adequado => 'Adequado',
            self::Atencao => 'Atencao',
            self::Sobrepreco => 'Sobrepreco',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Adequado => 'success',
            self::Atencao => 'warning',
            self::Sobrepreco => 'danger',
        };
    }
}
