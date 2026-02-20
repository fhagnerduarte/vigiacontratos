<?php

namespace App\Enums;

enum CategoriaContrato: string
{
    case Essencial = 'essencial';
    case NaoEssencial = 'nao_essencial';

    public function label(): string
    {
        return match ($this) {
            self::Essencial => 'Essencial',
            self::NaoEssencial => 'Nao Essencial',
        };
    }
}
