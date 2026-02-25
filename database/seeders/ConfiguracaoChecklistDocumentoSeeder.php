<?php

namespace Database\Seeders;

use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracaoChecklistDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $conn = DB::connection('tenant');

        $documentosPadrao = [
            TipoDocumentoContratual::ContratoOriginal,
            TipoDocumentoContratual::PublicacaoOficial,
            TipoDocumentoContratual::ParecerJuridico,
            TipoDocumentoContratual::NotaEmpenho,
        ];

        foreach (TipoContrato::cases() as $tipoContrato) {
            foreach ($documentosPadrao as $tipoDocumento) {
                $conn->table('configuracoes_checklist_documento')->updateOrInsert(
                    [
                        'tipo_contrato' => $tipoContrato->value,
                        'tipo_documento' => $tipoDocumento->value,
                    ],
                    [
                        'is_ativo' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
