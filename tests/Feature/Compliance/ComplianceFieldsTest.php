<?php

namespace Tests\Feature\Compliance;

use App\Enums\RegimeExecucao;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Enums\TipoFiscal;
use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use App\Services\FiscalService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ComplianceFieldsTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->admin = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
    }

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);
    }

    // --- Enums ---

    public function test_regime_execucao_enum_tem_5_cases(): void
    {
        $this->assertCount(5, RegimeExecucao::cases());
    }

    public function test_regime_execucao_enum_labels(): void
    {
        $this->assertEquals('Empreitada Integral', RegimeExecucao::EmpreitadaIntegral->label());
        $this->assertEquals('Preco Unitario', RegimeExecucao::PrecoUnitario->label());
        $this->assertEquals('Preco Global', RegimeExecucao::PrecoGlobal->label());
        $this->assertEquals('Tarefa', RegimeExecucao::Tarefa->label());
        $this->assertEquals('Contratacao Integrada', RegimeExecucao::ContratacaoIntegrada->label());
    }

    public function test_tipo_fiscal_enum_tem_2_cases(): void
    {
        $this->assertCount(2, TipoFiscal::cases());
        $this->assertEquals('Titular', TipoFiscal::Titular->label());
        $this->assertEquals('Substituto', TipoFiscal::Substituto->label());
    }

    public function test_tipo_documento_contratual_novos_3_cases(): void
    {
        $this->assertCount(15, TipoDocumentoContratual::cases());
        $this->assertEquals('Termo de Recebimento Provisorio', TipoDocumentoContratual::TermoRecebimentoProvisorio->label());
        $this->assertEquals('Termo de Recebimento Definitivo', TipoDocumentoContratual::TermoRecebimentoDefinitivo->label());
        $this->assertEquals('Portaria de Designacao Fiscal', TipoDocumentoContratual::PortariaDesignacaoFiscal->label());
    }

    // --- Model Contrato ---

    public function test_contrato_publicado_accessor_true_quando_data_publicacao_preenchida(): void
    {
        $contrato = Contrato::factory()->create([
            'data_publicacao' => '2026-01-15',
        ]);

        $this->assertTrue($contrato->publicado);
    }

    public function test_contrato_publicado_accessor_false_quando_data_publicacao_nula(): void
    {
        $contrato = Contrato::factory()->create([
            'data_publicacao' => null,
        ]);

        $this->assertFalse($contrato->publicado);
    }

    public function test_contrato_salva_campos_compliance(): void
    {
        $contrato = Contrato::factory()->create([
            'data_assinatura' => '2026-01-01',
            'regime_execucao' => RegimeExecucao::PrecoGlobal,
            'condicoes_pagamento' => 'Pagamento mensal conforme medicao',
            'garantias' => 'Garantia de 5% do valor global',
            'data_publicacao' => '2026-01-05',
            'veiculo_publicacao' => 'Diario Oficial do Municipio',
            'link_transparencia' => 'https://transparencia.municipio.gov.br/contrato/123',
        ]);

        $contrato->refresh();

        $this->assertEquals('2026-01-01', $contrato->data_assinatura->format('Y-m-d'));
        $this->assertEquals(RegimeExecucao::PrecoGlobal, $contrato->regime_execucao);
        $this->assertEquals('Pagamento mensal conforme medicao', $contrato->condicoes_pagamento);
        $this->assertEquals('Garantia de 5% do valor global', $contrato->garantias);
        $this->assertEquals('2026-01-05', $contrato->data_publicacao->format('Y-m-d'));
        $this->assertEquals('Diario Oficial do Municipio', $contrato->veiculo_publicacao);
        $this->assertEquals('https://transparencia.municipio.gov.br/contrato/123', $contrato->link_transparencia);
    }

    public function test_contrato_fiscal_substituto_relationship(): void
    {
        $contrato = Contrato::factory()->create();

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
        ]);

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'substituto',
            'nome' => 'Fiscal Substituto Teste',
        ]);

        $contrato->refresh();

        $this->assertNotNull($contrato->fiscalAtual);
        $this->assertNotNull($contrato->fiscalSubstituto);
        $this->assertEquals('substituto', $contrato->fiscalSubstituto->tipo_fiscal->value);
        $this->assertEquals('Fiscal Substituto Teste', $contrato->fiscalSubstituto->nome);
    }

    public function test_contrato_fiscal_atual_retorna_apenas_titular(): void
    {
        $contrato = Contrato::factory()->create();

        $titular = Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
            'nome' => 'Titular',
        ]);

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'substituto',
            'nome' => 'Substituto',
        ]);

        $contrato->refresh();

        $this->assertEquals('Titular', $contrato->fiscalAtual->nome);
        $this->assertEquals('titular', $contrato->fiscalAtual->tipo_fiscal->value);
    }

    // --- Model Fiscal ---

    public function test_fiscal_salva_tipo_fiscal_e_portaria(): void
    {
        $contrato = Contrato::factory()->create();

        $fiscal = Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo_fiscal' => 'substituto',
            'portaria_designacao' => 'Portaria 123/2026',
            'data_ultimo_relatorio' => '2026-02-01',
        ]);

        $fiscal->refresh();

        $this->assertEquals(TipoFiscal::Substituto, $fiscal->tipo_fiscal);
        $this->assertEquals('Portaria 123/2026', $fiscal->portaria_designacao);
        $this->assertEquals('2026-02-01', $fiscal->data_ultimo_relatorio->format('Y-m-d'));
    }

    public function test_fiscal_scope_titular(): void
    {
        $contrato = Contrato::factory()->create();

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo_fiscal' => 'titular',
        ]);

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'tipo_fiscal' => 'substituto',
        ]);

        $this->assertEquals(1, Fiscal::titular()->where('contrato_id', $contrato->id)->count());
        $this->assertEquals(1, Fiscal::substituto()->where('contrato_id', $contrato->id)->count());
    }

    public function test_fiscal_tipo_fiscal_default_titular(): void
    {
        $contrato = Contrato::factory()->create();
        $servidor = Servidor::factory()->create();

        $fiscal = FiscalService::designar($contrato, [
            'servidor_id' => $servidor->id,
        ]);

        $this->assertEquals(TipoFiscal::Titular, $fiscal->tipo_fiscal);
    }

    // --- FiscalService ---

    public function test_fiscal_service_designar_com_tipo_fiscal(): void
    {
        $contrato = Contrato::factory()->create();
        $servidor = Servidor::factory()->create();

        $fiscal = FiscalService::designar($contrato, [
            'servidor_id' => $servidor->id,
            'portaria_designacao' => 'Portaria 456/2026',
        ], TipoFiscal::Titular);

        $this->assertEquals(TipoFiscal::Titular, $fiscal->tipo_fiscal);
        $this->assertEquals('Portaria 456/2026', $fiscal->portaria_designacao);
        $this->assertTrue($fiscal->is_atual);
    }

    public function test_fiscal_service_designar_substituto(): void
    {
        $contrato = Contrato::factory()->create();
        $servidorTitular = Servidor::factory()->create(['nome' => 'Titular']);
        $servidorSubstituto = Servidor::factory()->create(['nome' => 'Substituto']);

        // Designa titular
        $titular = FiscalService::designar($contrato, [
            'servidor_id' => $servidorTitular->id,
        ]);

        // Designa substituto
        $substituto = FiscalService::designarSubstituto($contrato, [
            'servidor_id' => $servidorSubstituto->id,
        ]);

        // Ambos devem estar ativos
        $this->assertTrue($titular->fresh()->is_atual);
        $this->assertTrue($substituto->is_atual);
        $this->assertEquals(TipoFiscal::Titular, $titular->tipo_fiscal);
        $this->assertEquals(TipoFiscal::Substituto, $substituto->tipo_fiscal);
    }

    public function test_fiscal_service_designar_substituto_desativa_anterior(): void
    {
        $contrato = Contrato::factory()->create();
        $servidor1 = Servidor::factory()->create();
        $servidor2 = Servidor::factory()->create();

        // Designa titular
        FiscalService::designar($contrato, ['servidor_id' => $servidor1->id]);

        // Cria substituto 1
        $sub1 = Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'substituto',
        ]);

        // Designa novo substituto (deve desativar sub1)
        $contrato->refresh();
        $sub2 = FiscalService::designarSubstituto($contrato, [
            'servidor_id' => $servidor2->id,
        ]);

        $this->assertFalse($sub1->fresh()->is_atual);
        $this->assertNotNull($sub1->fresh()->data_fim);
        $this->assertTrue($sub2->is_atual);
    }

    public function test_fiscal_service_trocar_mantém_substituto(): void
    {
        $contrato = Contrato::factory()->create();
        $servidorTitular1 = Servidor::factory()->create();
        $servidorTitular2 = Servidor::factory()->create();
        $servidorSubstituto = Servidor::factory()->create();

        // Designa titular e substituto
        FiscalService::designar($contrato, ['servidor_id' => $servidorTitular1->id]);
        $contrato->refresh();
        $substituto = FiscalService::designarSubstituto($contrato, [
            'servidor_id' => $servidorSubstituto->id,
        ]);

        // Troca titular (nao deve afetar substituto)
        $contrato->refresh();
        $novoTitular = FiscalService::trocar($contrato, [
            'servidor_id' => $servidorTitular2->id,
        ]);

        $this->assertTrue($novoTitular->is_atual);
        $this->assertEquals(TipoFiscal::Titular, $novoTitular->tipo_fiscal);
        // Substituto permanece ativo
        $this->assertTrue($substituto->fresh()->is_atual);
    }

    // --- Controller: Store com novos campos ---

    public function test_store_contrato_com_campos_compliance(): void
    {
        $this->actAsAdmin();

        $fornecedor = Fornecedor::factory()->create();
        $secretaria = Secretaria::factory()->create();
        $this->admin->secretarias()->attach($secretaria->id);
        $servidor = Servidor::factory()->create();
        $servidorSubstituto = Servidor::factory()->create();

        $response = $this->post(route('tenant.contratos.store'), [
            'ano' => '2026',
            'objeto' => 'Teste compliance fields',
            'tipo' => 'servico',
            'modalidade_contratacao' => 'pregao_eletronico',
            'secretaria_id' => $secretaria->id,
            'fornecedor_id' => $fornecedor->id,
            'numero_processo' => '12345/2026',
            'valor_global' => 100000,
            'data_assinatura' => '2026-01-01',
            'data_inicio' => '2026-01-15',
            'data_fim' => '2027-01-15',
            'regime_execucao' => 'preco_global',
            'condicoes_pagamento' => 'Pagamento conforme medicao',
            'garantias' => 'Garantia de 5%',
            'data_publicacao' => '2026-01-10',
            'veiculo_publicacao' => 'Diario Oficial',
            'link_transparencia' => 'https://transparencia.gov.br/123',
            'fiscal_servidor_id' => $servidor->id,
            'fiscal_substituto_servidor_id' => $servidorSubstituto->id,
            'portaria_designacao' => 'Portaria 789/2026',
            'servidor_id' => $servidor->id,
        ]);

        $response->assertRedirect(route('tenant.contratos.index'));

        // Verificar contrato criado com campos compliance
        $contrato = Contrato::latest()->first();
        $this->assertEquals('2026-01-01', $contrato->data_assinatura->format('Y-m-d'));
        $this->assertEquals(RegimeExecucao::PrecoGlobal, $contrato->regime_execucao);
        $this->assertEquals('Pagamento conforme medicao', $contrato->condicoes_pagamento);
        $this->assertEquals('Garantia de 5%', $contrato->garantias);
        $this->assertEquals('2026-01-10', $contrato->data_publicacao->format('Y-m-d'));
        $this->assertEquals('Diario Oficial', $contrato->veiculo_publicacao);
        $this->assertTrue($contrato->publicado);

        // Verificar fiscal titular
        $this->assertNotNull($contrato->fiscalAtual);
        $this->assertEquals(TipoFiscal::Titular, $contrato->fiscalAtual->tipo_fiscal);
        $this->assertEquals('Portaria 789/2026', $contrato->fiscalAtual->portaria_designacao);

        // Verificar fiscal substituto
        $this->assertNotNull($contrato->fiscalSubstituto);
        $this->assertEquals(TipoFiscal::Substituto, $contrato->fiscalSubstituto->tipo_fiscal);
    }

    public function test_store_contrato_sem_fiscal_substituto(): void
    {
        $this->actAsAdmin();

        $fornecedor = Fornecedor::factory()->create();
        $secretaria = Secretaria::factory()->create();
        $this->admin->secretarias()->attach($secretaria->id);
        $servidor = Servidor::factory()->create();

        $response = $this->post(route('tenant.contratos.store'), [
            'ano' => '2026',
            'objeto' => 'Contrato sem substituto',
            'tipo' => 'servico',
            'modalidade_contratacao' => 'pregao_eletronico',
            'secretaria_id' => $secretaria->id,
            'fornecedor_id' => $fornecedor->id,
            'numero_processo' => '99999/2026',
            'valor_global' => 50000,
            'data_inicio' => '2026-02-01',
            'data_fim' => '2027-02-01',
            'fiscal_servidor_id' => $servidor->id,
            'servidor_id' => $servidor->id,
        ]);

        $response->assertRedirect(route('tenant.contratos.index'));

        $contrato = Contrato::latest()->first();
        $this->assertNotNull($contrato->fiscalAtual);
        $this->assertNull($contrato->fiscalSubstituto);
    }

    public function test_validacao_data_assinatura_antes_data_inicio(): void
    {
        $this->actAsAdmin();

        $fornecedor = Fornecedor::factory()->create();
        $secretaria = Secretaria::factory()->create();
        $this->admin->secretarias()->attach($secretaria->id);
        $servidor = Servidor::factory()->create();

        $response = $this->post(route('tenant.contratos.store'), [
            'ano' => '2026',
            'objeto' => 'Teste validacao assinatura',
            'tipo' => 'servico',
            'modalidade_contratacao' => 'pregao_eletronico',
            'secretaria_id' => $secretaria->id,
            'fornecedor_id' => $fornecedor->id,
            'numero_processo' => '11111/2026',
            'valor_global' => 50000,
            'data_assinatura' => '2026-03-01', // depois de data_inicio
            'data_inicio' => '2026-02-01',
            'data_fim' => '2027-02-01',
            'fiscal_servidor_id' => $servidor->id,
            'servidor_id' => $servidor->id,
        ]);

        $response->assertSessionHasErrors('data_assinatura');
    }

    public function test_validacao_fiscal_substituto_diferente_do_titular(): void
    {
        $this->actAsAdmin();

        $fornecedor = Fornecedor::factory()->create();
        $secretaria = Secretaria::factory()->create();
        $this->admin->secretarias()->attach($secretaria->id);
        $servidor = Servidor::factory()->create();

        $response = $this->post(route('tenant.contratos.store'), [
            'ano' => '2026',
            'objeto' => 'Teste fiscal igual',
            'tipo' => 'servico',
            'modalidade_contratacao' => 'pregao_eletronico',
            'secretaria_id' => $secretaria->id,
            'fornecedor_id' => $fornecedor->id,
            'numero_processo' => '22222/2026',
            'valor_global' => 50000,
            'data_inicio' => '2026-02-01',
            'data_fim' => '2027-02-01',
            'fiscal_servidor_id' => $servidor->id,
            'fiscal_substituto_servidor_id' => $servidor->id, // mesmo servidor
            'servidor_id' => $servidor->id,
        ]);

        $response->assertSessionHasErrors('fiscal_substituto_servidor_id');
    }

    public function test_show_contrato_carrega_fiscal_substituto(): void
    {
        $this->actAsAdmin();

        $secretaria = Secretaria::factory()->create();
        $this->admin->secretarias()->attach($secretaria->id);

        $contrato = Contrato::factory()->create([
            'secretaria_id' => $secretaria->id,
            'data_publicacao' => '2026-01-15',
            'veiculo_publicacao' => 'DOM',
        ]);

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'titular',
        ]);

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
            'tipo_fiscal' => 'substituto',
            'nome' => 'Substituto Teste',
        ]);

        $response = $this->get(route('tenant.contratos.show', $contrato));

        $response->assertOk();
        $response->assertSee('Substituto Teste');
    }

    public function test_update_contrato_com_campos_compliance(): void
    {
        $this->actAsAdmin();

        $secretaria = Secretaria::factory()->create();
        $this->admin->secretarias()->attach($secretaria->id);

        $contrato = Contrato::factory()->vigente()->create([
            'secretaria_id' => $secretaria->id,
            'tipo' => TipoContrato::Servico,
            'data_inicio' => '2026-02-01',
            'modalidade_contratacao' => 'pregao_eletronico',
        ]);

        $response = $this->put(route('tenant.contratos.update', $contrato), [
            'objeto' => $contrato->objeto,
            'tipo' => $contrato->tipo->value,
            'modalidade_contratacao' => $contrato->modalidade_contratacao->value,
            'secretaria_id' => $secretaria->id,
            'fornecedor_id' => $contrato->fornecedor_id,
            'numero_processo' => $contrato->numero_processo,
            'valor_global' => $contrato->valor_global,
            'data_assinatura' => '2026-01-01',
            'data_inicio' => '2026-02-01',
            'data_fim' => $contrato->data_fim->format('Y-m-d'),
            'regime_execucao' => 'empreitada_integral',
            'condicoes_pagamento' => 'Teste condicoes',
            'garantias' => 'Teste garantias',
            'data_publicacao' => '2026-01-10',
            'veiculo_publicacao' => 'Diario Oficial',
            'link_transparencia' => 'https://transparencia.gov.br/456',
        ]);

        $response->assertRedirect(route('tenant.contratos.show', $contrato));

        $contrato->refresh();
        $this->assertEquals(RegimeExecucao::EmpreitadaIntegral, $contrato->regime_execucao);
        $this->assertEquals('Teste condicoes', $contrato->condicoes_pagamento);
        $this->assertEquals('Teste garantias', $contrato->garantias);
        $this->assertTrue($contrato->publicado);
    }
}
