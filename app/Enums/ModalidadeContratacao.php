<?php

namespace App\Enums;

enum ModalidadeContratacao: string
{
    case PregaoEletronico = 'pregao_eletronico';
    case PregaoPresencial = 'pregao_presencial';
    case Concorrencia = 'concorrencia';
    case TomadaPreco = 'tomada_preco';
    case Convite = 'convite';
    case Leilao = 'leilao';
    case Dispensa = 'dispensa';
    case Inexigibilidade = 'inexigibilidade';
    case AdesaoAta = 'adesao_ata';

    public function label(): string
    {
        return match ($this) {
            self::PregaoEletronico => 'Pregao Eletronico',
            self::PregaoPresencial => 'Pregao Presencial',
            self::Concorrencia => 'Concorrencia',
            self::TomadaPreco => 'Tomada de Preco',
            self::Convite => 'Convite',
            self::Leilao => 'Leilao',
            self::Dispensa => 'Dispensa',
            self::Inexigibilidade => 'Inexigibilidade',
            self::AdesaoAta => 'Adesao a Ata',
        };
    }

    public function isSensivel(): bool
    {
        return in_array($this, [self::Dispensa, self::Inexigibilidade]);
    }
}
