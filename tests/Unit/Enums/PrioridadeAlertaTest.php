<?php

namespace Tests\Unit\Enums;

use App\Enums\PrioridadeAlerta;
use PHPUnit\Framework\TestCase;

class PrioridadeAlertaTest extends TestCase
{
    public function test_todos_tem_label_cor_icone(): void
    {
        foreach (PrioridadeAlerta::cases() as $case) {
            $this->assertNotEmpty($case->label());
            $this->assertNotEmpty($case->cor());
            $this->assertNotEmpty($case->icone());
        }
    }

    public function test_urgente_cor_danger(): void
    {
        $this->assertEquals('danger', PrioridadeAlerta::Urgente->cor());
    }

    public function test_total_3_prioridades(): void
    {
        $this->assertCount(3, PrioridadeAlerta::cases());
    }
}
