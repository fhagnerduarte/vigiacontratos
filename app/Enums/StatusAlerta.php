<?php

namespace App\Enums;

enum StatusAlerta: string
{
    case Pendente = 'pendente';
    case Enviado = 'enviado';
    case Visualizado = 'visualizado';
    case Resolvido = 'resolvido';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Enviado => 'Enviado',
            self::Visualizado => 'Visualizado',
            self::Resolvido => 'Resolvido',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Pendente => 'warning',
            self::Enviado => 'info',
            self::Visualizado => 'primary',
            self::Resolvido => 'success',
        };
    }
}
