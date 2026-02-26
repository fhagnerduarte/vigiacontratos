<?php

namespace Database\Seeders;

use App\Enums\FaseContratual;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Services\ChecklistService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracaoChecklistDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $conn = DB::connection('tenant');

        // Mapeamento completo com descrições por fase
        $checklistPorFase = [
            FaseContratual::Planejamento->value => [
                ['tipo' => TipoDocumentoContratual::TermoReferencia, 'descricao' => 'Estudo técnico preliminar e especificações', 'ordem' => 1],
                ['tipo' => TipoDocumentoContratual::Justificativa, 'descricao' => 'Justificativa da necessidade de contratação', 'ordem' => 2],
            ],
            FaseContratual::Formalizacao->value => [
                ['tipo' => TipoDocumentoContratual::ContratoOriginal, 'descricao' => 'Instrumento contratual assinado', 'ordem' => 1],
                ['tipo' => TipoDocumentoContratual::ParecerJuridico, 'descricao' => 'Parecer jurídico sobre legalidade', 'ordem' => 2],
                ['tipo' => TipoDocumentoContratual::NotaEmpenho, 'descricao' => 'Nota de empenho da despesa', 'ordem' => 3],
            ],
            FaseContratual::Publicacao->value => [
                ['tipo' => TipoDocumentoContratual::PublicacaoOficial, 'descricao' => 'Extrato publicado no Diário Oficial', 'ordem' => 1],
            ],
            FaseContratual::Fiscalizacao->value => [
                ['tipo' => TipoDocumentoContratual::PortariaDesignacaoFiscal, 'descricao' => 'Portaria de designação do fiscal (Lei 14.133 art. 117)', 'ordem' => 1],
                ['tipo' => TipoDocumentoContratual::RelatorioFiscalizacao, 'descricao' => 'Relatório de acompanhamento do fiscal', 'ordem' => 2],
            ],
            FaseContratual::ExecucaoFinanceira->value => [
                ['tipo' => TipoDocumentoContratual::NotaFiscal, 'descricao' => 'Nota fiscal dos serviços/produtos', 'ordem' => 1],
                ['tipo' => TipoDocumentoContratual::OrdemServico, 'descricao' => 'Ordem de serviço ou fornecimento', 'ordem' => 2],
                ['tipo' => TipoDocumentoContratual::RelatorioMedicao, 'descricao' => 'Relatório de medição de serviços', 'ordem' => 3],
            ],
            FaseContratual::GestaoAditivos->value => [
                ['tipo' => TipoDocumentoContratual::AditivoDoc, 'descricao' => 'Termo aditivo ao contrato', 'ordem' => 1],
            ],
            FaseContratual::Encerramento->value => [
                ['tipo' => TipoDocumentoContratual::TermoRecebimentoProvisorio, 'descricao' => 'Termo de recebimento provisório (Lei 14.133 art. 140)', 'ordem' => 1],
                ['tipo' => TipoDocumentoContratual::TermoRecebimentoDefinitivo, 'descricao' => 'Termo de recebimento definitivo (Lei 14.133 art. 140)', 'ordem' => 2],
            ],
        ];

        foreach (TipoContrato::cases() as $tipoContrato) {
            foreach ($checklistPorFase as $fase => $documentos) {
                foreach ($documentos as $doc) {
                    $conn->table('configuracoes_checklist_documento')->updateOrInsert(
                        [
                            'fase' => $fase,
                            'tipo_contrato' => $tipoContrato->value,
                            'tipo_documento' => $doc['tipo']->value,
                        ],
                        [
                            'descricao' => $doc['descricao'],
                            'ordem' => $doc['ordem'],
                            'is_ativo' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
