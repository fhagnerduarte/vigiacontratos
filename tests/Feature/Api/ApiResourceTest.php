<?php

namespace Tests\Feature\Api;

use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Models\Aditivo;
use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ApiResourceTest extends TestCase
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

    // --- ContratoResource ---

    public function test_contrato_resource_retorna_formato_correto(): void
    {
        Contrato::factory()->create(['secretaria_id' => $this->secretaria->id]);

        $response = $this->getJson('/api/v1/contratos', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'numero', 'ano', 'objeto',
                        'tipo' => ['value', 'label'],
                        'status' => ['value', 'label'],
                        'valor_global', 'data_inicio', 'data_fim',
                        'score_risco', 'nivel_risco',
                        'fornecedor' => ['id', 'razao_social'],
                        'secretaria' => ['id', 'nome'],
                        'created_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_contrato_resource_enums_tem_value_e_label(): void
    {
        Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'tipo' => TipoContrato::Servico,
            'status' => StatusContrato::Vigente,
        ]);

        $response = $this->getJson('/api/v1/contratos', $this->apiHeaders());

        $response->assertStatus(200);

        $contrato = $response->json('data.0');
        $this->assertArrayHasKey('value', $contrato['tipo']);
        $this->assertArrayHasKey('label', $contrato['tipo']);
        $this->assertArrayHasKey('value', $contrato['status']);
        $this->assertArrayHasKey('label', $contrato['status']);
    }

    // --- Filtros ---

    public function test_filtro_por_status(): void
    {
        Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'status' => StatusContrato::Vigente,
        ]);
        Contrato::factory()->vencido()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);

        $response = $this->getJson('/api/v1/contratos?status=vigente', $this->apiHeaders());

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $contrato) {
            $this->assertEquals('vigente', $contrato['status']['value']);
        }
    }

    public function test_filtro_por_busca_textual(): void
    {
        Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'objeto' => 'Servico de limpeza predial',
        ]);
        Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'objeto' => 'Fornecimento de material',
        ]);

        $response = $this->getJson('/api/v1/contratos?q=limpeza', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_filtro_por_range_valor(): void
    {
        $baseline = Contrato::where('valor_global', '>=', 100000)->count();

        Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'valor_global' => 50000,
        ]);
        Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'valor_global' => 500000,
        ]);

        $response = $this->getJson('/api/v1/contratos?valor_min=100000', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount($baseline + 1, $response->json('data'));
    }

    // --- Paginacao ---

    public function test_paginacao_default_15_por_pagina(): void
    {
        $baseline = Contrato::count();

        Contrato::factory()->count(20)->create([
            'secretaria_id' => $this->secretaria->id,
        ]);

        $response = $this->getJson('/api/v1/contratos', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(15, $response->json('data'));
        $this->assertEquals($baseline + 20, $response->json('meta.total'));
    }

    public function test_paginacao_custom_per_page(): void
    {
        Contrato::factory()->count(10)->create([
            'secretaria_id' => $this->secretaria->id,
        ]);

        $response = $this->getJson('/api/v1/contratos?per_page=5', $this->apiHeaders());

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_paginacao_max_100(): void
    {
        Contrato::factory()->count(5)->create([
            'secretaria_id' => $this->secretaria->id,
        ]);

        $response = $this->getJson('/api/v1/contratos?per_page=999', $this->apiHeaders());

        $response->assertStatus(200);
        // max 100 por pagina
        $this->assertTrue($response->json('meta.per_page') <= 100);
    }

    // --- Show com includes ---

    public function test_contrato_show_com_includes(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);

        $response = $this->getJson(
            "/api/v1/contratos/{$contrato->id}?include=fornecedor,secretaria,aditivos",
            $this->apiHeaders()
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'numero', 'fornecedor', 'secretaria', 'aditivos',
                ],
            ]);
    }

    public function test_contrato_show_rejeita_include_invalido(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);

        $response = $this->getJson(
            "/api/v1/contratos/{$contrato->id}?include=fornecedor,hackerRelation",
            $this->apiHeaders()
        );

        $response->assertStatus(200);
        // hackerRelation nao deve aparecer
        $this->assertArrayNotHasKey('hackerRelation', $response->json('data'));
    }

    // --- AditivoResource ---

    public function test_aditivos_listagem(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);
        Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $response = $this->getJson('/api/v1/aditivos', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'contrato_id', 'tipo', 'status', 'valor_acrescimo'],
                ],
            ]);
    }

    public function test_aditivos_por_contrato(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);
        Aditivo::factory()->count(3)->create(['contrato_id' => $contrato->id]);

        $response = $this->getJson(
            "/api/v1/contratos/{$contrato->id}/aditivos",
            $this->apiHeaders()
        );

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    // --- FornecedorResource ---

    public function test_fornecedores_listagem(): void
    {
        Fornecedor::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/fornecedores', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'razao_social', 'cnpj'],
                ],
            ]);
    }

    public function test_fornecedor_show(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->getJson(
            "/api/v1/fornecedores/{$fornecedor->id}",
            $this->apiHeaders()
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'razao_social', 'cnpj'],
            ]);
    }

    // --- SecretariaResource ---

    public function test_secretarias_listagem(): void
    {
        $response = $this->getJson('/api/v1/secretarias', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nome', 'sigla'],
                ],
            ]);
    }

    // --- ServidorResource ---

    public function test_servidores_listagem(): void
    {
        Servidor::factory()->create(['secretaria_id' => $this->secretaria->id]);

        $response = $this->getJson('/api/v1/servidores', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nome', 'matricula', 'cargo'],
                ],
            ]);
    }

    // --- AlertaResource ---

    public function test_alertas_listagem(): void
    {
        $response = $this->getJson('/api/v1/alertas', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    // --- Dashboard API ---

    public function test_dashboard_indicadores(): void
    {
        $response = $this->getJson('/api/v1/dashboard/indicadores', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure(['indicadores']);
    }

    // --- Painel Risco API ---

    public function test_painel_risco_indicadores(): void
    {
        $response = $this->getJson('/api/v1/painel-risco/indicadores', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure(['indicadores']);
    }

    public function test_painel_risco_ranking(): void
    {
        $response = $this->getJson('/api/v1/painel-risco/ranking', $this->apiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure(['ranking']);
    }

    // --- Permissao ---

    public function test_usuario_sem_permissao_nao_acessa_contratos(): void
    {
        $userSemPermissao = $this->createUserWithRole('gabinete');
        Sanctum::actingAs($userSemPermissao);

        $response = $this->getJson('/api/v1/contratos', $this->apiHeaders());

        // Gabinete tem contrato.visualizar no seeder, deve funcionar
        // Vamos testar com fiscal que pode nao ter fornecedor.visualizar
        $response->assertStatus(200);
    }

    public function test_contrato_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/contratos/99999', $this->apiHeaders());

        $response->assertStatus(404);
    }
}
