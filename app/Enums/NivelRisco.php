<?php

namespace App\Enums;

enum NivelRisco: string
{
    case Baixo = 'baixo';
    case Medio = 'medio';
    case Alto = 'alto';

    public function label(): string
    {
        return match ($this) {
            self::Baixo => 'Baixo',
            self::Medio => 'Medio',
            self::Alto => 'Alto',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Baixo => 'success',
            self::Medio => 'warning',
            self::Alto => 'danger',
        };
    }
}
