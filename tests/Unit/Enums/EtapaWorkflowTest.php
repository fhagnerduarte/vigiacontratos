<?php

namespace Tests\Unit\Enums;

use App\Enums\EtapaWorkflow;
use PHPUnit\Framework\TestCase;

class EtapaWorkflowTest extends TestCase
{
    public function test_todos_tem_label(): void
    {
        foreach (EtapaWorkflow::cases() as $case) {
            $this->assertNotEmpty($case->label());
        }
    }

    public function test_ordem_crescente(): void
    {
        $ordens = array_map(
            fn (EtapaWorkflow $etapa) => $etapa->ordem(),
            EtapaWorkflow::cases()
        );

        $this->assertEquals([1, 2, 3, 4, 5], $ordens);
    }

    public function test_role_responsavel_retorna_string(): void
    {
        foreach (EtapaWorkflow::cases() as $case) {
            $this->assertIsString($case->roleResponsavel());
            $this->assertNotEmpty($case->roleResponsavel());
        }
    }

    public function test_total_5_etapas(): void
    {
        $this->assertCount(5, EtapaWorkflow::cases());
    }

    public function test_solicitacao_ordem_1(): void
    {
        $this->assertEquals(1, EtapaWorkflow::Solicitacao->ordem());
    }

    public function test_homologacao_ordem_5(): void
    {
        $this->assertEquals(5, EtapaWorkflow::Homologacao->ordem());
    }
}
