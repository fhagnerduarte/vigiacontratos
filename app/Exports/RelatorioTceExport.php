<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RelatorioTceExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private array $dados)
    {
    }

    public function collection(): Collection
    {
        return collect($this->dados['contratos'])->map(function ($contrato) {
            $pendenciaTexto = '';
            if (! empty($this->dados['pendencias'])) {
                foreach ($this->dados['pendencias'] as $p) {
                    if ($p['numero'] === $contrato['numero'] && ! empty($p['campos_faltantes'])) {
                        $pendenciaTexto = implode('; ', $p['campos_faltantes']);
                        break;
                    }
                }
            }

            return [
                $contrato['numero'],
                $contrato['objeto'],
                $contrato['cnpj_fornecedor'] ?? '-',
                $contrato['fornecedor'] ?? '-',
                $contrato['secretaria'] ?? '-',
                $contrato['modalidade'] ?? '-',
                $contrato['numero_processo'] ?? '-',
                number_format((float) $contrato['valor_global'], 2, ',', '.'),
                number_format((float) ($contrato['valor_empenhado'] ?? 0), 2, ',', '.'),
                number_format((float) ($contrato['percentual_executado'] ?? 0), 2, ',', '.') . '%',
                $contrato['data_inicio'] ?? '-',
                $contrato['data_fim'] ?? '-',
                $contrato['data_assinatura'] ?? '-',
                $contrato['data_publicacao'] ?? '-',
                $contrato['status'] ?? '-',
                $contrato['fiscal_titular'] ?? '-',
                $contrato['qtd_aditivos'] ?? 0,
                $contrato['score'],
                $contrato['nivel'],
                implode(', ', $contrato['categorias']),
                $pendenciaTexto ?: 'Nenhuma',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Numero',
            'Objeto',
            'CNPJ Fornecedor',
            'Razao Social',
            'Secretaria',
            'Modalidade',
            'N. Processo',
            'Valor Global (R$)',
            'Valor Empenhado (R$)',
            '% Executado',
            'Data Inicio',
            'Data Fim',
            'Data Assinatura',
            'Data Publicacao',
            'Status',
            'Fiscal Titular',
            'Qtd Aditivos',
            'Score Risco',
            'Nivel Risco',
            'Categorias Risco',
            'Pendencias',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
