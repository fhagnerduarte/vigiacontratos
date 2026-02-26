<?php

namespace App\Enums;

enum FormatoExportacaoTce: string
{
    case Xml = 'xml';
    case Csv = 'csv';
    case Excel = 'excel';
    case Pdf = 'pdf';

    public function label(): string
    {
        return match ($this) {
            self::Xml => 'XML',
            self::Csv => 'CSV',
            self::Excel => 'Excel',
            self::Pdf => 'PDF',
        };
    }

    public function extensao(): string
    {
        return match ($this) {
            self::Xml => '.xml',
            self::Csv => '.csv',
            self::Excel => '.xlsx',
            self::Pdf => '.pdf',
        };
    }

    public function contentType(): string
    {
        return match ($this) {
            self::Xml => 'application/xml',
            self::Csv => 'text/csv; charset=UTF-8',
            self::Excel => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            self::Pdf => 'application/pdf',
        };
    }
}
