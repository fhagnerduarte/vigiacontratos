<?php

namespace Database\Seeders;

use App\Enums\TipoEventoAlerta;
use App\Models\ConfiguracaoAlertaAvancado;
use Illuminate\Database\Seeder;

class ConfiguracaoAlertaAvancadoSeeder extends Seeder
{
    public function run(): void
    {
        $configuracoes = [
            [
                'tipo_evento' => TipoEventoAlerta::ExecucaoAposVencimento->value,
                'dias_inatividade' => null,
                'dias_sem_relatorio' => null,
                'percentual_limite_valor' => null,
                'is_ativo' => true,
            ],
            [
                'tipo_evento' => TipoEventoAlerta::AditivoAcimaLimite->value,
                'dias_inatividade' => null,
                'dias_sem_relatorio' => null,
                'percentual_limite_valor' => 25.00,
                'is_ativo' => true,
            ],
            [
                'tipo_evento' => TipoEventoAlerta::ContratoSemFiscal->value,
                'dias_inatividade' => null,
                'dias_sem_relatorio' => null,
                'percentual_limite_valor' => null,
                'is_ativo' => true,
            ],
            [
                'tipo_evento' => TipoEventoAlerta::FiscalSemRelatorio->value,
                'dias_inatividade' => null,
                'dias_sem_relatorio' => 60,
                'percentual_limite_valor' => null,
                'is_ativo' => true,
            ],
            [
                'tipo_evento' => TipoEventoAlerta::ProrrogacaoForaDoPrazo->value,
                'dias_inatividade' => null,
                'dias_sem_relatorio' => null,
                'percentual_limite_valor' => null,
                'is_ativo' => true,
            ],
            [
                'tipo_evento' => TipoEventoAlerta::ContratoParado->value,
                'dias_inatividade' => 90,
                'dias_sem_relatorio' => null,
                'percentual_limite_valor' => null,
                'is_ativo' => true,
            ],
            [
                'tipo_evento' => TipoEventoAlerta::EmpenhoInsuficiente->value,
                'dias_inatividade' => null,
                'dias_sem_relatorio' => null,
                'percentual_limite_valor' => null,
                'is_ativo' => true,
            ],
        ];

        foreach ($configuracoes as $config) {
            ConfiguracaoAlertaAvancado::firstOrCreate(
                ['tipo_evento' => $config['tipo_evento']],
                $config
            );
        }
    }
}
