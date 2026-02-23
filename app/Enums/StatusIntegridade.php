<?php

namespace App\Enums;

enum StatusIntegridade: string
{
    case Ok = 'ok';
    case Divergente = 'divergente';
    case ArquivoAusente = 'arquivo_ausente';

    public function label(): string
    {
        return match ($this) {
            self::Ok => 'Íntegro',
            self::Divergente => 'Hash Divergente',
            self::ArquivoAusente => 'Arquivo Ausente',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Ok => 'success',
            self::Divergente => 'danger',
            self::ArquivoAusente => 'warning',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Ok => 'solar:shield-check-bold',
            self::Divergente => 'solar:danger-triangle-bold',
            self::ArquivoAusente => 'solar:file-corrupted-bold',
        };
    }
}
