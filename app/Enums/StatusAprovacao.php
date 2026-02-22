<?php

namespace App\Enums;

enum StatusAprovacao: string
{
    case Pendente = 'pendente';
    case Aprovado = 'aprovado';
    case Reprovado = 'reprovado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Aprovado => 'Aprovado',
            self::Reprovado => 'Reprovado',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Pendente => 'warning',
            self::Aprovado => 'success',
            self::Reprovado => 'danger',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Pendente => 'solar:clock-circle-bold',
            self::Aprovado => 'solar:check-circle-bold',
            self::Reprovado => 'solar:close-circle-bold',
        };
    }
}
