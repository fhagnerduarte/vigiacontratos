<?php

namespace Tests\Feature\Api;

use App\Enums\ClassificacaoSigilo;
use App\Enums\DatasetDadosAbertos;
use App\Enums\FormatoDadosAbertos;
use App\Enums\ModalidadeContratacao;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Models\Contrato;
use App\Models\ExportacaoDadosAbertos;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ApiDadosAbertosTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    protected User $adminUser;
    protected Secretaria $secretaria;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        $this->setUpTenant();

        $this->secretaria = Secretaria::factory()->create();
        $this->adminUser = $this->createAdminUser();
        $this->adminUser->secretarias()->attach($this->secretaria->id);

        Sanctum::actingAs($this->adminUser);
        Queue::fake();
    }

    private function apiHeaders(): array
    {
        return ['X-Tenant-Slug' => 'testing'];
    }

    private function criarContratoPublico(array $overrides = []): Contrato
    {
        $fornecedor = Fornecedor::factory()->create();
        $servidor = Servidor::factory()->create();

        $contrato = Contrato::factory()->create(array_merge([
            'secretaria_id' => $this->secretaria->id,
            'fornecedor_id' => $fornecedor->id,
            'status' => StatusContrato::Vigente,
            'tipo' => TipoContrato::Servico,
            'numero_processo' => fake()->numerify('#####/####'),
            'data_publicacao' => now()->subDays(30),
            'data_assinatura' => now()->subDays(35),
            'modalidade_contratacao' => ModalidadeContratacao::PregaoEletronico,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico,
            'publicado_portal' => true,
            'fundamento_legal' => 'Lei 14.133/2021',
        ], $overrides));

        $contrato->fiscais()->create([
            'servidor_id' => $servidor->id,
            'nome' => $servidor->nome,
            'matricula' => 'MAT-' . fake()->numerify('######'),
            'cargo' => 'Fiscal de Contrato',
            'data_inicio' => now()->subDays(10),
            'tipo_fiscal' => 'titular',
            'is_atual' => true,
        ]);

        return $contrato;
    }

    // ========== Auth ==========

    public function test_endpoints_requerem_autenticacao(): void
    {
        // Desautenticar
        app('auth')->forgetGuards();

        $endpoints = [
            ['GET', '/api/v1/dados-abertos/catalogo'],
            ['GET', '/api/v1/dados-abertos/contratos'],
            ['GET', '/api/v1/dados-abertos/fornecedores'],
            ['GET', '/api/v1/dados-abertos/licitacoes'],
            ['POST', '/api/v1/dados-abertos/exportar'],
            ['GET', '/api/v1/dados-abertos/estatisticas'],
            ['GET', '/api/v1/dados-abertos/historico'],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url, [], $this->apiHeaders());
            $response->assertStatus(401, "Endpoint {$method} {$url} deveria retornar 401");
        }
    }

    public function test_endpoints_requerem_tenant_header(): void
    {
        $response = $this->getJson('/api/v1/dados-abertos/catalogo');
        $response->assertStatus(422);
    }

    // ========== Catalogo ==========

    public function test_catalogo_retorna_3_datasets(): void
    {
        $response = $this->getJson('/api/v1/dados-abertos/catalogo', $this->apiHeaders());

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.dataset', 'contratos')
            ->assertJsonPath('data.1.dataset', 'fornecedores')
            ->assertJsonPath('data.2.dataset', 'licitacoes');
    }

    public function test_catalogo_inclui_campos_e_filtros(): void
    {
        $response = $this->getJson('/api/v1/dados-abertos/catalogo', $this->apiHeaders());

        $response->assertOk();

        $data = $response->json('data');

        foreach ($data as $dataset) {
            $this->assertArrayHasKey('dataset', $dataset);
            $this->assertArrayHasKey('nome', $dataset);
            $this->assertArrayHasKey('descricao', $dataset);
            $this->assertArrayHasKey('campos', $dataset);
            $this->assertArrayHasKey('filtros_disponiveis', $dataset);
            $this->assertArrayHasKey('formatos', $dataset);
            $this->assertArrayHasKey('total_registros', $dataset);
            $this->assertIsArray($dataset['campos']);
            $this->assertIsArray($dataset['filtros_disponiveis']);
            $this->assertEquals(['json', 'csv', 'xml'], $dataset['formatos']);
        }
    }

    // ========== Contratos ==========

    public function test_contratos_retorna_dados_paginados(): void
    {
        $this->criarContratoPublico();
        $this->criarContratoPublico();

        $response = $this->getJson('/api/v1/dados-abertos/contratos', $this->apiHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'per_page', 'current_page', 'last_page', 'gerado_em'],
            ]);

        $this->assertGreaterThanOrEqual(2, $response->json('meta.total'));
    }

    public function test_contratos_retorna_campos_expandidos(): void
    {
        $this->criarContratoPublico();

        $response = $this->getJson('/api/v1/dados-abertos/contratos', $this->apiHeaders());

        $response->assertOk();

        $contrato = $response->json('data.0');

        $camposExpandidos = [
            'numero', 'ano', 'objeto', 'tipo', 'status', 'modalidade',
            'fornecedor_razao_social', 'fornecedor_cnpj', 'secretaria',
            'valor_global', 'valor_mensal', 'data_inicio', 'data_fim',
            'fundamento_legal', 'regime_execucao', 'categoria_servico',
            'dotacao_orcamentaria', 'nivel_risco', 'score_risco',
            'percentual_executado', 'fiscal_titular',
            'qtd_aditivos', 'valor_total_aditivos',
        ];

        foreach ($camposExpandidos as $campo) {
            $this->assertArrayHasKey($campo, $contrato, "Campo '{$campo}' ausente nos dados expandidos");
        }
    }

    public function test_contratos_filtra_por_status(): void
    {
        $this->criarContratoPublico(['status' => StatusContrato::Vigente]);

        $response = $this->getJson('/api/v1/dados-abertos/contratos?status=vigente', $this->apiHeaders());

        $response->assertOk();

        $data = $response->json('data');
        foreach ($data as $contrato) {
            $this->assertEquals('vigente', $contrato['status']);
        }
    }

    public function test_contratos_filtra_por_ano(): void
    {
        $this->criarContratoPublico(['ano' => '2026']);

        $response = $this->getJson('/api/v1/dados-abertos/contratos?ano=2026', $this->apiHeaders());

        $response->assertOk();

        $data = $response->json('data');
        foreach ($data as $contrato) {
            $this->assertEquals('2026', $contrato['ano']);
        }
    }

    public function test_contratos_filtra_por_secretaria(): void
    {
        $this->criarContratoPublico();

        $response = $this->getJson(
            '/api/v1/dados-abertos/contratos?secretaria_id=' . $this->secretaria->id,
            $this->apiHeaders()
        );

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, $response->json('meta.total'));
    }

    public function test_contratos_filtra_por_modalidade(): void
    {
        $this->criarContratoPublico(['modalidade_contratacao' => ModalidadeContratacao::PregaoEletronico]);

        $response = $this->getJson(
            '/api/v1/dados-abertos/contratos?modalidade=pregao_eletronico',
            $this->apiHeaders()
        );

        $response->assertOk();

        $data = $response->json('data');
        foreach ($data as $contrato) {
            $this->assertEquals('pregao_eletronico', $contrato['modalidade']);
        }
    }

    public function test_contratos_apenas_publicos_visiveis(): void
    {
        // Contrato publico visivel
        $this->criarContratoPublico();

        // Contrato sigiloso (NAO visivel)
        Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'classificacao_sigilo' => ClassificacaoSigilo::Reservado,
            'publicado_portal' => false,
        ]);

        // Contrato publico mas nao publicado no portal
        Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico,
            'publicado_portal' => false,
        ]);

        $response = $this->getJson('/api/v1/dados-abertos/contratos', $this->apiHeaders());

        $response->assertOk();

        // Baseline: apenas contratos com classificacao_sigilo=publico E publicado_portal=true
        $totalVisivel = Contrato::withoutGlobalScopes()->visivelNoPortal()->count();
        $this->assertEquals($totalVisivel, $response->json('meta.total'));
    }

    // ========== Fornecedores ==========

    public function test_fornecedores_retorna_dados(): void
    {
        Fornecedor::factory()->create();

        $response = $this->getJson('/api/v1/dados-abertos/fornecedores', $this->apiHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'per_page', 'current_page', 'last_page'],
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('meta.total'));
    }

    public function test_fornecedores_inclui_totais(): void
    {
        $fornecedor = Fornecedor::factory()->create();
        $this->criarContratoPublico(['fornecedor_id' => $fornecedor->id]);

        $response = $this->getJson('/api/v1/dados-abertos/fornecedores', $this->apiHeaders());

        $response->assertOk();

        $data = $response->json('data');
        $found = collect($data)->firstWhere('cnpj', $fornecedor->cnpj);

        $this->assertNotNull($found);
        $this->assertArrayHasKey('total_contratos', $found);
        $this->assertArrayHasKey('valor_total_contratado', $found);
        $this->assertArrayHasKey('contratos_vigentes', $found);
        $this->assertGreaterThanOrEqual(1, $found['total_contratos']);
    }

    public function test_fornecedores_filtra_busca(): void
    {
        $fornecedor = Fornecedor::factory()->create(['razao_social' => 'Empresa Busca Teste LTDA']);

        $response = $this->getJson(
            '/api/v1/dados-abertos/fornecedores?busca=Busca Teste',
            $this->apiHeaders()
        );

        $response->assertOk();

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
        $this->assertStringContainsString('Busca Teste', $data[0]['razao_social']);
    }

    // ========== Licitacoes ==========

    public function test_licitacoes_retorna_dados(): void
    {
        $this->criarContratoPublico(['numero_processo' => '99999/2026']);

        $response = $this->getJson('/api/v1/dados-abertos/licitacoes', $this->apiHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'per_page', 'current_page', 'last_page'],
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('meta.total'));
    }

    public function test_licitacoes_filtra_por_modalidade(): void
    {
        $this->criarContratoPublico([
            'modalidade_contratacao' => ModalidadeContratacao::PregaoEletronico,
            'numero_processo' => '88888/2026',
        ]);

        $response = $this->getJson(
            '/api/v1/dados-abertos/licitacoes?modalidade=pregao_eletronico',
            $this->apiHeaders()
        );

        $response->assertOk();

        $data = $response->json('data');
        foreach ($data as $licitacao) {
            $this->assertEquals('pregao_eletronico', $licitacao['modalidade']);
        }
    }

    // ========== Exportar ==========

    public function test_exportar_json_contratos(): void
    {
        $this->criarContratoPublico();

        $response = $this->postJson('/api/v1/dados-abertos/exportar', [
            'dataset' => 'contratos',
            'formato' => 'json',
        ], $this->apiHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'metadata' => ['dataset', 'formato', 'total', 'gerado_em'],
                'dados',
            ])
            ->assertJsonPath('metadata.dataset', 'contratos')
            ->assertJsonPath('metadata.formato', 'json');
    }

    public function test_exportar_csv_contratos(): void
    {
        $this->criarContratoPublico();

        $response = $this->postJson('/api/v1/dados-abertos/exportar', [
            'dataset' => 'contratos',
            'formato' => 'csv',
        ], $this->apiHeaders());

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertNotEmpty($content);
    }

    public function test_exportar_xml_contratos(): void
    {
        $this->criarContratoPublico();

        $response = $this->postJson('/api/v1/dados-abertos/exportar', [
            'dataset' => 'contratos',
            'formato' => 'xml',
        ], $this->apiHeaders());

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml', $content);
        $this->assertStringContainsString('<DadosAbertos', $content);
        $this->assertStringContainsString('dataset="contratos"', $content);

        // Validar XML sintaticamente
        $xml = simplexml_load_string($content);
        $this->assertNotFalse($xml);
    }

    public function test_exportar_registra_historico(): void
    {
        $this->criarContratoPublico();

        $baseline = ExportacaoDadosAbertos::count();

        $this->postJson('/api/v1/dados-abertos/exportar', [
            'dataset' => 'contratos',
            'formato' => 'json',
        ], $this->apiHeaders());

        $this->assertEquals($baseline + 1, ExportacaoDadosAbertos::count());

        $exportacao = ExportacaoDadosAbertos::orderByDesc('id')->first();
        $this->assertEquals(DatasetDadosAbertos::Contratos, $exportacao->dataset);
        $this->assertEquals(FormatoDadosAbertos::Json, $exportacao->formato);
        $this->assertEquals($this->adminUser->id, $exportacao->solicitado_por);
        $this->assertGreaterThanOrEqual(0, $exportacao->total_registros);
    }

    public function test_exportar_formato_invalido(): void
    {
        $response = $this->postJson('/api/v1/dados-abertos/exportar', [
            'dataset' => 'contratos',
            'formato' => 'pdf',
        ], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['formato']);
    }

    public function test_exportar_dataset_invalido(): void
    {
        $response = $this->postJson('/api/v1/dados-abertos/exportar', [
            'dataset' => 'invalido',
            'formato' => 'json',
        ], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset']);
    }

    // ========== Estatisticas ==========

    public function test_estatisticas_retorna_contadores(): void
    {
        // Criar algumas exportacoes
        ExportacaoDadosAbertos::create([
            'dataset' => 'contratos',
            'formato' => 'json',
            'total_registros' => 10,
            'solicitado_por' => $this->adminUser->id,
            'ip_solicitante' => '127.0.0.1',
        ]);

        ExportacaoDadosAbertos::create([
            'dataset' => 'fornecedores',
            'formato' => 'csv',
            'total_registros' => 5,
            'solicitado_por' => $this->adminUser->id,
            'ip_solicitante' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/dados-abertos/estatisticas', $this->apiHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total_exportacoes',
                    'por_dataset',
                    'por_formato',
                    'ultima_exportacao',
                ],
            ]);

        $this->assertGreaterThanOrEqual(2, $response->json('data.total_exportacoes'));
    }

    // ========== Historico ==========

    public function test_historico_retorna_paginado(): void
    {
        ExportacaoDadosAbertos::create([
            'dataset' => 'contratos',
            'formato' => 'json',
            'total_registros' => 10,
            'solicitado_por' => $this->adminUser->id,
            'ip_solicitante' => '127.0.0.1',
        ]);

        $response = $this->getJson('/api/v1/dados-abertos/historico', $this->apiHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'dataset' => ['value', 'label'],
                        'formato' => ['value', 'label'],
                        'total_registros',
                        'created_at',
                    ],
                ],
            ]);
    }

    // ========== Enums ==========

    public function test_enums_dataset_formato_valores(): void
    {
        // DatasetDadosAbertos
        $this->assertCount(3, DatasetDadosAbertos::cases());
        $this->assertEquals('contratos', DatasetDadosAbertos::Contratos->value);
        $this->assertEquals('fornecedores', DatasetDadosAbertos::Fornecedores->value);
        $this->assertEquals('licitacoes', DatasetDadosAbertos::Licitacoes->value);

        foreach (DatasetDadosAbertos::cases() as $case) {
            $this->assertNotEmpty($case->label());
            $this->assertNotEmpty($case->descricao());
        }

        // FormatoDadosAbertos
        $this->assertCount(3, FormatoDadosAbertos::cases());
        $this->assertEquals('json', FormatoDadosAbertos::Json->value);
        $this->assertEquals('csv', FormatoDadosAbertos::Csv->value);
        $this->assertEquals('xml', FormatoDadosAbertos::Xml->value);

        foreach (FormatoDadosAbertos::cases() as $case) {
            $this->assertNotEmpty($case->label());
            $this->assertNotEmpty($case->contentType());
            $this->assertNotEmpty($case->extensao());
        }
    }
}
