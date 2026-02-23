<?php

namespace Tests\Unit\Enums;

use App\Enums\ModalidadeContratacao;
use PHPUnit\Framework\TestCase;

class ModalidadeContratacaoTest extends TestCase
{
    public function test_dispensa_e_sensivel(): void
    {
        $this->assertTrue(ModalidadeContratacao::Dispensa->isSensivel());
    }

    public function test_inexigibilidade_e_sensivel(): void
    {
        $this->assertTrue(ModalidadeContratacao::Inexigibilidade->isSensivel());
    }

    public function test_pregao_eletronico_nao_e_sensivel(): void
    {
        $this->assertFalse(ModalidadeContratacao::PregaoEletronico->isSensivel());
    }

    public function test_concorrencia_nao_e_sensivel(): void
    {
        $this->assertFalse(ModalidadeContratacao::Concorrencia->isSensivel());
    }

    public function test_todos_tem_label(): void
    {
        foreach (ModalidadeContratacao::cases() as $case) {
            $this->assertNotEmpty($case->label());
        }
    }
}
