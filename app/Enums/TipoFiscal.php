<?php

namespace App\Enums;

enum TipoFiscal: string
{
    case Titular = 'titular';
    case Substituto = 'substituto';

    public function label(): string
    {
        return match ($this) {
            self::Titular => 'Titular',
            self::Substituto => 'Substituto',
        };
    }
}
