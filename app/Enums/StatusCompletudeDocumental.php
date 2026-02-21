<?php

namespace App\Enums;

enum StatusCompletudeDocumental: string
{
    case Completo = 'completo';
    case Parcial = 'parcial';
    case Incompleto = 'incompleto';

    public function label(): string
    {
        return match ($this) {
            self::Completo => 'Completo',
            self::Parcial => 'Parcial',
            self::Incompleto => 'Incompleto',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Completo => 'success',
            self::Parcial => 'warning',
            self::Incompleto => 'danger',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Completo => 'solar:check-circle-bold',
            self::Parcial => 'solar:danger-triangle-bold',
            self::Incompleto => 'solar:close-circle-bold',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::Completo => 'Documentacao Completa',
            self::Parcial => 'Documentacao Parcial — itens pendentes no checklist',
            self::Incompleto => 'Documentacao Incompleta — contrato original ausente',
        };
    }
}
