<?php

namespace Tests\Unit\Services;

use App\Enums\NivelRisco;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use App\Services\ContratoService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ContratoServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_gerar_numero_primeiro_contrato_do_ano(): void
    {
        $numero = ContratoService::gerarNumero('2026');
        $this->assertEquals('001/2026', $numero);
    }

    public function test_gerar_numero_sequencial(): void
    {
        Contrato::factory()->create(['numero' => '001/2026', 'ano' => '2026']);
        $numero = ContratoService::gerarNumero('2026');
        $this->assertEquals('002/2026', $numero);
    }

    public function test_gerar_numero_terceiro_contrato(): void
    {
        // Usa ano 2099 para evitar conflito com dados de outros testes
        Contrato::factory()->create(['numero' => '001/2099', 'ano' => '2099']);
        Contrato::factory()->create(['numero' => '002/2099', 'ano' => '2099']);
        $numero = ContratoService::gerarNumero('2099');
        $this->assertEquals('003/2099', $numero);
    }

    public function test_gerar_numero_ano_diferente_reinicia(): void
    {
        Contrato::factory()->create(['numero' => '005/2098', 'ano' => '2098']);
        $numero = ContratoService::gerarNumero('2097');
        $this->assertEquals('001/2097', $numero);
    }

    public function test_calcular_prazo_meses_12(): void
    {
        $meses = ContratoService::calcularPrazoMeses('2026-01-01', '2027-01-01');
        $this->assertEquals(12, $meses);
    }

    public function test_calcular_prazo_meses_6(): void
    {
        $meses = ContratoService::calcularPrazoMeses('2026-01-01', '2026-07-01');
        $this->assertEquals(6, $meses);
    }

    public function test_criar_contrato_completo(): void
    {
        $user = $this->createAdminUser();
        $fornecedor = Fornecedor::factory()->create();
        $secretaria = Secretaria::factory()->create();
        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        // Usa ano distante para evitar colisao com contratos criados por factories de outros testes
        $ano = '2090';

        $dados = [
            'objeto' => 'Contrato de teste automatizado',
            'tipo' => TipoContrato::Servico->value,
            'modalidade_contratacao' => 'pregao_eletronico',
            'fornecedor_id' => $fornecedor->id,
            'secretaria_id' => $secretaria->id,
            'servidor_id' => $servidor->id,
            'unidade_gestora' => 'Prefeitura',
            'ano' => $ano,
            'data_inicio' => '2090-01-01',
            'data_fim' => '2091-01-01',
            'valor_global' => 120000.00,
            'valor_mensal' => 10000.00,
            'tipo_pagamento' => 'mensal',
            'fonte_recurso' => 'Recursos Proprios',
            'dotacao_orcamentaria' => '01.02.03.004.0005.1.000.00',
            'numero_empenho' => '0001/2090',
            'numero_processo' => '00001/2090',
            'categoria' => 'nao_essencial',
            'categoria_servico' => 'tecnologia',
        ];

        $contrato = ContratoService::criar($dados, null, $user, '127.0.0.1');

        $this->assertNotNull($contrato->id);
        $this->assertEquals('001/' . $ano, $contrato->numero);
        $this->assertEquals(StatusContrato::Vigente, $contrato->status);
        $this->assertEquals(12, $contrato->prazo_meses);
        $this->assertNotNull($contrato->score_risco);
    }

    public function test_criar_contrato_com_fiscal(): void
    {
        $user = $this->createAdminUser();
        $fornecedor = Fornecedor::factory()->create();
        $secretaria = Secretaria::factory()->create();
        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);
        $servidorFiscal = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        $dados = [
            'objeto' => 'Contrato com fiscal',
            'tipo' => TipoContrato::Servico->value,
            'modalidade_contratacao' => 'pregao_eletronico',
            'fornecedor_id' => $fornecedor->id,
            'secretaria_id' => $secretaria->id,
            'servidor_id' => $servidor->id,
            'unidade_gestora' => 'Prefeitura',
            'ano' => '2091',
            'data_inicio' => '2091-01-01',
            'data_fim' => '2092-01-01',
            'valor_global' => 120000.00,
            'valor_mensal' => 10000.00,
            'tipo_pagamento' => 'mensal',
            'fonte_recurso' => 'Recursos Proprios',
            'dotacao_orcamentaria' => '01.02.03.004.0005.1.000.00',
            'numero_empenho' => '0001/2091',
            'numero_processo' => '00001/2091',
            'categoria' => 'nao_essencial',
            'categoria_servico' => 'tecnologia',
        ];

        $dadosFiscal = ['servidor_id' => $servidorFiscal->id];

        $contrato = ContratoService::criar($dados, $dadosFiscal, $user, '127.0.0.1');

        $this->assertNotNull($contrato->fiscalAtual);
        $this->assertEquals($servidorFiscal->nome, $contrato->fiscalAtual->nome);
    }

    public function test_atualizar_contrato_registra_auditoria(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->create();

        $contrato = ContratoService::atualizar($contrato, [
            'objeto' => 'Objeto atualizado para teste',
        ], $user, '127.0.0.1');

        $this->assertEquals('Objeto atualizado para teste', $contrato->objeto);
        $this->assertTrue($contrato->historicoAlteracoes()->exists());
    }

    public function test_atualizar_contrato_recalcula_risco(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->create(['score_risco' => 0]);

        $contrato = ContratoService::atualizar($contrato, [
            'valor_global' => 2000000.00, // Acima de 1M = +10pts risco financeiro
        ], $user, '127.0.0.1');

        $this->assertGreaterThan(0, $contrato->score_risco);
    }
}
