<?php

namespace App\Enums;

enum AcaoLogDocumento: string
{
    case Upload = 'upload';
    case Download = 'download';
    case Substituicao = 'substituicao';
    case Exclusao = 'exclusao';
    case Visualizacao = 'visualizacao';

    public function label(): string
    {
        return match ($this) {
            self::Upload => 'Upload',
            self::Download => 'Download',
            self::Substituicao => 'Substituição',
            self::Exclusao => 'Exclusão',
            self::Visualizacao => 'Visualização',
        };
    }
}
