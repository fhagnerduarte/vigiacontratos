<?php

namespace App\Enums;

enum StatusAditivo: string
{
    case Vigente = 'vigente';
    case Vencido = 'vencido';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Vigente => 'Vigente',
            self::Vencido => 'Vencido',
            self::Cancelado => 'Cancelado',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Vigente => 'success',
            self::Vencido => 'warning',
            self::Cancelado => 'danger',
        };
    }
}
