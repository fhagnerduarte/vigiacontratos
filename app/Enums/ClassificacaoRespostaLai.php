<?php

namespace App\Enums;

enum ClassificacaoRespostaLai: string
{
    case Deferida = 'deferida';
    case ParcialmenteDeferida = 'parcialmente_deferida';
    case Indeferida = 'indeferida';

    public function label(): string
    {
        return match ($this) {
            self::Deferida => 'Deferida',
            self::ParcialmenteDeferida => 'Parcialmente Deferida',
            self::Indeferida => 'Indeferida',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Deferida => 'solar:check-circle-bold',
            self::ParcialmenteDeferida => 'solar:minus-circle-bold',
            self::Indeferida => 'solar:close-circle-bold',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Deferida => 'success',
            self::ParcialmenteDeferida => 'warning',
            self::Indeferida => 'danger',
        };
    }
}
