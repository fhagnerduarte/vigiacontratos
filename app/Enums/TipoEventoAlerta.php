<?php

namespace App\Enums;

enum TipoEventoAlerta: string
{
    case VencimentoVigencia = 'vencimento_vigencia';
    case TerminoAditivo = 'termino_aditivo';
    case PrazoGarantia = 'prazo_garantia';
    case PrazoExecucaoFisica = 'prazo_execucao_fisica';

    public function label(): string
    {
        return match ($this) {
            self::VencimentoVigencia => 'Vencimento da Vigencia',
            self::TerminoAditivo => 'Termino de Aditivo',
            self::PrazoGarantia => 'Prazo de Garantia',
            self::PrazoExecucaoFisica => 'Prazo de Execucao Fisica',
        };
    }
}
