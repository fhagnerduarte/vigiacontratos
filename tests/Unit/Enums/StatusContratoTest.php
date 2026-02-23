<?php

namespace Tests\Unit\Enums;

use App\Enums\StatusContrato;
use PHPUnit\Framework\TestCase;

class StatusContratoTest extends TestCase
{
    public function test_todos_tem_label_e_cor(): void
    {
        foreach (StatusContrato::cases() as $case) {
            $this->assertNotEmpty($case->label());
            $this->assertNotEmpty($case->cor());
        }
    }

    public function test_total_6_cases(): void
    {
        $this->assertCount(6, StatusContrato::cases());
    }

    public function test_vigente_cor_success(): void
    {
        $this->assertEquals('success', StatusContrato::Vigente->cor());
    }
}
