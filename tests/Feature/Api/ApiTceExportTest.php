<?php

namespace Tests\Feature\Api;

use App\Enums\FormatoExportacaoTce;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Models\Contrato;
use App\Models\ExportacaoTce;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ApiTceExportTest extends TestCase
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
    }

    private function apiHeaders(): array
    {
        return ['X-Tenant-Slug' => 'testing'];
    }

    private function criarContratoCompleto(array $overrides = []): Contrato
    {
        $fornecedor = Fornecedor::factory()->create();
        $servidor = Servidor::factory()->create();

        $contrato = Contrato::factory()->create(array_merge([
            'secretaria_id' => $this->secretaria->id,
            'fornecedor_id' => $fornecedor->id,
            'status' => StatusContrato::Vigente,
            'tipo' => TipoContrato::Servico,
            'numero_processo' => '2026/001',
            'data_publicacao' => now()->subDays(30),
            'modalidade_contratacao' => 'pregao_eletronico',
        ], $overrides));

        // Designar fiscal titular
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

    private function criarContratoIncompleto(array $overrides = []): Contrato
    {
        return Contrato::factory()->create(array_merge([
            'secretaria_id' => $this->secretaria->id,
            'status' => StatusContrato::Vigente,
            'numero_processo' => '',
            'data_publicacao' => null,
            'fundamento_legal' => null,
        ], $overrides));
    }

    // --- GET /tce/dados ---

    public function test_dados_tce_retorna_contratos_com_risco(): void
    {
        $this->criarContratoCompleto();

        $response = $this->getJson('/api/v1/tce/dados', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'municipio',
                'data_geracao',
                'resumo' => ['total_monitorados', 'alto_risco', 'medio_risco', 'baixo_risco'],
                'total_pendencias',
                'contratos' => [
                    '*' => [
                        'numero', 'objeto', 'fornecedor', 'cnpj_fornecedor',
                        'secretaria', 'valor_global', 'data_inicio', 'data_fim',
                        'score', 'nivel', 'categorias',
                        'modalidade', 'numero_processo', 'fiscal_titular',
                        'qtd_aditivos', 'percentual_executado', 'status',
                    ],
                ],
            ]);
    }

    public function test_dados_tce_filtra_por_status(): void
    {
        $this->criarContratoCompleto(['status' => StatusContrato::Vigente]);
        $this->criarContratoCompleto(['status' => StatusContrato::Encerrado]);

        $response = $this->getJson('/api/v1/tce/dados?status=encerrado', $this->apiHeaders());

        $response->assertStatus(200);
        $contratos = $response->json('contratos');

        foreach ($contratos as $contrato) {
            $this->assertEquals('Encerrado', $contrato['status']);
        }
    }

    public function test_dados_tce_filtra_por_secretaria(): void
    {
        $outraSecretaria = Secretaria::factory()->create();
        $this->criarContratoCompleto(['secretaria_id' => $this->secretaria->id]);
        $this->criarContratoCompleto(['secretaria_id' => $outraSecretaria->id]);

        $response = $this->getJson(
            '/api/v1/tce/dados?secretaria_id=' . $outraSecretaria->id,
            $this->apiHeaders()
        );

        $response->assertStatus(200);
        $contratos = $response->json('contratos');

        foreach ($contratos as $contrato) {
            $this->assertEquals($outraSecretaria->nome, $contrato['secretaria']);
        }
    }

    public function test_dados_tce_filtra_por_nivel_risco(): void
    {
        $this->criarContratoCompleto(['nivel_risco' => 'alto', 'score_risco' => 75]);
        $this->criarContratoCompleto(['nivel_risco' => 'baixo', 'score_risco' => 10]);

        $response = $this->getJson('/api/v1/tce/dados?nivel_risco=alto', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('contratos')));
    }

    public function test_dados_tce_bypassa_secretaria_scope(): void
    {
        $outraSecretaria = Secretaria::factory()->create();
        $this->criarContratoCompleto(['secretaria_id' => $this->secretaria->id]);
        $this->criarContratoCompleto(['secretaria_id' => $outraSecretaria->id]);

        // Mesmo sem vinculo com outra secretaria, TCE deve retornar todos
        $response = $this->getJson('/api/v1/tce/dados', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, $response->json('resumo.total_monitorados'));
    }

    public function test_dados_tce_inclui_campos_extras_tce(): void
    {
        $this->criarContratoCompleto([
            'numero_processo' => '2026/TEST-001',
            'fundamento_legal' => 'Lei 14.133/2021',
            'data_publicacao' => '2026-01-15',
        ]);

        $response = $this->getJson('/api/v1/tce/dados', $this->apiHeaders());

        $response->assertStatus(200);
        $contrato = $response->json('contratos.0');

        $this->assertArrayHasKey('modalidade', $contrato);
        $this->assertArrayHasKey('numero_processo', $contrato);
        $this->assertArrayHasKey('fundamento_legal', $contrato);
        $this->assertArrayHasKey('data_publicacao', $contrato);
        $this->assertArrayHasKey('fiscal_titular', $contrato);
        $this->assertArrayHasKey('qtd_aditivos', $contrato);
        $this->assertArrayHasKey('valor_empenhado', $contrato);
        $this->assertArrayHasKey('saldo_contratual', $contrato);
    }

    // --- GET /tce/validar ---

    public function test_validar_retorna_pendencias_contratos_incompletos(): void
    {
        $this->criarContratoIncompleto();

        $response = $this->getJson('/api/v1/tce/validar', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_contratos',
                'total_pendencias',
                'pendencias' => [
                    '*' => ['numero', 'objeto', 'campos_faltantes', 'total_faltantes', 'completo'],
                ],
            ]);

        $this->assertGreaterThan(0, $response->json('total_pendencias'));
        $pendencia = collect($response->json('pendencias'))->first(fn ($p) => ! $p['completo']);
        $this->assertNotEmpty($pendencia['campos_faltantes']);
    }

    public function test_validar_contrato_completo_sem_pendencias(): void
    {
        $this->criarContratoCompleto();

        $response = $this->getJson('/api/v1/tce/validar', $this->apiHeaders());

        $response->assertStatus(200);

        $pendencias = $response->json('pendencias');
        $completos = collect($pendencias)->filter(fn ($p) => $p['completo']);
        $this->assertGreaterThanOrEqual(1, $completos->count());
    }

    // --- POST /tce/exportar ---

    public function test_exportar_xml_gera_arquivo_valido(): void
    {
        $this->criarContratoCompleto();

        $response = $this->postJson('/api/v1/tce/exportar', [
            'formato' => 'xml',
        ], $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'exportacao' => [
                    'id', 'formato', 'total_contratos', 'total_pendencias',
                    'arquivo_nome', 'gerado_por', 'created_at',
                ],
                'conteudo',
                'content_type',
            ]);

        $this->assertEquals('application/xml', $response->json('content_type'));
        $this->assertNotEmpty($response->json('conteudo'));
    }

    public function test_exportar_csv_registra_exportacao(): void
    {
        $this->criarContratoCompleto();

        $response = $this->postJson('/api/v1/tce/exportar', [
            'formato' => 'csv',
        ], $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'exportacao' => ['id', 'formato', 'total_contratos'],
                'message',
            ]);
    }

    public function test_exportar_excel_registra_exportacao(): void
    {
        $this->criarContratoCompleto();

        $response = $this->postJson('/api/v1/tce/exportar', [
            'formato' => 'excel',
        ], $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'exportacao' => ['id', 'formato', 'total_contratos'],
            ]);
    }

    public function test_exportar_registra_historico(): void
    {
        $this->criarContratoCompleto();
        $countAntes = ExportacaoTce::count();

        $this->postJson('/api/v1/tce/exportar', [
            'formato' => 'xml',
        ], $this->apiHeaders());

        $this->assertEquals($countAntes + 1, ExportacaoTce::count());

        $exportacao = ExportacaoTce::latest()->first();
        $this->assertEquals('xml', $exportacao->formato->value);
        $this->assertEquals($this->adminUser->id, $exportacao->gerado_por);
        $this->assertGreaterThan(0, $exportacao->total_contratos);
        $this->assertStringContains('relatorio-tce', $exportacao->arquivo_nome);
    }

    public function test_exportar_com_filtros(): void
    {
        $this->criarContratoCompleto(['nivel_risco' => 'alto', 'score_risco' => 80]);

        $response = $this->postJson('/api/v1/tce/exportar', [
            'formato' => 'xml',
            'filtros' => ['nivel_risco' => 'alto'],
        ], $this->apiHeaders());

        $response->assertStatus(200);
        $exportacao = ExportacaoTce::latest()->first();
        $this->assertEquals(['nivel_risco' => 'alto'], $exportacao->filtros);
    }

    public function test_exportar_formato_invalido_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/tce/exportar', [
            'formato' => 'invalido',
        ], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['formato']);
    }

    public function test_exportar_sem_formato_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/tce/exportar', [], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['formato']);
    }

    public function test_xml_contem_estrutura_leiaute_tce(): void
    {
        $this->criarContratoCompleto();

        $response = $this->postJson('/api/v1/tce/exportar', [
            'formato' => 'xml',
        ], $this->apiHeaders());

        $xmlString = $response->json('conteudo');
        $xml = simplexml_load_string($xmlString);

        $this->assertNotFalse($xml, 'XML deve ser valido');
        $this->assertEquals('RelatorioTCE', $xml->getName());
        $this->assertEquals('1.0', (string) $xml['versao']);
        $this->assertNotNull($xml->Cabecalho);
        $this->assertNotNull($xml->Cabecalho->Municipio);
        $this->assertNotNull($xml->Cabecalho->DataGeracao);
        $this->assertNotNull($xml->Cabecalho->TotalContratos);
        $this->assertNotNull($xml->Resumo);
        $this->assertNotNull($xml->Resumo->AltoRisco);
        $this->assertNotNull($xml->Contratos);
        $this->assertGreaterThanOrEqual(1, count($xml->Contratos->Contrato));

        $primeiroContrato = $xml->Contratos->Contrato[0];
        $this->assertNotEmpty((string) $primeiroContrato->Numero);
        $this->assertNotEmpty((string) $primeiroContrato->Objeto);
        $this->assertNotEmpty((string) $primeiroContrato->ValorGlobal);
        $this->assertNotEmpty((string) $primeiroContrato->ScoreRisco);
        $this->assertNotEmpty((string) $primeiroContrato->NivelRisco);
    }

    // --- GET /tce/historico ---

    public function test_historico_lista_exportacoes(): void
    {
        ExportacaoTce::create([
            'formato' => 'xml',
            'total_contratos' => 10,
            'total_pendencias' => 2,
            'arquivo_nome' => 'test.xml',
            'gerado_por' => $this->adminUser->id,
        ]);

        $response = $this->getJson('/api/v1/tce/historico', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'formato', 'total_contratos', 'total_pendencias',
                        'arquivo_nome', 'gerado_por', 'created_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_historico_paginado(): void
    {
        $baseline = ExportacaoTce::count();

        for ($i = 0; $i < 20; $i++) {
            ExportacaoTce::create([
                'formato' => 'csv',
                'total_contratos' => $i + 1,
                'total_pendencias' => 0,
                'arquivo_nome' => "test-{$i}.csv",
                'gerado_por' => $this->adminUser->id,
            ]);
        }

        $response = $this->getJson('/api/v1/tce/historico?per_page=5', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
        $this->assertEquals($baseline + 20, $response->json('meta.total'));
    }

    public function test_historico_ordenado_por_data_desc(): void
    {
        // Limpar registros anteriores para teste isolado
        ExportacaoTce::query()->delete();

        $antigo = new ExportacaoTce();
        $antigo->formato = 'xml';
        $antigo->total_contratos = 5;
        $antigo->total_pendencias = 0;
        $antigo->arquivo_nome = 'antigo.xml';
        $antigo->gerado_por = $this->adminUser->id;
        $antigo->created_at = now()->subDays(10);
        $antigo->updated_at = now()->subDays(10);
        $antigo->save();

        $recente = new ExportacaoTce();
        $recente->formato = 'csv';
        $recente->total_contratos = 8;
        $recente->total_pendencias = 1;
        $recente->arquivo_nome = 'recente.csv';
        $recente->gerado_por = $this->adminUser->id;
        $recente->created_at = now()->addMinute();
        $recente->updated_at = now()->addMinute();
        $recente->save();

        $response = $this->getJson('/api/v1/tce/historico', $this->apiHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('recente.csv', $data[0]['arquivo_nome']);
        $this->assertEquals('antigo.xml', $data[1]['arquivo_nome']);
    }

    // --- Autenticacao e Permissoes ---

    public function test_dados_tce_requer_autenticacao(): void
    {
        // Criar request sem Sanctum actingAs
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/v1/tce/dados', $this->apiHeaders());

        $response->assertStatus(401);
    }

    public function test_exportar_requer_autenticacao(): void
    {
        $this->app['auth']->forgetGuards();

        $response = $this->postJson('/api/v1/tce/exportar', [
            'formato' => 'xml',
        ], $this->apiHeaders());

        $response->assertStatus(401);
    }

    // --- Enum FormatoExportacaoTce ---

    public function test_formato_exportacao_enum_valores(): void
    {
        $this->assertEquals('xml', FormatoExportacaoTce::Xml->value);
        $this->assertEquals('csv', FormatoExportacaoTce::Csv->value);
        $this->assertEquals('excel', FormatoExportacaoTce::Excel->value);
        $this->assertEquals('pdf', FormatoExportacaoTce::Pdf->value);
    }

    public function test_formato_exportacao_labels(): void
    {
        $this->assertEquals('XML', FormatoExportacaoTce::Xml->label());
        $this->assertEquals('CSV', FormatoExportacaoTce::Csv->label());
        $this->assertEquals('Excel', FormatoExportacaoTce::Excel->label());
        $this->assertEquals('PDF', FormatoExportacaoTce::Pdf->label());
    }

    public function test_formato_exportacao_extensoes(): void
    {
        $this->assertEquals('.xml', FormatoExportacaoTce::Xml->extensao());
        $this->assertEquals('.csv', FormatoExportacaoTce::Csv->extensao());
        $this->assertEquals('.xlsx', FormatoExportacaoTce::Excel->extensao());
        $this->assertEquals('.pdf', FormatoExportacaoTce::Pdf->extensao());
    }

    public function test_formato_exportacao_content_types(): void
    {
        $this->assertEquals('application/xml', FormatoExportacaoTce::Xml->contentType());
        $this->assertEquals('text/csv; charset=UTF-8', FormatoExportacaoTce::Csv->contentType());
        $this->assertStringContains('spreadsheetml', FormatoExportacaoTce::Excel->contentType());
        $this->assertEquals('application/pdf', FormatoExportacaoTce::Pdf->contentType());
    }

    // --- Helper ---

    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '{$haystack}' contains '{$needle}'."
        );
    }
}
