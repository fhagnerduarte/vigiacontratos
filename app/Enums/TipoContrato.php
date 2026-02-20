<?php

namespace App\Enums;

enum TipoContrato: string
{
    case Servico = 'servico';
    case Obra = 'obra';
    case Compra = 'compra';
    case Locacao = 'locacao';

    public function label(): string
    {
        return match ($this) {
            self::Servico => 'Servico',
            self::Obra => 'Obra',
            self::Compra => 'Compra',
            self::Locacao => 'Locacao',
        };
    }
}
