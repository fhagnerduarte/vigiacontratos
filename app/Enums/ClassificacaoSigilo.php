<?php

namespace App\Enums;

enum ClassificacaoSigilo: string
{
    case Publico = 'publico';
    case Reservado = 'reservado';
    case Secreto = 'secreto';
    case Ultrassecreto = 'ultrassecreto';

    public function label(): string
    {
        return match ($this) {
            self::Publico => 'Público',
            self::Reservado => 'Reservado',
            self::Secreto => 'Secreto',
            self::Ultrassecreto => 'Ultrassecreto',
        };
    }

    /**
     * Prazo máximo de sigilo em anos (LAI art. 24).
     * Público não possui prazo (retorna 0).
     */
    public function prazoAnos(): int
    {
        return match ($this) {
            self::Publico => 0,
            self::Reservado => 5,
            self::Secreto => 15,
            self::Ultrassecreto => 25,
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Publico => 'solar:lock-unlocked-bold',
            self::Reservado => 'solar:lock-bold',
            self::Secreto => 'solar:shield-warning-bold',
            self::Ultrassecreto => 'solar:danger-triangle-bold',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Publico => 'success',
            self::Reservado => 'warning',
            self::Secreto => 'danger',
            self::Ultrassecreto => 'dark',
        };
    }

    /**
     * Verifica se a classificação requer justificativa (todas exceto público).
     */
    public function requerJustificativa(): bool
    {
        return $this !== self::Publico;
    }
}
