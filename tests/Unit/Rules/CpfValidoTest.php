<?php

namespace Tests\Unit\Rules;

use App\Rules\CpfValido;
use PHPUnit\Framework\TestCase;

class CpfValidoTest extends TestCase
{
    private CpfValido $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new CpfValido();
    }

    private function validar(string $cpf): bool
    {
        $passed = true;
        $this->rule->validate('cpf', $cpf, function () use (&$passed) {
            $passed = false;
        });

        return $passed;
    }

    public function test_valida_cpf_valido_sem_formatacao(): void
    {
        $this->assertTrue($this->validar('52998224725'));
    }

    public function test_valida_cpf_valido_com_formatacao(): void
    {
        $this->assertTrue($this->validar('529.982.247-25'));
    }

    public function test_valida_cpf_conhecido(): void
    {
        $this->assertTrue($this->validar('11144477735'));
    }

    public function test_rejeita_cpf_curto(): void
    {
        $this->assertFalse($this->validar('5299822472'));
    }

    public function test_rejeita_cpf_longo(): void
    {
        $this->assertFalse($this->validar('529982247255'));
    }

    public function test_rejeita_cpf_com_digitos_repetidos(): void
    {
        $this->assertFalse($this->validar('11111111111'));
        $this->assertFalse($this->validar('00000000000'));
        $this->assertFalse($this->validar('99999999999'));
    }

    public function test_rejeita_cpf_com_primeiro_digito_errado(): void
    {
        // 52998224725 e valido; trocar penultimo digito
        $this->assertFalse($this->validar('52998224735'));
    }

    public function test_rejeita_cpf_com_segundo_digito_errado(): void
    {
        // 52998224725 e valido; trocar ultimo digito
        $this->assertFalse($this->validar('52998224726'));
    }

    public function test_formatar_cpf(): void
    {
        $this->assertEquals('529.982.247-25', CpfValido::formatarCpf('52998224725'));
    }

    public function test_formatar_cpf_ja_formatado(): void
    {
        $this->assertEquals('529.982.247-25', CpfValido::formatarCpf('529.982.247-25'));
    }
}
