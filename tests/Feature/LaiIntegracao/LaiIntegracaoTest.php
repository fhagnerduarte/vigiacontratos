<?php

namespace Tests\Feature\LaiIntegracao;

use App\Enums\ClassificacaoRespostaLai;
use App\Enums\ClassificacaoSigilo;
use App\Enums\RegimeExecucao;
use App\Enums\StatusContrato;
use App\Enums\StatusSolicitacaoLai;
use App\Enums\TipoContrato;
use App\Models\Contrato;
use App\Models\Secretaria;
use App\Models\SolicitacaoLai;
use App\Models\User;
use App\Services\ClassificacaoService;
use App\Services\PublicacaoAutomaticaService;
use App\Services\RelatorioService;
use App\Services\RiscoService;
use App\Services\SolicitacaoLaiService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class LaiIntegracaoTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    private User $admin;
    private Secretaria $secretaria;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->seedBaseData();

        $this->setUpTenant();
        $this->secretaria = Secretaria::factory()->create();
        $this->admin = $this->createAdminUser();
        $this->admin->secretarias()->attach($this->secretaria->id);
        $this->actingAs($this->admin);
    }

    private function criarContratoPublico(array $overrides = []): Contrato
    {
        return Contrato::factory()->create(array_merge([
            'secretaria_id' => $this->secretaria->id,
            'tipo' => TipoContrato::Servico->value,
            'status' => StatusContrato::Vigente->value,
            'data_inicio' => now()->subMonths(6),
            'data_assinatura' => now()->subMonths(6)->subDays(5),
            'data_fim' => now()->addMonths(6),
            'valor_global' => 500000.00,
            'prazo_meses' => 12,
            'regime_execucao' => RegimeExecucao::PrecoGlobal->value,
            'data_publicacao' => now()->subMonths(6)->addDay(),
            'veiculo_publicacao' => 'Diario Oficial do Municipio',
            'link_transparencia' => 'https://transparencia.municipio.gov.br/contrato/123',
            'numero_processo' => '001/2025',
            'fundamento_legal' => 'Lei 14.133/2021, Art. 75',
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'publicado_portal' => true,
        ], $overrides));
    }

    private function criarSolicitacaoLai(array $overrides = []): SolicitacaoLai
    {
        return SolicitacaoLaiService::criar(array_merge([
            'nome_solicitante' => 'Cidadao Teste',
            'email_solicitante' => 'cidadao@teste.com',
            'cpf_solicitante' => '123.456.789-00',
            'assunto' => 'Consulta sobre contratos vigentes',
            'descricao' => 'Solicito informacoes detalhadas sobre os contratos vigentes da secretaria de educacao.',
        ], $overrides));
    }

    // ============================================================
    // RISCO: Categoria Transparencia
    // ============================================================

    public function test_risco_transparencia_zerado_contrato_completo(): void
    {
        $contrato = $this->criarContratoPublico();

        $risco = RiscoService::calcularExpandido($contrato);

        $this->assertArrayHasKey('transparencia', $risco['categorias']);
        $this->assertEquals(0, $risco['categorias']['transparencia']['score']);
        $this->assertEmpty($risco['categorias']['transparencia']['criterios']);
    }

    public function test_risco_transparencia_nao_publicado_portal(): void
    {
        $contrato = $this->criarContratoPublico([
            'publicado_portal' => false,
        ]);

        $risco = RiscoService::calcularExpandido($contrato);
        $transparencia = $risco['categorias']['transparencia'];

        $this->assertEquals(10, $transparencia['score']);
        $this->assertCount(1, $transparencia['criterios']);
        $this->assertStringContainsString('nao publicado no portal', $transparencia['criterios'][0]);
    }

    public function test_risco_transparencia_sigilo_sem_justificativa(): void
    {
        $contrato = $this->criarContratoPublico([
            'classificacao_sigilo' => ClassificacaoSigilo::Reservado->value,
            'justificativa_sigilo' => null,
            'publicado_portal' => false,
        ]);

        $risco = RiscoService::calcularExpandido($contrato);
        $transparencia = $risco['categorias']['transparencia'];

        $this->assertEquals(10, $transparencia['score']);
        $this->assertStringContainsString('sigilo sem justificativa', $transparencia['criterios'][0]);
    }

    public function test_risco_transparencia_dados_publicacao_incompletos(): void
    {
        $contrato = $this->criarContratoPublico([
            'data_publicacao' => null,
            'veiculo_publicacao' => null,
        ]);

        $risco = RiscoService::calcularExpandido($contrato);
        $transparencia = $risco['categorias']['transparencia'];

        $this->assertEquals(5, $transparencia['score']);
        $this->assertStringContainsString('Dados de publicacao incompletos', $transparencia['criterios'][0]);
    }

    // ============================================================
    // CICLO: Fluxos Completos LAI
    // ============================================================

    public function test_ciclo_classificar_publicar_verificar_risco(): void
    {
        // 1. Criar contrato publico e classificar como reservado
        $contrato = $this->criarContratoPublico([
            'data_inicio' => now()->subMonths(7),
            'data_assinatura' => now()->subMonths(7)->subDays(5),
            'data_publicacao' => now()->subMonths(7)->addDay(),
        ]);

        ClassificacaoService::classificar(
            $contrato,
            ClassificacaoSigilo::Reservado,
            'Informacao sensivel de seguranca publica',
            $this->admin,
            '127.0.0.1'
        );

        $contrato->refresh();
        $this->assertEquals(ClassificacaoSigilo::Reservado, $contrato->classificacao_sigilo);
        $this->assertNotNull($contrato->justificativa_sigilo);

        // 2. Desclassificar para publico
        ClassificacaoService::desclassificar($contrato, $this->admin, '127.0.0.1');

        $contrato->refresh();
        $this->assertEquals(ClassificacaoSigilo::Publico, $contrato->classificacao_sigilo);

        // 3. Publicar automaticamente
        $contrato->update(['publicado_portal' => false]);
        $resultado = PublicacaoAutomaticaService::publicar();

        $this->assertGreaterThanOrEqual(1, $resultado['publicados']);

        // 4. Verificar risco transparencia = 0
        $contrato->refresh();
        $risco = RiscoService::calcularExpandido($contrato);
        $this->assertEquals(0, $risco['categorias']['transparencia']['score']);
    }

    public function test_ciclo_solicitacao_criar_analisar_responder(): void
    {
        // 1. Criar solicitacao
        $solicitacao = $this->criarSolicitacaoLai();

        $this->assertNotNull($solicitacao->protocolo);
        $this->assertEquals(StatusSolicitacaoLai::Recebida, $solicitacao->status);
        $this->assertNotNull($solicitacao->prazo_legal);

        // 2. Analisar
        SolicitacaoLaiService::analisar($solicitacao, $this->admin, '127.0.0.1');
        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::EmAnalise, $solicitacao->status);

        // 3. Responder deferida
        SolicitacaoLaiService::responder(
            $solicitacao,
            'Segue a relacao completa de contratos vigentes conforme solicitado.',
            ClassificacaoRespostaLai::Deferida,
            $this->admin,
            '127.0.0.1'
        );

        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::Respondida, $solicitacao->status);
        $this->assertEquals(ClassificacaoRespostaLai::Deferida, $solicitacao->classificacao_resposta);
        $this->assertNotNull($solicitacao->data_resposta);

        // 4. Verificar historico (3 entradas: criacao, analise, resposta)
        $this->assertEquals(3, $solicitacao->historicos()->count());
    }

    public function test_ciclo_solicitacao_prorrogar_e_responder(): void
    {
        $solicitacao = $this->criarSolicitacaoLai();

        // 1. Prorrogar (+10 dias)
        SolicitacaoLaiService::prorrogar(
            $solicitacao,
            'Necessidade de consultar departamento juridico para levantamento completo dos dados.',
            $this->admin,
            '127.0.0.1'
        );

        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::Prorrogada, $solicitacao->status);
        $this->assertNotNull($solicitacao->prazo_estendido);

        // Prazo estendido = prazo_legal + 10 dias
        $expectedPrazo = $solicitacao->prazo_legal->copy()->addDays(10)->toDateString();
        $this->assertEquals($expectedPrazo, $solicitacao->prazo_estendido->toDateString());

        // 2. Nao permite segunda prorrogacao
        $this->assertFalse($solicitacao->is_prorrogavel);

        // 3. Responder apos prorrogacao
        SolicitacaoLaiService::responder(
            $solicitacao,
            'Apos analise detalhada, segue a documentacao solicitada em anexo.',
            ClassificacaoRespostaLai::Deferida,
            $this->admin,
            '127.0.0.1'
        );

        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::Respondida, $solicitacao->status);
    }

    public function test_ciclo_publicacao_automatica(): void
    {
        // 1. Criar contrato publico nao publicado
        $contrato = $this->criarContratoPublico([
            'publicado_portal' => false,
            'data_inicio' => now()->subMonths(8),
            'data_assinatura' => now()->subMonths(8)->subDays(5),
            'data_publicacao' => now()->subMonths(8)->addDay(),
        ]);

        $this->assertFalse($contrato->publicado_portal);

        // 2. Executar publicacao automatica
        $resultado = PublicacaoAutomaticaService::publicar();

        $this->assertGreaterThanOrEqual(1, $resultado['publicados']);

        // 3. Verificar que o contrato foi publicado
        $contrato->refresh();
        $this->assertTrue($contrato->publicado_portal);
    }

    // ============================================================
    // RELATORIO: Dados e PDF
    // ============================================================

    public function test_relatorio_lai_dados_corretos(): void
    {
        // Capturar baseline (outros testes podem ter criado contratos via withoutGlobalScope)
        $baseline = RelatorioService::dadosRelatorioLai();
        $baseTotal = $baseline['resumo']['total_contratos'];
        $basePublicos = $baseline['resumo']['contratos_publicos'];
        $basePublicados = $baseline['resumo']['publicados_portal'];
        $baseNaoPublicados = $baseline['resumo']['nao_publicados'];

        // Criar contratos mistos
        $this->criarContratoPublico(['publicado_portal' => true]);
        $this->criarContratoPublico(['publicado_portal' => false]);
        $this->criarContratoPublico([
            'classificacao_sigilo' => ClassificacaoSigilo::Reservado->value,
            'justificativa_sigilo' => 'Seguranca nacional',
            'publicado_portal' => false,
        ]);

        $dados = RelatorioService::dadosRelatorioLai();

        $this->assertArrayHasKey('municipio', $dados);
        $this->assertArrayHasKey('data_geracao', $dados);
        $this->assertArrayHasKey('resumo', $dados);
        $this->assertArrayHasKey('classificacao', $dados);
        $this->assertArrayHasKey('sic', $dados);

        // +3 contratos total, +2 publicos
        $this->assertEquals($baseTotal + 3, $dados['resumo']['total_contratos']);
        $this->assertEquals($basePublicos + 2, $dados['resumo']['contratos_publicos']);
        $this->assertEquals($basePublicados + 1, $dados['resumo']['publicados_portal']);
        $this->assertEquals($baseNaoPublicados + 1, $dados['resumo']['nao_publicados']);

        // 4 classificacoes (Publico, Reservado, Secreto, Ultrassecreto)
        $this->assertCount(4, $dados['classificacao']);
    }

    public function test_relatorio_lai_inclui_sic(): void
    {
        // Capturar baseline (outros testes podem ter criado solicitacoes)
        $baseline = RelatorioService::dadosRelatorioLai();
        $baseTotal = $baseline['sic']['total_solicitacoes'];
        $basePendentes = $baseline['sic']['pendentes'];
        $baseRespondidas = $baseline['sic']['respondidas'];

        // Criar 2 solicitacoes: 1 pendente + 1 respondida
        $this->criarSolicitacaoLai(['assunto' => 'Consulta contratos 1']);

        $s2 = $this->criarSolicitacaoLai(['assunto' => 'Consulta contratos 2']);
        SolicitacaoLaiService::responder(
            $s2,
            'Resposta completa sobre o assunto solicitado com todos os dados.',
            ClassificacaoRespostaLai::Deferida,
            $this->admin,
            '127.0.0.1'
        );

        $dados = RelatorioService::dadosRelatorioLai();

        $this->assertEquals($baseTotal + 2, $dados['sic']['total_solicitacoes']);
        $this->assertEquals($basePendentes + 1, $dados['sic']['pendentes']);
        $this->assertEquals($baseRespondidas + 1, $dados['sic']['respondidas']);
    }

    public function test_relatorio_lai_pdf_download(): void
    {
        $this->criarContratoPublico();

        $response = $this->get(route('tenant.relatorios.lai'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ============================================================
    // VIEWS: Sidebar e Relatorios Index
    // ============================================================

    public function test_sidebar_links_transparencia(): void
    {
        $response = $this->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Portal P');
        $response->assertSee('e-SIC');
        $response->assertSee('LAI');
    }

    public function test_relatorios_index_card_lai(): void
    {
        $response = $this->get(route('tenant.relatorios.index'));

        $response->assertStatus(200);
        $response->assertSee('Transparencia LAI');
        $response->assertSee('Lei 12.527/2011');
    }

    // ============================================================
    // SCORE: Integracao com calcularExpandido
    // ============================================================

    public function test_expandido_retorna_6_categorias(): void
    {
        $contrato = $this->criarContratoPublico();

        $risco = RiscoService::calcularExpandido($contrato);

        $this->assertCount(6, $risco['categorias']);
        $this->assertArrayHasKey('vencimento', $risco['categorias']);
        $this->assertArrayHasKey('financeiro', $risco['categorias']);
        $this->assertArrayHasKey('documental', $risco['categorias']);
        $this->assertArrayHasKey('juridico', $risco['categorias']);
        $this->assertArrayHasKey('operacional', $risco['categorias']);
        $this->assertArrayHasKey('transparencia', $risco['categorias']);
    }

    public function test_score_transparencia_impacta_total(): void
    {
        // Contrato com multiplos problemas de transparencia
        $contrato = $this->criarContratoPublico([
            'classificacao_sigilo' => ClassificacaoSigilo::Reservado->value,
            'justificativa_sigilo' => null,
            'publicado_portal' => false,
            'data_publicacao' => null,
            'veiculo_publicacao' => null,
        ]);

        $risco = RiscoService::calcularExpandido($contrato);
        $transparencia = $risco['categorias']['transparencia'];

        // Sigilo sem justificativa (+10) + Dados incompletos (+5) = 15pts
        // Nao soma "nao publicado no portal" pois classificacao != publico
        $this->assertEquals(15, $transparencia['score']);
        $this->assertCount(2, $transparencia['criterios']);

        // Verifica que contribui ao score total
        $this->assertGreaterThanOrEqual(15, $risco['score']);
    }
}
