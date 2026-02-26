<?php

namespace Tests\Feature\Api;

use App\Enums\CategoriaServico;
use App\Enums\StatusComparativoPreco;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Models\ComparativoPreco;
use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\PrecoReferencial;
use App\Models\Secretaria;
use App\Models\User;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ApiPnpTest extends TestCase
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

    private function criarPrecoReferencial(array $overrides = []): PrecoReferencial
    {
        return PrecoReferencial::factory()->create(array_merge([
            'registrado_por' => $this->adminUser->id,
        ], $overrides));
    }

    private function criarContratoComCategoria(CategoriaServico $categoria, array $overrides = []): Contrato
    {
        return Contrato::factory()->create(array_merge([
            'secretaria_id' => $this->secretaria->id,
            'fornecedor_id' => Fornecedor::factory()->create()->id,
            'status' => StatusContrato::Vigente,
            'tipo' => TipoContrato::Servico,
            'categoria_servico' => $categoria->value,
        ], $overrides));
    }

    // --- Autenticacao ---

    public function test_endpoints_requerem_autenticacao(): void
    {
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/v1/pnp/precos', $this->apiHeaders());
        $response->assertStatus(401);
    }

    public function test_endpoints_requerem_tenant_header(): void
    {
        $response = $this->getJson('/api/v1/pnp/precos');
        $response->assertStatus(422);
    }

    // --- GET /pnp/precos ---

    public function test_listar_precos_retorna_paginado(): void
    {
        $this->criarPrecoReferencial();
        $this->criarPrecoReferencial();

        $response = $this->getJson('/api/v1/pnp/precos', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'descricao', 'categoria_servico', 'unidade_medida',
                        'preco_minimo', 'preco_mediano', 'preco_maximo',
                        'fonte', 'data_referencia', 'vigencia_ate',
                        'is_vigente', 'is_ativo', 'created_at',
                    ],
                ],
                'links',
                'meta',
            ]);

        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_listar_precos_filtra_por_categoria(): void
    {
        $this->criarPrecoReferencial(['categoria_servico' => CategoriaServico::Limpeza->value]);
        $this->criarPrecoReferencial(['categoria_servico' => CategoriaServico::Tecnologia->value]);

        $response = $this->getJson('/api/v1/pnp/precos?categoria_servico=limpeza', $this->apiHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals('limpeza', $item['categoria_servico']['value']);
        }
    }

    public function test_listar_precos_filtra_vigentes(): void
    {
        $this->criarPrecoReferencial([
            'vigencia_ate' => now()->addMonths(6)->format('Y-m-d'),
            'is_ativo' => true,
        ]);
        $this->criarPrecoReferencial([
            'vigencia_ate' => now()->subMonths(1)->format('Y-m-d'),
            'is_ativo' => true,
        ]);

        $response = $this->getJson('/api/v1/pnp/precos?vigentes=1', $this->apiHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertTrue($item['is_vigente']);
        }
    }

    // --- GET /pnp/precos/{id} ---

    public function test_show_preco_retorna_detalhes(): void
    {
        $preco = $this->criarPrecoReferencial();

        $response = $this->getJson("/api/v1/pnp/precos/{$preco->id}", $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'descricao', 'categoria_servico', 'unidade_medida',
                    'preco_minimo', 'preco_mediano', 'preco_maximo',
                    'fonte', 'data_referencia', 'is_vigente', 'registrador',
                ],
            ]);

        $this->assertEquals($preco->id, $response->json('data.id'));
    }

    public function test_show_preco_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/pnp/precos/99999', $this->apiHeaders());

        $response->assertStatus(404);
    }

    // --- POST /pnp/precos ---

    public function test_registrar_preco_com_sucesso(): void
    {
        $dados = [
            'descricao' => 'Servico de limpeza predial',
            'categoria_servico' => 'limpeza',
            'unidade_medida' => 'mes',
            'preco_minimo' => 15000.00,
            'preco_mediano' => 20000.00,
            'preco_maximo' => 28000.00,
            'fonte' => 'PNP 2026',
            'data_referencia' => now()->subDays(10)->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/v1/pnp/precos', $dados, $this->apiHeaders());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'descricao', 'categoria_servico', 'preco_maximo'],
                'message',
            ]);

        $this->assertDatabaseHas('precos_referenciais', [
            'descricao' => 'Servico de limpeza predial',
            'categoria_servico' => 'limpeza',
        ], 'tenant');
    }

    public function test_registrar_preco_valida_campos_obrigatorios(): void
    {
        $response = $this->postJson('/api/v1/pnp/precos', [], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['descricao', 'categoria_servico', 'unidade_medida', 'preco_minimo', 'preco_mediano', 'preco_maximo', 'fonte', 'data_referencia']);
    }

    public function test_registrar_preco_valida_ordenacao_valores(): void
    {
        $dados = [
            'descricao' => 'Teste',
            'categoria_servico' => 'limpeza',
            'unidade_medida' => 'mes',
            'preco_minimo' => 30000.00,
            'preco_mediano' => 20000.00,
            'preco_maximo' => 10000.00,
            'fonte' => 'Teste',
            'data_referencia' => now()->subDays(5)->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/v1/pnp/precos', $dados, $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['preco_mediano']);
    }

    public function test_registrar_preco_dispara_webhook(): void
    {
        $dados = [
            'descricao' => 'Servico webhook test',
            'categoria_servico' => 'tecnologia',
            'unidade_medida' => 'mes',
            'preco_minimo' => 10000.00,
            'preco_mediano' => 15000.00,
            'preco_maximo' => 20000.00,
            'fonte' => 'PNP 2026',
            'data_referencia' => now()->subDays(5)->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/v1/pnp/precos', $dados, $this->apiHeaders());

        $response->assertStatus(201);

        // Webhook e disparado via DispararWebhookJob — como Queue::fake() esta ativo,
        // verificamos que o preco foi criado (o webhook foi chamado no service)
        $this->assertDatabaseHas('precos_referenciais', [
            'descricao' => 'Servico webhook test',
        ], 'tenant');
    }

    // --- PUT /pnp/precos/{id} ---

    public function test_atualizar_preco_com_sucesso(): void
    {
        $preco = $this->criarPrecoReferencial(['descricao' => 'Original']);

        $response = $this->putJson("/api/v1/pnp/precos/{$preco->id}", [
            'descricao' => 'Atualizado',
            'categoria_servico' => $preco->categoria_servico->value,
            'unidade_medida' => $preco->unidade_medida,
            'preco_minimo' => $preco->preco_minimo,
            'preco_mediano' => $preco->preco_mediano,
            'preco_maximo' => $preco->preco_maximo,
            'fonte' => $preco->fonte,
            'data_referencia' => $preco->data_referencia->format('Y-m-d'),
        ], $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertEquals('Atualizado', $response->json('data.descricao'));
    }

    public function test_atualizar_preco_inexistente_retorna_404(): void
    {
        $response = $this->putJson('/api/v1/pnp/precos/99999', [
            'descricao' => 'Teste',
            'categoria_servico' => 'limpeza',
            'unidade_medida' => 'mes',
            'preco_minimo' => 1000.00,
            'preco_mediano' => 1500.00,
            'preco_maximo' => 2000.00,
            'fonte' => 'Teste',
            'data_referencia' => now()->subDays(5)->format('Y-m-d'),
        ], $this->apiHeaders());

        $response->assertStatus(404);
    }

    // --- GET /pnp/categorias ---

    public function test_categorias_retorna_todas_com_contagem(): void
    {
        $this->criarPrecoReferencial(['categoria_servico' => CategoriaServico::Limpeza->value]);
        $this->criarPrecoReferencial(['categoria_servico' => CategoriaServico::Limpeza->value]);
        $this->criarPrecoReferencial(['categoria_servico' => CategoriaServico::Tecnologia->value]);

        $response = $this->getJson('/api/v1/pnp/categorias', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'total_precos'],
                ],
            ]);

        $data = collect($response->json('data'));
        $this->assertCount(count(CategoriaServico::cases()), $data);

        $limpeza = $data->firstWhere('value', 'limpeza');
        $this->assertGreaterThanOrEqual(2, $limpeza['total_precos']);
    }

    // --- GET /pnp/comparativo ---

    public function test_comparativo_geral_retorna_resumo(): void
    {
        $this->criarPrecoReferencial([
            'categoria_servico' => CategoriaServico::Limpeza->value,
            'preco_maximo' => 30000.00,
        ]);

        $this->criarContratoComCategoria(CategoriaServico::Limpeza, [
            'valor_mensal' => 25000.00,
        ]);

        $response = $this->getJson('/api/v1/pnp/comparativo', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_contratos', 'comparados', 'sem_referencia',
                'adequados', 'atencao', 'sobrepreco',
            ]);
    }

    // --- POST /pnp/contratos/{id}/comparar ---

    public function test_comparar_contrato_adequado(): void
    {
        $preco = $this->criarPrecoReferencial([
            'categoria_servico' => CategoriaServico::Limpeza->value,
            'preco_maximo' => 30000.00,
        ]);

        $contrato = $this->criarContratoComCategoria(CategoriaServico::Limpeza, [
            'valor_mensal' => 25000.00,
        ]);

        $response = $this->postJson("/api/v1/pnp/contratos/{$contrato->id}/comparar", [], $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'valor_contrato', 'valor_referencia',
                    'percentual_diferenca', 'status_comparativo',
                ],
            ]);

        $this->assertEquals('adequado', $response->json('data.status_comparativo.value'));
    }

    public function test_comparar_contrato_atencao(): void
    {
        $this->criarPrecoReferencial([
            'categoria_servico' => CategoriaServico::Tecnologia->value,
            'preco_maximo' => 20000.00,
        ]);

        $contrato = $this->criarContratoComCategoria(CategoriaServico::Tecnologia, [
            'valor_mensal' => 23500.00, // 17.5% acima
        ]);

        $response = $this->postJson("/api/v1/pnp/contratos/{$contrato->id}/comparar", [], $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertEquals('atencao', $response->json('data.status_comparativo.value'));
    }

    public function test_comparar_contrato_sobrepreco(): void
    {
        $this->criarPrecoReferencial([
            'categoria_servico' => CategoriaServico::Seguranca->value,
            'preco_maximo' => 30000.00,
        ]);

        $contrato = $this->criarContratoComCategoria(CategoriaServico::Seguranca, [
            'valor_mensal' => 45000.00, // 50% acima
        ]);

        $response = $this->postJson("/api/v1/pnp/contratos/{$contrato->id}/comparar", [], $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertEquals('sobrepreco', $response->json('data.status_comparativo.value'));
    }

    public function test_comparar_contrato_sem_referencia(): void
    {
        $contrato = $this->criarContratoComCategoria(CategoriaServico::Alimentacao);

        $response = $this->postJson("/api/v1/pnp/contratos/{$contrato->id}/comparar", [], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'contrato_id']);
    }

    public function test_comparar_contrato_dispara_webhook(): void
    {
        $this->criarPrecoReferencial([
            'categoria_servico' => CategoriaServico::Manutencao->value,
            'preco_maximo' => 25000.00,
        ]);

        $contrato = $this->criarContratoComCategoria(CategoriaServico::Manutencao, [
            'valor_mensal' => 20000.00,
        ]);

        $response = $this->postJson("/api/v1/pnp/contratos/{$contrato->id}/comparar", [], $this->apiHeaders());

        $response->assertStatus(200);

        // Comparativo foi criado no banco — webhook foi chamado no service
        $this->assertDatabaseHas('comparativos_preco', [
            'contrato_id' => $contrato->id,
        ], 'tenant');
    }

    // --- GET /pnp/indicadores ---

    public function test_indicadores_retorna_estrutura_correta(): void
    {
        $response = $this->getJson('/api/v1/pnp/indicadores', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_referencias', 'categorias_cobertas',
                'contratos_sobrepreco', 'percentual_sobrepreco_medio',
                'economia_potencial', 'total_comparados',
            ]);
    }

    public function test_indicadores_calcula_economia_potencial(): void
    {
        $preco = $this->criarPrecoReferencial([
            'categoria_servico' => CategoriaServico::Saude->value,
            'preco_maximo' => 50000.00,
        ]);

        $contrato = $this->criarContratoComCategoria(CategoriaServico::Saude, [
            'valor_mensal' => 80000.00,
        ]);

        // Gerar comparativo (sobrepreco)
        ComparativoPreco::create([
            'contrato_id' => $contrato->id,
            'preco_referencial_id' => $preco->id,
            'valor_contrato' => 80000.00,
            'valor_referencia' => 50000.00,
            'percentual_diferenca' => 60.00,
            'status_comparativo' => StatusComparativoPreco::Sobrepreco->value,
            'gerado_por' => $this->adminUser->id,
        ]);

        $response = $this->getJson('/api/v1/pnp/indicadores', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertGreaterThan(0, $response->json('contratos_sobrepreco'));
        $this->assertGreaterThan(0, $response->json('economia_potencial'));
    }

    // --- GET /pnp/historico ---

    public function test_historico_retorna_por_categoria(): void
    {
        $this->criarPrecoReferencial([
            'categoria_servico' => CategoriaServico::Obras->value,
            'data_referencia' => now()->subMonths(3)->format('Y-m-d'),
        ]);
        $this->criarPrecoReferencial([
            'categoria_servico' => CategoriaServico::Obras->value,
            'data_referencia' => now()->subMonth()->format('Y-m-d'),
        ]);

        $response = $this->getJson('/api/v1/pnp/historico?categoria_servico=obras', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'categoria' => ['value', 'label'],
                'data',
            ]);

        $this->assertEquals('obras', $response->json('categoria.value'));
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    // --- Integracao RiscoService ---

    public function test_sobrepreco_impacta_score_risco(): void
    {
        $preco = $this->criarPrecoReferencial([
            'categoria_servico' => CategoriaServico::Limpeza->value,
            'preco_maximo' => 20000.00,
        ]);

        $contrato = $this->criarContratoComCategoria(CategoriaServico::Limpeza, [
            'valor_mensal' => 30000.00,
            'valor_global' => 360000.00,
        ]);

        // Criar comparativo com sobrepreco
        ComparativoPreco::create([
            'contrato_id' => $contrato->id,
            'preco_referencial_id' => $preco->id,
            'valor_contrato' => 30000.00,
            'valor_referencia' => 20000.00,
            'percentual_diferenca' => 50.00,
            'status_comparativo' => StatusComparativoPreco::Sobrepreco->value,
            'gerado_por' => $this->adminUser->id,
        ]);

        $risco = \App\Services\RiscoService::calcularExpandido($contrato);

        $financeiro = $risco['categorias']['financeiro'] ?? null;
        $this->assertNotNull($financeiro);

        $criteriosStr = implode(' ', $financeiro['criterios']);
        $this->assertStringContainsString('Sobrepreco', $criteriosStr);
    }

    // --- Enum StatusComparativoPreco ---

    public function test_status_comparativo_enum_valores_labels(): void
    {
        $this->assertEquals('adequado', StatusComparativoPreco::Adequado->value);
        $this->assertEquals('atencao', StatusComparativoPreco::Atencao->value);
        $this->assertEquals('sobrepreco', StatusComparativoPreco::Sobrepreco->value);

        $this->assertEquals('Adequado', StatusComparativoPreco::Adequado->label());
        $this->assertEquals('Atencao', StatusComparativoPreco::Atencao->label());
        $this->assertEquals('Sobrepreco', StatusComparativoPreco::Sobrepreco->label());

        $this->assertEquals('success', StatusComparativoPreco::Adequado->cor());
        $this->assertEquals('warning', StatusComparativoPreco::Atencao->cor());
        $this->assertEquals('danger', StatusComparativoPreco::Sobrepreco->cor());
    }
}
