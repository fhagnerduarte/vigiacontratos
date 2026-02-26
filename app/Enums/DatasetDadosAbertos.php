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
            self::Contratos => 'Contratos Públicos',
            self::Fornecedores => 'Fornecedores',
            self::Licitacoes => 'Licitações e Processos',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::Contratos => 'Dados de contratos públicos com valores, vigência, fornecedores e execução financeira.',
            self::Fornecedores => 'Cadastro de fornecedores com totais de contratos e valores contratados.',
            self::Licitacoes => 'Processos licitatórios com modalidade, objeto, valores e resultados.',
        };
    }
}
