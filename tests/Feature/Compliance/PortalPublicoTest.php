<?php

namespace Tests\Feature\Compliance;

use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Tenant;
use App\Services\DadosAbertosService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class PortalPublicoTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();

        $this->tenant = Tenant::first()
            ?? Tenant::create([
                'nome' => 'Prefeitura Teste',
                'slug' => 'pref-teste',
                'database_name' => config('database.connections.tenant.database'),
                'is_ativo' => true,
                'plano' => 'basico',
            ]);
    }

    // --- Middleware ResolveTenantPublic ---

    public function test_portal_slug_invalido_retorna_404(): void
    {
        $response = $this->get('/slug-inexistente/portal');
        $response->assertStatus(404);
    }

    public function test_portal_tenant_inativo_retorna_404(): void
    {
        $this->tenant->update(['is_ativo' => false]);

        $response = $this->get("/{$this->tenant->slug}/portal");
        $response->assertStatus(404);

        $this->tenant->update(['is_ativo' => true]);
    }

    // --- Portal Home ---

    public function test_portal_home_acessivel_sem_autenticacao(): void
    {
        $response = $this->get("/{$this->tenant->slug}/portal");
        $response->assertStatus(200);
        $response->assertSee('Transparencia Contratual');
    }

    public function test_portal_home_exibe_indicadores(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
            'valor_global' => 100000,
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal");
        $response->assertStatus(200);
        $response->assertSee('Contratos Publicados');
    }

    // --- Portal Contratos ---

    public function test_portal_contratos_lista_apenas_publicos_publicados(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
            'objeto' => 'Contrato Visivel Portal',
        ]);

        Contrato::factory()->create([
            'classificacao_sigilo' => 'reservado',
            'publicado_portal' => true,
            'objeto' => 'Contrato Reservado Oculto',
        ]);

        Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => false,
            'objeto' => 'Contrato Nao Publicado',
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal/contratos");
        $response->assertStatus(200);
        $response->assertSee('Contrato Visivel Portal');
        $response->assertDontSee('Contrato Reservado Oculto');
        $response->assertDontSee('Contrato Nao Publicado');
    }

    public function test_portal_contratos_filtro_busca(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
            'objeto' => 'Servico de Limpeza Predial',
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal/contratos?busca=Limpeza");
        $response->assertStatus(200);
        $response->assertSee('Servico de Limpeza Predial');
    }

    public function test_portal_contratos_filtro_ano(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
            'ano' => '2025',
            'objeto' => 'Contrato 2025',
        ]);

        Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
            'ano' => '2026',
            'objeto' => 'Contrato 2026',
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal/contratos?ano=2025");
        $response->assertStatus(200);
        $response->assertSee('Contrato 2025');
        $response->assertDontSee('Contrato 2026');
    }

    // --- Portal Contrato Detalhe ---

    public function test_portal_contrato_detalhe_publico(): void
    {
        $contrato = Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
            'numero' => 'LAI-DET-001',
            'objeto' => 'Objeto do Contrato Detalhe',
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal/contratos/LAI-DET-001");
        $response->assertStatus(200);
        $response->assertSee('Objeto do Contrato Detalhe');
    }

    public function test_portal_contrato_sigiloso_retorna_404(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'secreto',
            'publicado_portal' => false,
            'numero' => 'LAI-DET-002',
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal/contratos/LAI-DET-002");
        $response->assertStatus(404);
    }

    // --- Portal Fornecedores ---

    public function test_portal_fornecedores_acessivel(): void
    {
        Fornecedor::factory()->create([
            'razao_social' => 'Empresa Teste LAI',
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal/fornecedores");
        $response->assertStatus(200);
        $response->assertSee('Empresa Teste LAI');
    }

    public function test_portal_fornecedores_filtro_busca(): void
    {
        Fornecedor::factory()->create(['razao_social' => 'ABC Servicos Ltda']);
        Fornecedor::factory()->create(['razao_social' => 'XYZ Comercio Ltda']);

        $response = $this->get("/{$this->tenant->slug}/portal/fornecedores?busca=ABC");
        $response->assertStatus(200);
        $response->assertSee('ABC Servicos Ltda');
        $response->assertDontSee('XYZ Comercio Ltda');
    }

    // --- Dados Abertos ---

    public function test_portal_dados_abertos_pagina(): void
    {
        $response = $this->get("/{$this->tenant->slug}/portal/dados-abertos");
        $response->assertStatus(200);
        $response->assertSee('Dados Abertos');
        $response->assertSee('JSON');
        $response->assertSee('CSV');
    }

    public function test_portal_dados_abertos_json(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
            'objeto' => 'Contrato JSON Export',
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal/dados-abertos?formato=json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'metadata' => ['total', 'gerado_em', 'formato'],
            'dados',
        ]);
    }

    public function test_portal_dados_abertos_csv(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal/dados-abertos?formato=csv");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_portal_dados_abertos_json_nao_inclui_sigilosos(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
            'objeto' => 'Contrato Publico Visivel LAI Test',
        ]);

        Contrato::factory()->create([
            'classificacao_sigilo' => 'secreto',
            'publicado_portal' => false,
            'objeto' => 'Contrato Secreto Oculto LAI Test',
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal/dados-abertos?formato=json");
        $json = $response->json();

        $objetos = collect($json['dados'])->pluck('objeto');
        $this->assertTrue($objetos->contains('Contrato Publico Visivel LAI Test'));
        $this->assertFalse($objetos->contains('Contrato Secreto Oculto LAI Test'));
    }

    // --- DadosAbertosService ---

    public function test_dados_abertos_service_indicadores_publicos(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
            'valor_global' => 50000,
            'status' => 'vigente',
        ]);

        $indicadores = DadosAbertosService::obterIndicadoresPublicos();

        $this->assertArrayHasKey('total_contratos', $indicadores);
        $this->assertArrayHasKey('valor_total', $indicadores);
        $this->assertArrayHasKey('contratos_vigentes', $indicadores);
        $this->assertArrayHasKey('por_secretaria', $indicadores);
        $this->assertGreaterThanOrEqual(1, $indicadores['total_contratos']);
    }
}
