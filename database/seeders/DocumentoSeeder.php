<?php

namespace Database\Seeders;

use App\Enums\TipoDocumentoContratual;
use App\Models\Contrato;
use App\Models\Documento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $conn = DB::connection('tenant');

        $contratos = Contrato::all();

        if ($contratos->isEmpty()) {
            return;
        }

        // Pegar primeiro user para uploaded_by
        $userId = $conn->table('users')->value('id');
        if (! $userId) {
            return;
        }

        // Tipos de documento para gerar exemplos
        $tiposExemplo = [
            TipoDocumentoContratual::ContratoOriginal,
            TipoDocumentoContratual::PublicacaoOficial,
            TipoDocumentoContratual::ParecerJuridico,
            TipoDocumentoContratual::NotaEmpenho,
            TipoDocumentoContratual::TermoReferencia,
        ];

        foreach ($contratos as $index => $contrato) {
            // Cada contrato recebe de 1 a 5 tipos de documento (variando completude)
            $qtdTipos = min($index + 1, count($tiposExemplo));
            $tiposParaEsteContrato = array_slice($tiposExemplo, 0, $qtdTipos);

            foreach ($tiposParaEsteContrato as $tipo) {
                $numero = str_replace('/', '-', $contrato->numero);
                $nomeArquivo = "contrato_{$numero}_{$tipo->value}_v1.pdf";
                $caminho = "documentos/contratos/{$contrato->id}/{$tipo->value}/{$nomeArquivo}";

                Documento::create([
                    'documentable_type' => Contrato::class,
                    'documentable_id' => $contrato->id,
                    'tipo_documento' => $tipo->value,
                    'nome_original' => $tipo->label() . ' - ' . $contrato->numero . '.pdf',
                    'nome_arquivo' => $nomeArquivo,
                    'descricao' => 'Documento de exemplo gerado pelo seeder',
                    'caminho' => $caminho,
                    'tamanho' => rand(102400, 5242880), // 100KB a 5MB
                    'mime_type' => 'application/pdf',
                    'hash_integridade' => hash('sha256', $caminho . now()->timestamp),
                    'versao' => 1,
                    'is_versao_atual' => true,
                    'uploaded_by' => $userId,
                ]);
            }
        }
    }
}
