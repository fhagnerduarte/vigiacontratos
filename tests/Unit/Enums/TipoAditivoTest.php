<?php

namespace Tests\Unit\Enums;

use App\Enums\TipoAditivo;
use PHPUnit\Framework\TestCase;

class TipoAditivoTest extends TestCase
{
    public function test_prazo_altera_prazo_nao_altera_valor(): void
    {
        $this->assertTrue(TipoAditivo::Prazo->alteraPrazo());
        $this->assertFalse(TipoAditivo::Prazo->alteraValor());
    }

    public function test_valor_altera_valor_nao_altera_prazo(): void
    {
        $this->assertTrue(TipoAditivo::Valor->alteraValor());
        $this->assertFalse(TipoAditivo::Valor->alteraPrazo());
    }

    public function test_prazo_e_valor_altera_ambos(): void
    {
        $this->assertTrue(TipoAditivo::PrazoEValor->alteraPrazo());
        $this->assertTrue(TipoAditivo::PrazoEValor->alteraValor());
    }

    public function test_supressao_exige_supressao(): void
    {
        $this->assertTrue(TipoAditivo::Supressao->exigeSupressao());
        $this->assertFalse(TipoAditivo::Prazo->exigeSupressao());
    }

    public function test_misto_altera_prazo_valor_e_exige_supressao(): void
    {
        $this->assertTrue(TipoAditivo::Misto->alteraPrazo());
        $this->assertTrue(TipoAditivo::Misto->alteraValor());
        $this->assertTrue(TipoAditivo::Misto->exigeSupressao());
    }

    public function test_reequilibrio_altera_valor_nao_prazo(): void
    {
        $this->assertTrue(TipoAditivo::Reequilibrio->alteraValor());
        $this->assertFalse(TipoAditivo::Reequilibrio->alteraPrazo());
    }

    public function test_alteracao_clausula_nao_altera_prazo_nem_valor(): void
    {
        $this->assertFalse(TipoAditivo::AlteracaoClausula->alteraPrazo());
        $this->assertFalse(TipoAditivo::AlteracaoClausula->alteraValor());
        $this->assertFalse(TipoAditivo::AlteracaoClausula->exigeSupressao());
    }

    public function test_todos_tem_label_e_cor(): void
    {
        foreach (TipoAditivo::cases() as $case) {
            $this->assertNotEmpty($case->label());
            $this->assertNotEmpty($case->cor());
        }
    }
}
