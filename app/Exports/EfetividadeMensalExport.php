<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EfetividadeMensalExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private array $dados)
    {
    }

    public function collection(): Collection
    {
        return collect($this->dados['contratos'])->map(function ($contrato) {
            $statusLabel = match ($contrato['status_efetividade']) {
                'regularizado_a_tempo' => 'Regularizado a Tempo',
                'regularizado_retroativo' => 'Regularizado Retroativamente',
                'vencido_sem_acao' => 'Vencido sem Acao',
                default => '-',
            };

            return [
                $contrato['numero'],
                $contrato['objeto'],
                $contrato['secretaria'],
                $contrato['fornecedor'],
                number_format($contrato['valor_global'], 2, ',', '.'),
                $contrato['data_fim'],
                $contrato['status_atual'],
                $statusLabel,
                $contrato['aditivo'],
                $contrato['dias_antecipacao'] !== null ? $contrato['dias_antecipacao'] . ' dias' : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Numero',
            'Objeto',
            'Secretaria',
            'Fornecedor',
            'Valor Global (R$)',
            'Data Fim',
            'Status Atual',
            'Efetividade',
            'Aditivo',
            'Dias Antecipacao',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
