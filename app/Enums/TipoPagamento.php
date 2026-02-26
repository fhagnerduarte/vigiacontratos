<?php

namespace App\Enums;

enum TipoPagamento: string
{
    case Mensal = 'mensal';
    case PorMedicao = 'por_medicao';
    case Parcelado = 'parcelado';
    case Unico = 'unico';

    public function label(): string
    {
        return match ($this) {
            self::Mensal => 'Mensal',
            self::PorMedicao => 'Por Medição',
            self::Parcelado => 'Parcelado',
            self::Unico => 'Único',
        };
    }
}
