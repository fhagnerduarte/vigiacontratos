<?php

namespace App\Enums;

enum TipoEventoAlerta: string
{
    case VencimentoVigencia = 'vencimento_vigencia';
    case TerminoAditivo = 'termino_aditivo';
    case PrazoGarantia = 'prazo_garantia';
    case PrazoExecucaoFisica = 'prazo_execucao_fisica';
    case AditivoSemDocumento = 'aditivo_sem_documento';
    case ProrrogacaoSemParecer = 'prorrogacao_sem_parecer';
    case ContratoSemPublicacao = 'contrato_sem_publicacao';

    // IMP-051: Motor completo (Regras 2-10)
    case ExecucaoAposVencimento = 'execucao_apos_vencimento';
    case AditivoAcimaLimite = 'aditivo_acima_limite';
    case ContratoSemFiscal = 'contrato_sem_fiscal';
    case FiscalSemRelatorio = 'fiscal_sem_relatorio';
    case ProrrogacaoForaDoPrazo = 'prorrogacao_fora_do_prazo';
    case ContratoParado = 'contrato_parado';

    // IMP-053: Execução Financeira Avançada
    case EmpenhoInsuficiente = 'empenho_insuficiente';

    // IMP-059: Alertas LAI (Lei 12.527/2011)
    case ContratoNaoPublicadoPortal = 'contrato_nao_publicado';
    case SolicitacaoLaiVencendo = 'solicitacao_lai_vencendo';
    case SolicitacaoLaiVencida = 'solicitacao_lai_vencida';
    case SigiloSemJustificativa = 'sigilo_sem_justificativa';

    public function label(): string
    {
        return match ($this) {
            self::VencimentoVigencia => 'Vencimento da Vigência',
            self::TerminoAditivo => 'Término de Aditivo',
            self::PrazoGarantia => 'Prazo de Garantia',
            self::PrazoExecucaoFisica => 'Prazo de Execução Física',
            self::AditivoSemDocumento => 'Aditivo sem Documento',
            self::ProrrogacaoSemParecer => 'Prorrogação sem Parecer Jurídico',
            self::ContratoSemPublicacao => 'Contrato sem Publicação Oficial',
            self::ExecucaoAposVencimento => 'Execução Financeira Após Vencimento',
            self::AditivoAcimaLimite => 'Aditivo Acima do Limite Legal',
            self::ContratoSemFiscal => 'Contrato sem Fiscal Designado',
            self::FiscalSemRelatorio => 'Fiscal sem Relatório Recente',
            self::ProrrogacaoForaDoPrazo => 'Prorrogação Fora do Prazo',
            self::ContratoParado => 'Contrato sem Movimentação',
            self::EmpenhoInsuficiente => 'Empenho Insuficiente',
            self::ContratoNaoPublicadoPortal => 'Contrato Não Publicado no Portal',
            self::SolicitacaoLaiVencendo => 'Solicitação LAI Vencendo',
            self::SolicitacaoLaiVencida => 'Solicitação LAI Vencida',
            self::SigiloSemJustificativa => 'Sigilo sem Justificativa',
        };
    }

    public function severidade(): string
    {
        return match ($this) {
            self::ExecucaoAposVencimento, self::AditivoAcimaLimite, self::EmpenhoInsuficiente => 'critica',
            self::ContratoSemFiscal, self::ProrrogacaoForaDoPrazo, self::SolicitacaoLaiVencida => 'alta',
            self::FiscalSemRelatorio, self::ContratoParado, self::SolicitacaoLaiVencendo, self::SigiloSemJustificativa => 'media',
            default => 'padrao',
        };
    }
}
