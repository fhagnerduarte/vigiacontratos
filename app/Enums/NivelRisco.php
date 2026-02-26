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
            self::Baixo => 'Regular',
            self::Medio => 'Atenção',
            self::Alto => 'Crítico',
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

    public function icone(): string
    {
        return match ($this) {
            self::Baixo => 'solar:shield-check-bold',
            self::Medio => 'solar:shield-warning-bold',
            self::Alto => 'solar:danger-triangle-bold',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::Baixo => 'Contrato em situação regular. Sem riscos identificados.',
            self::Medio => 'Contrato requer atenção. Riscos moderados identificados.',
            self::Alto => 'Contrato em situação crítica. Riscos altos requerem ação imediata.',
        };
    }
}
