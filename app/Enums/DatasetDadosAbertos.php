<?php

namespace App\Enums;

enum DatasetDadosAbertos: string
{
    case Contratos = 'contratos';
    case Fornecedores = 'fornecedores';
    case Licitacoes = 'licitacoes';

    public function label(): string
    {
        return match ($this) {
            self::Contratos => 'Contratos Publicos',
            self::Fornecedores => 'Fornecedores',
            self::Licitacoes => 'Licitacoes e Processos',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::Contratos => 'Dados de contratos publicos com valores, vigencia, fornecedores e execucao financeira.',
            self::Fornecedores => 'Cadastro de fornecedores com totais de contratos e valores contratados.',
            self::Licitacoes => 'Processos licitatorios com modalidade, objeto, valores e resultados.',
        };
    }
}
