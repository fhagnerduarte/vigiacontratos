<?php

namespace App\Enums;

enum TipoExecucaoFinanceira: string
{
    case Pagamento = 'pagamento';
    case Liquidacao = 'liquidacao';
    case EmpenhoAdicional = 'empenho_adicional';

    public function label(): string
    {
        return match ($this) {
            self::Pagamento => 'Pagamento',
            self::Liquidacao => 'Liquidacao',
            self::EmpenhoAdicional => 'Empenho Adicional',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Pagamento => 'lucide:banknote',
            self::Liquidacao => 'lucide:check-circle',
            self::EmpenhoAdicional => 'lucide:plus-circle',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Pagamento => 'primary',
            self::Liquidacao => 'success',
            self::EmpenhoAdicional => 'info',
        };
    }
}
