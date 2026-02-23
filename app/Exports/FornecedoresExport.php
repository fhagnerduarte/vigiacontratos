<?php

namespace App\Exports;

use App\Models\Fornecedor;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FornecedoresExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query(): Builder
    {
        return Fornecedor::withCount('contratos')->orderBy('razao_social');
    }

    public function headings(): array
    {
        return [
            'Razao Social',
            'Nome Fantasia',
            'CNPJ',
            'Representante Legal',
            'Email',
            'Telefone',
            'Endereco',
            'Cidade',
            'UF',
            'CEP',
            'Qtd Contratos',
        ];
    }

    public function map($fornecedor): array
    {
        return [
            $fornecedor->razao_social,
            $fornecedor->nome_fantasia ?? '-',
            $fornecedor->cnpj,
            $fornecedor->representante_legal ?? '-',
            $fornecedor->email ?? '-',
            $fornecedor->telefone ?? '-',
            $fornecedor->endereco ?? '-',
            $fornecedor->cidade ?? '-',
            $fornecedor->uf ?? '-',
            $fornecedor->cep ?? '-',
            $fornecedor->contratos_count,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
