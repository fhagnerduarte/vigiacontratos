<?php

namespace Database\Seeders;

use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Models\Contrato;
use App\Models\Documento;
use App\Services\DocumentoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $contratos = Contrato::withoutGlobalScopes()->get();

        if ($contratos->isEmpty()) {
            return;
        }

        $userId = DB::connection('tenant')->table('users')->value('id');
        if (! $userId) {
            return;
        }

        // Limpa cache para garantir leitura fresca do checklist configurado
        DocumentoService::limparCacheChecklist();

        foreach ($contratos as $contrato) {
            $tipos = $this->getTiposParaContrato($contrato);

            foreach ($tipos as $tipo) {
                $numero = str_replace('/', '-', $contrato->numero);
                $nomeArquivo = "contrato_{$numero}_{$tipo->value}_v1.pdf";
                $caminho = "documentos/contratos/{$contrato->id}/{$tipo->value}/{$nomeArquivo}";

                $existing = Documento::where('documentable_type', Contrato::class)
                    ->where('documentable_id', $contrato->id)
                    ->where('tipo_documento', $tipo->value)
                    ->exists();

                if (! $existing) {
                    Documento::create([
                        'documentable_type' => Contrato::class,
                        'documentable_id'   => $contrato->id,
                        'tipo_documento'    => $tipo->value,
                        'nome_original'     => $tipo->label() . ' - ' . $contrato->numero . '.pdf',
                        'nome_arquivo'      => $nomeArquivo,
                        'descricao'         => $this->getDescricao($tipo, $contrato),
                        'caminho'           => $caminho,
                        'tamanho'           => rand(102400, 5242880),
                        'mime_type'         => 'application/pdf',
                        'hash_integridade'  => hash('sha256', $caminho . $contrato->id),
                        'versao'            => 1,
                        'is_versao_atual'   => true,
                        'uploaded_by'       => $userId,
                    ]);
                }
            }
        }
    }

    /**
     * Retorna os tipos de documento a gerar para cada contrato.
     * Usa o checklist configurado no banco para garantir completude.
     *
     * @return TipoDocumentoContratual[]
     */
    private function getTiposParaContrato(Contrato $contrato): array
    {
        // Contratos intencionalmente incompletos (para testar semáforo vermelho/amarelo)
        if (in_array($contrato->numero, ['008/2026', '013/2026'])) {
            return [
                TipoDocumentoContratual::ContratoOriginal,
                TipoDocumentoContratual::TermoReferencia,
            ];
        }

        // Obter checklist obrigatório configurado para o tipo do contrato
        $tipoContrato = $contrato->tipo instanceof TipoContrato
            ? $contrato->tipo
            : TipoContrato::tryFrom($contrato->tipo);

        $obrigatorios = $tipoContrato
            ? DocumentoService::obterChecklistPorTipo($tipoContrato)
            : DocumentoService::CHECKLIST_OBRIGATORIO;

        // Converter strings para enums se necessário
        $tipos = [];
        foreach ($obrigatorios as $item) {
            if ($item instanceof TipoDocumentoContratual) {
                $tipos[] = $item;
            } else {
                $enum = TipoDocumentoContratual::tryFrom($item);
                if ($enum) {
                    $tipos[] = $enum;
                }
            }
        }

        // Contratos cancelados/suspensos — sem documentos de encerramento
        if (in_array($contrato->status, ['cancelado', 'suspenso'])) {
            $tipos = array_filter($tipos, fn ($t) => ! in_array($t, [
                TipoDocumentoContratual::TermoRecebimentoProvisorio,
                TipoDocumentoContratual::TermoRecebimentoDefinitivo,
            ]));
        }

        // Contratos vigentes em execução — sem termos de recebimento ainda
        if ($contrato->status === 'vigente' && $contrato->percentual_executado < 100) {
            $tipos = array_filter($tipos, fn ($t) => ! in_array($t, [
                TipoDocumentoContratual::TermoRecebimentoProvisorio,
                TipoDocumentoContratual::TermoRecebimentoDefinitivo,
            ]));
        }

        return array_values($tipos);
    }

    private function getDescricao(TipoDocumentoContratual $tipo, Contrato $contrato): string
    {
        return match ($tipo) {
            TipoDocumentoContratual::ContratoOriginal => "Contrato original assinado — {$contrato->numero}",
            TipoDocumentoContratual::TermoReferencia => "Termo de referência com especificações técnicas",
            TipoDocumentoContratual::PublicacaoOficial => "Publicação no Diário Oficial do Município",
            TipoDocumentoContratual::ParecerJuridico => "Parecer jurídico favorável à contratação",
            TipoDocumentoContratual::NotaEmpenho => "Nota de empenho — processo {$contrato->numero_processo}",
            TipoDocumentoContratual::OrdemServico => "Ordem de serviço para início da execução",
            TipoDocumentoContratual::NotaFiscal => "Nota fiscal referente ao último período",
            TipoDocumentoContratual::RelatorioMedicao => "Relatório de medição da última etapa executada",
            TipoDocumentoContratual::RelatorioFiscalizacao => "Relatório de fiscalização periódica",
            TipoDocumentoContratual::PortariaDesignacaoFiscal => "Portaria de designação do fiscal do contrato",
            TipoDocumentoContratual::TermoRecebimentoProvisorio => "Termo de recebimento provisório do objeto",
            TipoDocumentoContratual::TermoRecebimentoDefinitivo => "Termo de recebimento definitivo do objeto",
            TipoDocumentoContratual::Justificativa => "Justificativa da necessidade de contratação",
            TipoDocumentoContratual::AditivoDoc => "Termo aditivo ao contrato",
            default => "Documento complementar do contrato {$contrato->numero}",
        };
    }
}
