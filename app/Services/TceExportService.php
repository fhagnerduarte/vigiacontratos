<?php

namespace App\Services;

use App\Enums\FormatoExportacaoTce;
use App\Exports\RelatorioTceExport;
use App\Models\ExportacaoTce;
use Maatwebsite\Excel\Facades\Excel;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TceExportService
{
    private const CAMPOS_OBRIGATORIOS_TCE = [
        'numero' => 'Número do contrato',
        'objeto' => 'Objeto do contrato',
        'cnpj_fornecedor' => 'CNPJ do fornecedor',
        'valor_global' => 'Valor global',
        'data_inicio' => 'Data de início',
        'data_fim' => 'Data de fim',
        'modalidade' => 'Modalidade de contratação',
        'numero_processo' => 'Número do processo',
        'data_publicacao' => 'Data de publicação',
        'fiscal_titular' => 'Fiscal titular designado',
    ];

    public static function coletarDados(array $filtros = []): array
    {
        $dados = PainelRiscoService::dadosRelatorioTCE($filtros);
        $pendencias = self::validarCompletude($dados['contratos']);

        $dados['total_pendencias'] = collect($pendencias)->filter(fn ($p) => count($p['campos_faltantes']) > 0)->count();
        $dados['pendencias'] = $pendencias;

        return $dados;
    }

    public static function validarCompletude(array $contratos): array
    {
        $pendencias = [];

        foreach ($contratos as $contrato) {
            $camposFaltantes = [];

            foreach (self::CAMPOS_OBRIGATORIOS_TCE as $campo => $descricao) {
                $valor = $contrato[$campo] ?? null;

                if ($valor === null || $valor === '' || $valor === '-') {
                    $camposFaltantes[] = $descricao;
                }
            }

            $pendencias[] = [
                'numero' => $contrato['numero'],
                'objeto' => $contrato['objeto'],
                'campos_faltantes' => $camposFaltantes,
                'total_faltantes' => count($camposFaltantes),
                'completo' => count($camposFaltantes) === 0,
            ];
        }

        return $pendencias;
    }

    public static function gerarXml(array $dados): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><RelatorioTCE/>');
        $xml->addAttribute('versao', '1.0');

        // Cabeçalho
        $cabecalho = $xml->addChild('Cabecalho');
        $cabecalho->addChild('Municipio', self::xmlEscape($dados['municipio']));
        $cabecalho->addChild('DataGeracao', now()->format('Y-m-d\TH:i:s'));
        $cabecalho->addChild('TotalContratos', (string) $dados['resumo']['total_monitorados']);
        $cabecalho->addChild('TotalPendencias', (string) ($dados['total_pendencias'] ?? 0));

        // Resumo
        $resumo = $xml->addChild('Resumo');
        $resumo->addChild('TotalMonitorados', (string) $dados['resumo']['total_monitorados']);
        $resumo->addChild('AltoRisco', (string) $dados['resumo']['alto_risco']);
        $resumo->addChild('MedioRisco', (string) $dados['resumo']['medio_risco']);
        $resumo->addChild('BaixoRisco', (string) $dados['resumo']['baixo_risco']);

        // Contratos
        $contratosNode = $xml->addChild('Contratos');
        foreach ($dados['contratos'] as $contrato) {
            $node = $contratosNode->addChild('Contrato');
            $node->addChild('Numero', self::xmlEscape($contrato['numero']));
            $node->addChild('Objeto', self::xmlEscape($contrato['objeto']));
            $node->addChild('CNPJFornecedor', self::xmlEscape($contrato['cnpj_fornecedor'] ?? ''));
            $node->addChild('RazaoSocial', self::xmlEscape($contrato['fornecedor'] ?? ''));
            $node->addChild('Secretaria', self::xmlEscape($contrato['secretaria'] ?? ''));
            $node->addChild('Modalidade', self::xmlEscape($contrato['modalidade'] ?? ''));
            $node->addChild('NumeroProcesso', self::xmlEscape($contrato['numero_processo'] ?? ''));
            $node->addChild('FundamentoLegal', self::xmlEscape($contrato['fundamento_legal'] ?? ''));
            $node->addChild('ValorGlobal', number_format((float) $contrato['valor_global'], 2, '.', ''));
            $node->addChild('ValorEmpenhado', number_format((float) ($contrato['valor_empenhado'] ?? 0), 2, '.', ''));
            $node->addChild('PercentualExecutado', number_format((float) ($contrato['percentual_executado'] ?? 0), 2, '.', ''));
            $node->addChild('DataInicio', $contrato['data_inicio'] ?? '');
            $node->addChild('DataFim', $contrato['data_fim'] ?? '');
            $node->addChild('DataAssinatura', $contrato['data_assinatura'] ?? '');
            $node->addChild('DataPublicacao', $contrato['data_publicacao'] ?? '');
            $node->addChild('Status', $contrato['status'] ?? '');
            $node->addChild('FiscalTitular', self::xmlEscape($contrato['fiscal_titular'] ?? ''));
            $node->addChild('QtdAditivos', (string) ($contrato['qtd_aditivos'] ?? 0));
            $node->addChild('ScoreRisco', (string) $contrato['score']);
            $node->addChild('NivelRisco', $contrato['nivel']);
            $node->addChild('CategoriasRisco', implode(', ', $contrato['categorias']));

            $pendenciaTexto = '';
            if (! empty($dados['pendencias'])) {
                foreach ($dados['pendencias'] as $p) {
                    if ($p['numero'] === $contrato['numero'] && ! empty($p['campos_faltantes'])) {
                        $pendenciaTexto = implode('; ', $p['campos_faltantes']);
                        break;
                    }
                }
            }
            $node->addChild('Pendencias', self::xmlEscape($pendenciaTexto));
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    public static function gerarCsv(array $dados): StreamedResponse
    {
        $nomeArquivo = 'relatorio-tce-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($dados) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // Header
            fputcsv($handle, [
                'Número',
                'Objeto',
                'CNPJ Fornecedor',
                'Razão Social',
                'Secretaria',
                'Modalidade',
                'N. Processo',
                'Fundamento Legal',
                'Valor Global (R$)',
                'Valor Empenhado (R$)',
                '% Executado',
                'Data Início',
                'Data Fim',
                'Data Assinatura',
                'Data Publicação',
                'Status',
                'Fiscal Titular',
                'Qtd Aditivos',
                'Score Risco',
                'Nível Risco',
                'Categorias Risco',
                'Pendências',
            ], ';');

            // Dados
            foreach ($dados['contratos'] as $contrato) {
                $pendenciaTexto = '';
                if (! empty($dados['pendencias'])) {
                    foreach ($dados['pendencias'] as $p) {
                        if ($p['numero'] === $contrato['numero'] && ! empty($p['campos_faltantes'])) {
                            $pendenciaTexto = implode('; ', $p['campos_faltantes']);
                            break;
                        }
                    }
                }

                fputcsv($handle, [
                    $contrato['numero'],
                    $contrato['objeto'],
                    $contrato['cnpj_fornecedor'] ?? '',
                    $contrato['fornecedor'] ?? '',
                    $contrato['secretaria'] ?? '',
                    $contrato['modalidade'] ?? '',
                    $contrato['numero_processo'] ?? '',
                    $contrato['fundamento_legal'] ?? '',
                    number_format((float) $contrato['valor_global'], 2, ',', '.'),
                    number_format((float) ($contrato['valor_empenhado'] ?? 0), 2, ',', '.'),
                    number_format((float) ($contrato['percentual_executado'] ?? 0), 2, ',', '.') . '%',
                    $contrato['data_inicio'] ?? '',
                    $contrato['data_fim'] ?? '',
                    $contrato['data_assinatura'] ?? '',
                    $contrato['data_publicacao'] ?? '',
                    $contrato['status'] ?? '',
                    $contrato['fiscal_titular'] ?? '',
                    $contrato['qtd_aditivos'] ?? 0,
                    $contrato['score'],
                    $contrato['nivel'],
                    implode(', ', $contrato['categorias']),
                    $pendenciaTexto,
                ], ';');
            }

            fclose($handle);
        }, $nomeArquivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public static function gerarExcel(array $dados): BinaryFileResponse
    {
        $nomeArquivo = 'relatorio-tce-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new RelatorioTceExport($dados), $nomeArquivo);
    }

    public static function registrarExportacao(
        FormatoExportacaoTce $formato,
        ?array $filtros,
        int $totalContratos,
        int $totalPendencias,
    ): ExportacaoTce {
        $nomeArquivo = 'relatorio-tce-' . now()->format('Y-m-d-His') . $formato->extensao();

        return ExportacaoTce::create([
            'formato' => $formato->value,
            'filtros' => $filtros,
            'total_contratos' => $totalContratos,
            'total_pendencias' => $totalPendencias,
            'arquivo_nome' => $nomeArquivo,
            'gerado_por' => auth()->id(),
        ]);
    }

    private static function xmlEscape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
