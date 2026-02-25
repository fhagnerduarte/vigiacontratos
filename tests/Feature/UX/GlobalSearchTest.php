<?php

namespace Tests\Feature\UX;

use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class GlobalSearchTest extends TestCase
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

    // ==========================================
    // Busca Global
    // ==========================================

    public function test_busca_retorna_json(): void
    {
        $response = $this->actAsAdmin()
            ->getJson(route('tenant.busca', ['q' => 'teste']));

        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }

    public function test_busca_requer_minimo_2_caracteres(): void
    {
        $response = $this->actAsAdmin()
            ->getJson(route('tenant.busca', ['q' => 'a']));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_busca_encontra_contrato_por_numero(): void
    {
        Contrato::factory()->create([
            'numero' => 'BUSCA-999',
            'objeto' => 'Contrato busca global test',
        ]);

        $response = $this->actAsAdmin()
            ->getJson(route('tenant.busca', ['q' => 'BUSCA-999']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['type' => 'Contratos']);
    }

    public function test_busca_encontra_fornecedor_por_razao_social(): void
    {
        Fornecedor::create([
            'razao_social' => 'Fornecedor Pesquisavel XYZ',
            'cnpj' => '22.333.444/0001-55',
            'is_ativo' => true,
        ]);

        $response = $this->actAsAdmin()
            ->getJson(route('tenant.busca', ['q' => 'Pesquisavel XYZ']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['type' => 'Fornecedores']);
    }

    public function test_busca_encontra_servidor_por_nome(): void
    {
        Servidor::create([
            'nome' => 'Servidor Pesquisavel ABC',
            'cpf' => '111.222.333-44',
            'matricula' => 'MAT-9999',
            'cargo' => 'Analista',
            'is_ativo' => true,
        ]);

        $response = $this->actAsAdmin()
            ->getJson(route('tenant.busca', ['q' => 'Pesquisavel ABC']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['type' => 'Servidores']);
    }

    public function test_busca_retorna_vazio_para_termo_inexistente(): void
    {
        $response = $this->actAsAdmin()
            ->getJson(route('tenant.busca', ['q' => 'xyzinexistente999']));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_busca_requer_autenticacao(): void
    {
        $response = $this->getJson(route('tenant.busca', ['q' => 'test']));
        $response->assertUnauthorized();
    }

    // ==========================================
    // Navbar e Assets
    // ==========================================

    public function test_navbar_contem_busca_funcional(): void
    {
        $content = file_get_contents(resource_path('views/components/navbar.blade.php'));
        $this->assertStringContainsString('globalSearchInput', $content);
        $this->assertStringContainsString('globalSearchResults', $content);
        $this->assertStringContainsString('data-search-url', $content);
    }

    public function test_global_search_js_existe(): void
    {
        $this->assertFileExists(public_path('assets/js/global-search.js'));
    }

    public function test_keyboard_shortcuts_js_existe(): void
    {
        $this->assertFileExists(public_path('assets/js/keyboard-shortcuts.js'));
    }

    public function test_script_component_carrega_global_search(): void
    {
        $content = file_get_contents(resource_path('views/components/script.blade.php'));
        $this->assertStringContainsString('global-search.js', $content);
        $this->assertStringContainsString('keyboard-shortcuts.js', $content);
    }

    public function test_css_contem_estilos_busca(): void
    {
        $content = file_get_contents(public_path('assets/css/custom.css'));
        $this->assertStringContainsString('globalSearchResults', $content);
        $this->assertStringContainsString('search-result-item', $content);
        $this->assertStringContainsString('search-shortcut-hint', $content);
    }

    // ==========================================
    // Controller
    // ==========================================

    public function test_global_search_controller_existe(): void
    {
        $this->assertFileExists(app_path('Http/Controllers/Tenant/GlobalSearchController.php'));
    }

    public function test_rota_busca_esta_registrada(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('tenant.busca'));
    }
}
