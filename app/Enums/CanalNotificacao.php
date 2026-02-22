<?php

namespace App\Enums;

enum CanalNotificacao: string
{
    case Email = 'email';
    case Sistema = 'sistema';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'E-mail',
            self::Sistema => 'Sistema (Notificacao Interna)',
        };
    }
}
