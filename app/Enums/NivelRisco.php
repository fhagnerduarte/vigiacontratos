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
            self::Medio => 'Atencao',
            self::Alto => 'Critico',
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
            self::Baixo => 'Contrato em situacao regular. Sem riscos identificados.',
            self::Medio => 'Contrato requer atencao. Riscos moderados identificados.',
            self::Alto => 'Contrato em situacao critica. Riscos altos requerem acao imediata.',
        };
    }
}
