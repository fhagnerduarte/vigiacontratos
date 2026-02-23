<?php

namespace Tests\Unit\Enums;

use App\Enums\StatusCompletudeDocumental;
use PHPUnit\Framework\TestCase;

class StatusCompletudeDocumentalTest extends TestCase
{
    public function test_todos_tem_label_cor_icone_descricao(): void
    {
        foreach (StatusCompletudeDocumental::cases() as $case) {
            $this->assertNotEmpty($case->label());
            $this->assertNotEmpty($case->cor());
            $this->assertNotEmpty($case->icone());
            $this->assertNotEmpty($case->descricao());
        }
    }

    public function test_completo_cor_success(): void
    {
        $this->assertEquals('success', StatusCompletudeDocumental::Completo->cor());
    }

    public function test_incompleto_cor_danger(): void
    {
        $this->assertEquals('danger', StatusCompletudeDocumental::Incompleto->cor());
    }

    public function test_total_3_status(): void
    {
        $this->assertCount(3, StatusCompletudeDocumental::cases());
    }
}
