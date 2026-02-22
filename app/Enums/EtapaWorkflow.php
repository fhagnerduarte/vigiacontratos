<?php

namespace App\Enums;

enum EtapaWorkflow: string
{
    case Solicitacao = 'solicitacao';
    case AprovacaoSecretario = 'aprovacao_secretario';
    case ParecerJuridico = 'parecer_juridico';
    case ValidacaoControladoria = 'validacao_controladoria';
    case Homologacao = 'homologacao';

    public function label(): string
    {
        return match ($this) {
            self::Solicitacao => 'Solicitação',
            self::AprovacaoSecretario => 'Aprovação do Secretário',
            self::ParecerJuridico => 'Parecer Jurídico',
            self::ValidacaoControladoria => 'Validação da Controladoria',
            self::Homologacao => 'Homologação',
        };
    }

    public function ordem(): int
    {
        return match ($this) {
            self::Solicitacao => 1,
            self::AprovacaoSecretario => 2,
            self::ParecerJuridico => 3,
            self::ValidacaoControladoria => 4,
            self::Homologacao => 5,
        };
    }

    /**
     * Retorna o nome do perfil (role) responsavel por esta etapa.
     */
    public function roleResponsavel(): string
    {
        return match ($this) {
            self::Solicitacao => 'gestor_contrato',
            self::AprovacaoSecretario => 'secretario',
            self::ParecerJuridico => 'procuradoria',
            self::ValidacaoControladoria => 'controladoria',
            self::Homologacao => 'administrador_geral',
        };
    }
}
