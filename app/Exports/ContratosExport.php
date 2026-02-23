<?php

namespace App\Exports;

use App\Models\Contrato;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContratosExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private array $filtros = [])
    {
    }

    public function query(): Builder
    {
        $query = Contrato::with(['fornecedor:id,razao_social', 'secretaria:id,nome', 'fiscalAtual'])
            ->orderBy('created_at', 'desc');

        if (! empty($this->filtros['status'])) {
            $query->where('status', $this->filtros['status']);
        }

        if (! empty($this->filtros['secretaria_id'])) {
            $query->where('secretaria_id', $this->filtros['secretaria_id']);
        }

        if (! empty($this->filtros['modalidade'])) {
            $query->where('modalidade_contratacao', $this->filtros['modalidade']);
        }

        if (! empty($this->filtros['nivel_risco'])) {
            $query->where('nivel_risco', $this->filtros['nivel_risco']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Numero',
            'Objeto',
            'Status',
            'Tipo',
            'Modalidade',
            'Fornecedor',
            'Secretaria',
            'Valor Global (R$)',
            'Data Inicio',
            'Data Fim',
            'Prazo (meses)',
            'Nivel Risco',
            'Score Risco',
            '% Executado',
            'Fiscal Atual',
        ];
    }

    public function map($contrato): array
    {
        return [
            $contrato->numero . '/' . $contrato->ano,
            $contrato->objeto,
            $contrato->status?->label() ?? '-',
            $contrato->tipo?->label() ?? '-',
            $contrato->modalidade_contratacao?->label() ?? '-',
            $contrato->fornecedor?->razao_social ?? '-',
            $contrato->secretaria?->nome ?? '-',
            number_format((float) $contrato->valor_global, 2, ',', '.'),
            $contrato->data_inicio?->format('d/m/Y') ?? '-',
            $contrato->data_fim?->format('d/m/Y') ?? '-',
            $contrato->prazo_meses ?? '-',
            $contrato->nivel_risco?->label() ?? '-',
            $contrato->score_risco ?? 0,
            number_format((float) $contrato->percentual_executado, 2, ',', '.') . '%',
            $contrato->fiscalAtual?->nome ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
