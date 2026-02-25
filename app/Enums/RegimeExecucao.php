<?php

namespace App\Enums;

enum RegimeExecucao: string
{
    case EmpreitadaIntegral = 'empreitada_integral';
    case PrecoUnitario = 'preco_unitario';
    case PrecoGlobal = 'preco_global';
    case Tarefa = 'tarefa';
    case ContratacaoIntegrada = 'contratacao_integrada';

    public function label(): string
    {
        return match ($this) {
            self::EmpreitadaIntegral => 'Empreitada Integral',
            self::PrecoUnitario => 'Preco Unitario',
            self::PrecoGlobal => 'Preco Global',
            self::Tarefa => 'Tarefa',
            self::ContratacaoIntegrada => 'Contratacao Integrada',
        };
    }
}
