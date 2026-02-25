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

    // IMP-053: Execucao Financeira Avancada
    case EmpenhoInsuficiente = 'empenho_insuficiente';

    // IMP-059: Alertas LAI (Lei 12.527/2011)
    case ContratoNaoPublicadoPortal = 'contrato_nao_publicado';
    case SolicitacaoLaiVencendo = 'solicitacao_lai_vencendo';
    case SolicitacaoLaiVencida = 'solicitacao_lai_vencida';
    case SigiloSemJustificativa = 'sigilo_sem_justificativa';

    public function label(): string
    {
        return match ($this) {
            self::VencimentoVigencia => 'Vencimento da Vigencia',
            self::TerminoAditivo => 'Termino de Aditivo',
            self::PrazoGarantia => 'Prazo de Garantia',
            self::PrazoExecucaoFisica => 'Prazo de Execucao Fisica',
            self::AditivoSemDocumento => 'Aditivo sem Documento',
            self::ProrrogacaoSemParecer => 'Prorrogacao sem Parecer Juridico',
            self::ContratoSemPublicacao => 'Contrato sem Publicacao Oficial',
            self::ExecucaoAposVencimento => 'Execucao Financeira Apos Vencimento',
            self::AditivoAcimaLimite => 'Aditivo Acima do Limite Legal',
            self::ContratoSemFiscal => 'Contrato sem Fiscal Designado',
            self::FiscalSemRelatorio => 'Fiscal sem Relatorio Recente',
            self::ProrrogacaoForaDoPrazo => 'Prorrogacao Fora do Prazo',
            self::ContratoParado => 'Contrato sem Movimentacao',
            self::EmpenhoInsuficiente => 'Empenho Insuficiente',
            self::ContratoNaoPublicadoPortal => 'Contrato Nao Publicado no Portal',
            self::SolicitacaoLaiVencendo => 'Solicitacao LAI Vencendo',
            self::SolicitacaoLaiVencida => 'Solicitacao LAI Vencida',
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
