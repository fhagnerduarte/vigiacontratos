<?php

namespace Tests\Feature\Api;

use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Fornecedor;
use App\Models\Ocorrencia;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ApiContratosWriteTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    protected User $adminUser;
    protected Secretaria $secretaria;
    protected Servidor $servidor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        $this->setUpTenant();

        $this->secretaria = Secretaria::factory()->create();
        $this->servidor = Servidor::factory()->create(['secretaria_id' => $this->secretaria->id]);
        $this->adminUser = $this->createAdminUser();
        $this->adminUser->secretarias()->attach($this->secretaria->id);

        Sanctum::actingAs($this->adminUser);
    }

    private function apiHeaders(): array
    {
        return ['X-Tenant-Slug' => 'testing'];
    }

    private function dadosContratoValidos(): array
    {
        $fornecedor = Fornecedor::factory()->create();

        return [
            'ano' => date('Y'),
            'objeto' => 'Servico de limpeza predial via API',
            'tipo' => TipoContrato::Servico->value,
            'modalidade_contratacao' => 'pregao_eletronico',
            'secretaria_id' => $this->secretaria->id,
            'fornecedor_id' => $fornecedor->id,
            'unidade_gestora' => 'Prefeitura Municipal',
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->addYear()->toDateString(),
            'valor_global' => 120000,
            'valor_mensal' => 10000,
            'tipo_pagamento' => 'mensal',
            'fonte_recurso' => 'Recursos Proprios',
            'dotacao_orcamentaria' => '01.02.03.456.7890.1.234.56',
            'numero_empenho' => '1234/2026',
            'numero_processo' => '56789/2026',
            'fiscal_servidor_id' => $this->servidor->id,
            'portaria_designacao' => 'Port. 123/2026',
        ];
    }

    // --- Contrato CRUD ---

    public function test_criar_contrato_via_api(): void
    {
        $response = $this->postJson('/api/v1/contratos', $this->dadosContratoValidos(), $this->apiHeaders());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'numero', 'objeto', 'tipo', 'status'],
            ]);

        $this->assertEquals('Servico de limpeza predial via API', $response->json('data.objeto'));
    }

    public function test_criar_contrato_sem_campos_obrigatorios_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/contratos', ['objeto' => ''], $this->apiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['objeto']);
    }

    public function test_atualizar_contrato_via_api(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'status' => StatusContrato::Vigente,
            'tipo' => TipoContrato::Servico,
        ]);

        $response = $this->putJson(
            "/api/v1/contratos/{$contrato->id}",
            [
                'objeto' => 'Objeto atualizado via API',
                'tipo' => TipoContrato::Servico->value,
                'modalidade_contratacao' => $contrato->modalidade_contratacao->value,
                'secretaria_id' => $contrato->secretaria_id,
                'fornecedor_id' => $contrato->fornecedor_id,
                'numero_processo' => $contrato->numero_processo,
                'valor_global' => $contrato->valor_global,
                'data_inicio' => $contrato->data_inicio->toDateString(),
                'data_fim' => $contrato->data_fim->toDateString(),
            ],
            $this->apiHeaders()
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.objeto', 'Objeto atualizado via API');
    }

    public function test_atualizar_contrato_vencido_retorna_403(): void
    {
        $contrato = Contrato::factory()->vencido()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);

        $response = $this->putJson(
            "/api/v1/contratos/{$contrato->id}",
            ['objeto' => 'Tentativa'],
            $this->apiHeaders()
        );

        // UpdateContratoRequest::authorize() retorna false para contrato vencido (RN-006)
        $response->assertStatus(403);
    }

    public function test_excluir_contrato_via_api(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);

        $response = $this->deleteJson(
            "/api/v1/contratos/{$contrato->id}",
            [],
            $this->apiHeaders()
        );

        $response->assertStatus(204);
        $this->assertNotNull(Contrato::withTrashed()->find($contrato->id)->deleted_at);
    }

    // --- Rotas aninhadas (IMP-062) ---

    public function test_listar_fiscais_do_contrato(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);
        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'servidor_id' => $this->servidor->id,
        ]);

        $response = $this->getJson(
            "/api/v1/contratos/{$contrato->id}/fiscais",
            $this->apiHeaders()
        );

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_listar_documentos_do_contrato(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);

        $response = $this->getJson(
            "/api/v1/contratos/{$contrato->id}/documentos",
            $this->apiHeaders()
        );

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    // --- Fornecedor CRUD ---

    public function test_criar_fornecedor_via_api(): void
    {
        $response = $this->postJson('/api/v1/fornecedores', [
            'razao_social' => 'Empresa Teste API LTDA',
            'cnpj' => '11.222.333/0001-81',
        ], $this->apiHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('data.razao_social', 'Empresa Teste API LTDA');
    }

    public function test_atualizar_fornecedor_via_api(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->putJson(
            "/api/v1/fornecedores/{$fornecedor->id}",
            [
                'razao_social' => 'Nome Atualizado API',
                'cnpj' => $fornecedor->cnpj,
            ],
            $this->apiHeaders()
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.razao_social', 'Nome Atualizado API');
    }

    public function test_excluir_fornecedor_via_api(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->deleteJson(
            "/api/v1/fornecedores/{$fornecedor->id}",
            [],
            $this->apiHeaders()
        );

        $response->assertStatus(204);
    }

    // --- Execucao Financeira ---

    public function test_registrar_execucao_financeira_via_api(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);

        $response = $this->postJson(
            "/api/v1/contratos/{$contrato->id}/execucoes",
            [
                'descricao' => 'Pagamento mensal janeiro',
                'valor' => 10000.50,
                'data_execucao' => now()->toDateString(),
            ],
            $this->apiHeaders()
        );

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Execucao financeira registrada com sucesso.');
    }

    // --- Ocorrencia ---

    public function test_registrar_ocorrencia_via_api(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);
        $fiscal = Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'servidor_id' => $this->servidor->id,
        ]);

        $response = $this->postJson(
            "/api/v1/contratos/{$contrato->id}/ocorrencias",
            [
                'tipo_ocorrencia' => 'atraso',
                'data_ocorrencia' => now()->toDateString(),
                'descricao' => 'Atraso na entrega do servico contratado via API',
                'fiscal_id' => $fiscal->id,
            ],
            $this->apiHeaders()
        );

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Ocorrencia registrada com sucesso.');
    }

    public function test_resolver_ocorrencia_via_api(): void
    {
        $contrato = Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
        ]);
        $ocorrencia = Ocorrencia::factory()->create([
            'contrato_id' => $contrato->id,
        ]);

        $response = $this->postJson(
            "/api/v1/ocorrencias/{$ocorrencia->id}/resolver",
            ['observacoes' => 'Resolvido via API'],
            $this->apiHeaders()
        );

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Ocorrencia resolvida com sucesso.');
    }

    // --- Filtros combinados (IMP-062) ---

    public function test_filtros_combinados_status_e_secretaria(): void
    {
        Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'status' => StatusContrato::Vigente,
        ]);

        $response = $this->getJson(
            "/api/v1/contratos?status=vigente&secretaria_id={$this->secretaria->id}",
            $this->apiHeaders()
        );

        $response->assertStatus(200);
        foreach ($response->json('data') as $contrato) {
            $this->assertEquals('vigente', $contrato['status']['value']);
        }
    }

    public function test_filtro_data_range(): void
    {
        Contrato::factory()->create([
            'secretaria_id' => $this->secretaria->id,
            'data_inicio' => '2025-06-01',
        ]);

        $response = $this->getJson(
            '/api/v1/contratos?data_inicio_de=2025-01-01&data_inicio_ate=2025-12-31',
            $this->apiHeaders()
        );

        $response->assertStatus(200);
        foreach ($response->json('data') as $contrato) {
            $this->assertGreaterThanOrEqual('2025-01-01', $contrato['data_inicio']);
            $this->assertLessThanOrEqual('2025-12-31', $contrato['data_inicio']);
        }
    }

    // --- Permissoes ---

    public function test_usuario_sem_permissao_nao_cria_contrato(): void
    {
        $userFiscal = $this->createUserWithRole('fiscal');
        Sanctum::actingAs($userFiscal);

        $response = $this->postJson('/api/v1/contratos', $this->dadosContratoValidos(), $this->apiHeaders());

        $response->assertStatus(403);
    }
}
