<?php

namespace App\Services;

use App\Enums\FaseContratual;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Models\ConfiguracaoChecklistDocumento;
use App\Models\Contrato;
use App\Models\ContratoConformidadeFase;

class ChecklistService
{
    /**
     * Mapeamento padrao: quais tipos de documento pertencem a cada fase.
     * Usado como fallback quando a tabela nao tem fase configurada.
     */
    public const MAPEAMENTO_FASE_DOCUMENTO = [
        'planejamento' => [
            TipoDocumentoContratual::TermoReferencia,
            TipoDocumentoContratual::Justificativa,
        ],
        'formalizacao' => [
            TipoDocumentoContratual::ContratoOriginal,
            TipoDocumentoContratual::ParecerJuridico,
            TipoDocumentoContratual::NotaEmpenho,
        ],
        'publicacao' => [
            TipoDocumentoContratual::PublicacaoOficial,
        ],
        'fiscalizacao' => [
            TipoDocumentoContratual::PortariaDesignacaoFiscal,
            TipoDocumentoContratual::RelatorioFiscalizacao,
        ],
        'execucao_financeira' => [
            TipoDocumentoContratual::NotaFiscal,
            TipoDocumentoContratual::OrdemServico,
            TipoDocumentoContratual::RelatorioMedicao,
        ],
        'gestao_aditivos' => [
            TipoDocumentoContratual::AditivoDoc,
        ],
        'encerramento' => [
            TipoDocumentoContratual::TermoRecebimentoProvisorio,
            TipoDocumentoContratual::TermoRecebimentoDefinitivo,
        ],
    ];

    /**
     * Obtem o checklist de uma fase especifica para um contrato.
     *
     * @return array<array{tipo: TipoDocumentoContratual, label: string, presente: bool, versao: int|null, descricao: string|null}>
     */
    public static function obterChecklistPorFase(Contrato $contrato, FaseContratual $fase): array
    {
        $tipoContrato = $contrato->tipo instanceof TipoContrato ? $contrato->tipo : null;

        // Buscar configuracao do banco (por fase + tipo_contrato)
        $configurados = [];
        if ($tipoContrato) {
            try {
                $configurados = ConfiguracaoChecklistDocumento::where('fase', $fase->value)
                    ->where('tipo_contrato', $tipoContrato->value)
                    ->where('is_ativo', true)
                    ->orderBy('ordem')
                    ->get();
            } catch (\Throwable) {
                // Tabela pode nao existir ainda
            }
        }

        // Fallback para mapeamento padrao se nao ha configuracao no banco
        if (is_array($configurados) || ($configurados instanceof \Illuminate\Database\Eloquent\Collection && $configurados->isEmpty())) {
            $tiposPadrao = self::MAPEAMENTO_FASE_DOCUMENTO[$fase->value] ?? [];
            $documentosPresentes = self::obterDocumentosPresentes($contrato);

            return collect($tiposPadrao)->map(fn (TipoDocumentoContratual $tipo) => [
                'tipo' => $tipo,
                'label' => $tipo->label(),
                'presente' => $documentosPresentes->contains($tipo->value),
                'versao' => self::obterVersaoDocumento($contrato, $tipo),
                'descricao' => null,
            ])->values()->all();
        }

        $documentosPresentes = self::obterDocumentosPresentes($contrato);

        return $configurados->map(fn (ConfiguracaoChecklistDocumento $config) => [
            'tipo' => $config->tipo_documento,
            'label' => $config->tipo_documento->label(),
            'presente' => $documentosPresentes->contains($config->tipo_documento->value),
            'versao' => self::obterVersaoDocumento($contrato, $config->tipo_documento),
            'descricao' => $config->descricao,
        ])->values()->all();
    }

    /**
     * Calcula a conformidade de uma fase especifica.
     *
     * @return array{percentual: float, semaforo: string, total_obrigatorios: int, total_presentes: int}
     */
    public static function calcularConformidadeFase(Contrato $contrato, FaseContratual $fase): array
    {
        $checklist = self::obterChecklistPorFase($contrato, $fase);

        $totalObrigatorios = count($checklist);
        $totalPresentes = collect($checklist)->where('presente', true)->count();

        if ($totalObrigatorios === 0) {
            return [
                'percentual' => 100.0,
                'semaforo' => 'verde',
                'total_obrigatorios' => 0,
                'total_presentes' => 0,
            ];
        }

        $percentual = round(($totalPresentes / $totalObrigatorios) * 100, 2);

        $semaforo = match (true) {
            $percentual >= 100 => 'verde',
            $percentual >= 50 => 'amarelo',
            default => 'vermelho',
        };

        return [
            'percentual' => $percentual,
            'semaforo' => $semaforo,
            'total_obrigatorios' => $totalObrigatorios,
            'total_presentes' => $totalPresentes,
        ];
    }

    /**
     * Calcula a conformidade de todas as 7 fases de um contrato.
     *
     * @return array<string, array{fase: FaseContratual, label: string, icone: string, percentual: float, semaforo: string, total_obrigatorios: int, total_presentes: int}>
     */
    public static function calcularConformidadeGeral(Contrato $contrato): array
    {
        $resultado = [];

        foreach (FaseContratual::cases() as $fase) {
            $conformidade = self::calcularConformidadeFase($contrato, $fase);
            $resultado[$fase->value] = array_merge($conformidade, [
                'fase' => $fase,
                'label' => $fase->label(),
                'icone' => $fase->icone(),
            ]);
        }

        return $resultado;
    }

    /**
     * Calcula o percentual global de conformidade (media das 7 fases).
     */
    public static function calcularPercentualGlobal(Contrato $contrato): float
    {
        $conformidade = self::calcularConformidadeGeral($contrato);

        $fasesComItens = collect($conformidade)->filter(fn ($f) => $f['total_obrigatorios'] > 0);

        if ($fasesComItens->isEmpty()) {
            return 100.0;
        }

        return round($fasesComItens->avg('percentual'), 2);
    }

    /**
     * Atualiza o cache de conformidade por fase na tabela contrato_conformidade_fases.
     */
    public static function atualizarConformidadeCache(Contrato $contrato): void
    {
        $conformidade = self::calcularConformidadeGeral($contrato);

        foreach ($conformidade as $faseKey => $dados) {
            ContratoConformidadeFase::updateOrCreate(
                [
                    'contrato_id' => $contrato->id,
                    'fase' => $faseKey,
                ],
                [
                    'percentual_conformidade' => $dados['percentual'],
                    'total_obrigatorios' => $dados['total_obrigatorios'],
                    'total_presentes' => $dados['total_presentes'],
                    'nivel_semaforo' => $dados['semaforo'],
                ]
            );
        }
    }

    /**
     * Obtem os tipos de documentos presentes no contrato (versao atual, nao deletados).
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private static function obterDocumentosPresentes(Contrato $contrato): \Illuminate\Support\Collection
    {
        return $contrato->documentos()
            ->where('is_versao_atual', true)
            ->whereNull('deleted_at')
            ->pluck('tipo_documento')
            ->map(fn ($t) => $t instanceof TipoDocumentoContratual ? $t->value : $t)
            ->unique();
    }

    /**
     * Obtem a versao mais recente de um tipo de documento no contrato.
     */
    private static function obterVersaoDocumento(Contrato $contrato, TipoDocumentoContratual $tipo): ?int
    {
        $doc = $contrato->documentos()
            ->where('tipo_documento', $tipo->value)
            ->where('is_versao_atual', true)
            ->whereNull('deleted_at')
            ->first();

        return $doc?->versao;
    }
}
