<?php

namespace App\Enums;

enum StatusSolicitacaoLai: string
{
    case Recebida = 'recebida';
    case EmAnalise = 'em_analise';
    case Respondida = 'respondida';
    case Prorrogada = 'prorrogada';
    case Indeferida = 'indeferida';
    case Recurso = 'recurso';

    public function label(): string
    {
        return match ($this) {
            self::Recebida => 'Recebida',
            self::EmAnalise => 'Em Analise',
            self::Respondida => 'Respondida',
            self::Prorrogada => 'Prorrogada',
            self::Indeferida => 'Indeferida',
            self::Recurso => 'Em Recurso',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Recebida => 'solar:inbox-bold',
            self::EmAnalise => 'solar:magnifer-bold',
            self::Respondida => 'solar:check-circle-bold',
            self::Prorrogada => 'solar:clock-circle-bold',
            self::Indeferida => 'solar:close-circle-bold',
            self::Recurso => 'solar:rewind-back-bold',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Recebida => 'info',
            self::EmAnalise => 'warning',
            self::Respondida => 'success',
            self::Prorrogada => 'primary',
            self::Indeferida => 'danger',
            self::Recurso => 'secondary',
        };
    }

    public function isFinalizado(): bool
    {
        return in_array($this, [self::Respondida, self::Indeferida]);
    }

    public function permiteResposta(): bool
    {
        return in_array($this, [self::Recebida, self::EmAnalise, self::Prorrogada]);
    }

    public function permiteProrrogacao(): bool
    {
        return in_array($this, [self::Recebida, self::EmAnalise]);
    }
}
