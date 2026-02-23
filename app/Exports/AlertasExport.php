<?php

namespace App\Exports;

use App\Models\Alerta;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlertasExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private array $filtros = [])
    {
    }

    public function query(): Builder
    {
        $query = Alerta::with(['contrato.fornecedor', 'contrato.secretaria'])
            ->orderByRaw("FIELD(prioridade, 'urgente', 'atencao', 'informativo')")
            ->orderBy('data_vencimento');

        if (! empty($this->filtros['status'])) {
            $query->where('status', $this->filtros['status']);
        } else {
            $query->naoResolvidos();
        }

        if (! empty($this->filtros['prioridade'])) {
            $query->where('prioridade', $this->filtros['prioridade']);
        }

        if (! empty($this->filtros['tipo_evento'])) {
            $query->where('tipo_evento', $this->filtros['tipo_evento']);
        }

        if (! empty($this->filtros['secretaria_id'])) {
            $query->whereHas('contrato', fn ($q) => $q->where('secretaria_id', $this->filtros['secretaria_id']));
        }

        if (! empty($this->filtros['tipo_contrato'])) {
            $query->whereHas('contrato', fn ($q) => $q->where('tipo', $this->filtros['tipo_contrato']));
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Contrato',
            'Fornecedor',
            'Secretaria',
            'Tipo Evento',
            'Prioridade',
            'Status',
            'Dias p/ Vencimento',
            'Data Vencimento',
            'Data Disparo',
            'Mensagem',
        ];
    }

    public function map($alerta): array
    {
        return [
            $alerta->contrato?->numero . '/' . $alerta->contrato?->ano,
            $alerta->contrato?->fornecedor?->razao_social ?? '-',
            $alerta->contrato?->secretaria?->nome ?? '-',
            $alerta->tipo_evento?->label() ?? '-',
            $alerta->prioridade?->label() ?? '-',
            $alerta->status?->label() ?? '-',
            $alerta->dias_para_vencimento,
            $alerta->data_vencimento?->format('d/m/Y') ?? '-',
            $alerta->data_disparo?->format('d/m/Y H:i') ?? '-',
            $alerta->mensagem ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
