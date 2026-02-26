<?php

namespace App\Enums;

enum FormatoDadosAbertos: string
{
    case Json = 'json';
    case Csv = 'csv';
    case Xml = 'xml';

    public function label(): string
    {
        return match ($this) {
            self::Json => 'JSON',
            self::Csv => 'CSV',
            self::Xml => 'XML',
        };
    }

    public function contentType(): string
    {
        return match ($this) {
            self::Json => 'application/json',
            self::Csv => 'text/csv; charset=UTF-8',
            self::Xml => 'application/xml; charset=UTF-8',
        };
    }

    public function extensao(): string
    {
        return match ($this) {
            self::Json => 'json',
            self::Csv => 'csv',
            self::Xml => 'xml',
        };
    }
}
