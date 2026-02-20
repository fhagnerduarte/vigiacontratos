<?php

namespace App\Enums;

enum CategoriaServico: string
{
    case Transporte = 'transporte';
    case Alimentacao = 'alimentacao';
    case Tecnologia = 'tecnologia';
    case Obras = 'obras';
    case Limpeza = 'limpeza';
    case Seguranca = 'seguranca';
    case Manutencao = 'manutencao';
    case Saude = 'saude';
    case Educacao = 'educacao';
    case Outros = 'outros';

    public function label(): string
    {
        return match ($this) {
            self::Transporte => 'Transporte',
            self::Alimentacao => 'Alimentacao',
            self::Tecnologia => 'Tecnologia',
            self::Obras => 'Obras',
            self::Limpeza => 'Limpeza',
            self::Seguranca => 'Seguranca',
            self::Manutencao => 'Manutencao',
            self::Saude => 'Saude',
            self::Educacao => 'Educacao',
            self::Outros => 'Outros',
        };
    }
}
