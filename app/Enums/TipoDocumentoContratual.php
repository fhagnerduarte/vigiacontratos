<?php

namespace App\Enums;

enum TipoDocumentoContratual: string
{
    case ContratoOriginal = 'contrato_original';
    case TermoReferencia = 'termo_referencia';
    case PublicacaoOficial = 'publicacao_oficial';
    case ParecerJuridico = 'parecer_juridico';
    case AditivoDoc = 'aditivo_doc';
    case NotaEmpenho = 'nota_empenho';
    case NotaFiscal = 'nota_fiscal';
    case OrdemServico = 'ordem_servico';
    case RelatorioMedicao = 'relatorio_medicao';
    case RelatorioFiscalizacao = 'relatorio_fiscalizacao';
    case Justificativa = 'justificativa';
    case DocumentoComplementar = 'documento_complementar';
    case TermoRecebimentoProvisorio = 'termo_recebimento_provisorio';
    case TermoRecebimentoDefinitivo = 'termo_recebimento_definitivo';
    case PortariaDesignacaoFiscal = 'portaria_designacao_fiscal';

    public function label(): string
    {
        return match ($this) {
            self::ContratoOriginal => 'Contrato Original',
            self::TermoReferencia => 'Termo de Referência',
            self::PublicacaoOficial => 'Publicação Oficial',
            self::ParecerJuridico => 'Parecer Jurídico',
            self::AditivoDoc => 'Aditivo',
            self::NotaEmpenho => 'Nota de Empenho',
            self::NotaFiscal => 'Nota Fiscal',
            self::OrdemServico => 'Ordem de Serviço',
            self::RelatorioMedicao => 'Relatório de Medição',
            self::RelatorioFiscalizacao => 'Relatório de Fiscalização',
            self::Justificativa => 'Justificativa',
            self::DocumentoComplementar => 'Documento Complementar',
            self::TermoRecebimentoProvisorio => 'Termo de Recebimento Provisório',
            self::TermoRecebimentoDefinitivo => 'Termo de Recebimento Definitivo',
            self::PortariaDesignacaoFiscal => 'Portaria de Designação Fiscal',
        };
    }
}
