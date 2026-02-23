<?php

namespace Tests\Unit\Services;

use App\Services\FornecedorService;
use PHPUnit\Framework\TestCase;

class FornecedorServiceTest extends TestCase
{
    public function test_validar_cnpj_valido_retorna_true(): void
    {
        $this->assertTrue(FornecedorService::validarCnpj('11222333000181'));
    }

    public function test_validar_cnpj_invalido_retorna_false(): void
    {
        $this->assertFalse(FornecedorService::validarCnpj('11222333000199'));
    }

    public function test_validar_cnpj_repetido_retorna_false(): void
    {
        $this->assertFalse(FornecedorService::validarCnpj('11111111111111'));
    }

    public function test_validar_cnpj_com_formatacao(): void
    {
        $this->assertTrue(FornecedorService::validarCnpj('11.222.333/0001-81'));
    }

    public function test_formatar_cnpj(): void
    {
        $this->assertEquals('11.222.333/0001-81', FornecedorService::formatarCnpj('11222333000181'));
    }

    public function test_formatar_cnpj_ja_formatado(): void
    {
        $this->assertEquals('11.222.333/0001-81', FornecedorService::formatarCnpj('11.222.333/0001-81'));
    }

    public function test_validar_cnpj_vazio_retorna_false(): void
    {
        $this->assertFalse(FornecedorService::validarCnpj(''));
    }

    public function test_validar_cnpj_curto_retorna_false(): void
    {
        $this->assertFalse(FornecedorService::validarCnpj('1122233300018'));
    }
}
