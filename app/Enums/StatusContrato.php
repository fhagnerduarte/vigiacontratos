<?php

namespace App\Enums;

enum StatusContrato: string
{
    case Vigente = 'vigente';
    case Vencido = 'vencido';
    case Cancelado = 'cancelado';
    case Suspenso = 'suspenso';
    case Encerrado = 'encerrado';
    case Rescindido = 'rescindido';

    public function label(): string
    {
        return match ($this) {
            self::Vigente => 'Vigente',
            self::Vencido => 'Vencido',
            self::Cancelado => 'Cancelado',
            self::Suspenso => 'Suspenso',
            self::Encerrado => 'Encerrado',
            self::Rescindido => 'Rescindido',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Vigente => 'success',
            self::Vencido => 'danger',
            self::Cancelado => 'secondary',
            self::Suspenso => 'warning',
            self::Encerrado => 'info',
            self::Rescindido => 'danger',
        };
    }
}
