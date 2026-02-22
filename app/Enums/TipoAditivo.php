<?php

namespace App\Enums;

enum TipoAditivo: string
{
    case Prazo = 'prazo';
    case Valor = 'valor';
    case PrazoEValor = 'prazo_e_valor';
    case Supressao = 'supressao';
    case Reequilibrio = 'reequilibrio';
    case AlteracaoClausula = 'alteracao_clausula';
    case Misto = 'misto';

    public function label(): string
    {
        return match ($this) {
            self::Prazo => 'Prazo',
            self::Valor => 'Valor',
            self::PrazoEValor => 'Prazo e Valor',
            self::Supressao => 'Supressão',
            self::Reequilibrio => 'Reequilíbrio Econômico-Financeiro',
            self::AlteracaoClausula => 'Alteração de Cláusula',
            self::Misto => 'Misto',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Prazo => 'info',
            self::Valor => 'success',
            self::PrazoEValor => 'primary',
            self::Supressao => 'danger',
            self::Reequilibrio => 'warning',
            self::AlteracaoClausula => 'secondary',
            self::Misto => 'info',
        };
    }

    public function alteraPrazo(): bool
    {
        return in_array($this, [self::Prazo, self::PrazoEValor, self::Misto]);
    }

    public function alteraValor(): bool
    {
        return in_array($this, [self::Valor, self::PrazoEValor, self::Misto, self::Reequilibrio]);
    }

    public function exigeSupressao(): bool
    {
        return in_array($this, [self::Supressao, self::Misto]);
    }
}
