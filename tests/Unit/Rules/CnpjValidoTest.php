<?php

namespace Tests\Unit\Rules;

use App\Rules\CnpjValido;
use PHPUnit\Framework\TestCase;

class CnpjValidoTest extends TestCase
{
    private CnpjValido $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new CnpjValido();
    }

    private function validar(string $cnpj): bool
    {
        $passed = true;
        $this->rule->validate('cnpj', $cnpj, function () use (&$passed) {
            $passed = false;
        });

        return $passed;
    }

    public function test_valida_cnpj_valido_sem_formatacao(): void
    {
        $this->assertTrue($this->validar('11222333000181'));
    }

    public function test_valida_cnpj_valido_com_formatacao(): void
    {
        $this->assertTrue($this->validar('11.222.333/0001-81'));
    }

    public function test_valida_cnpj_conhecido(): void
    {
        // CNPJ da Petrobras
        $this->assertTrue($this->validar('33000167000101'));
    }

    public function test_rejeita_cnpj_curto(): void
    {
        $this->assertFalse($this->validar('1122233300018'));
    }

    public function test_rejeita_cnpj_longo(): void
    {
        $this->assertFalse($this->validar('112223330001811'));
    }

    public function test_rejeita_cnpj_com_digitos_repetidos(): void
    {
        $this->assertFalse($this->validar('11111111111111'));
        $this->assertFalse($this->validar('00000000000000'));
    }

    public function test_rejeita_cnpj_com_primeiro_digito_errado(): void
    {
        // 11222333000181 e valido; trocar penultimo digito
        $this->assertFalse($this->validar('11222333000191'));
    }

    public function test_rejeita_cnpj_com_segundo_digito_errado(): void
    {
        // 11222333000181 e valido; trocar ultimo digito
        $this->assertFalse($this->validar('11222333000182'));
    }
}
