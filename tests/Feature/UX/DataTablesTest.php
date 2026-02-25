<?php

namespace Tests\Feature\UX;

use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Role;
use App\Models\Servidor;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class DataTablesTest extends TestCase
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
    // Assets preparados
    // ==========================================

    public function test_datatables_init_js_existe(): void
    {
        $this->assertFileExists(public_path('assets/js/datatables-init.js'));
    }

    public function test_datatables_lib_existe(): void
    {
        $this->assertFileExists(public_path('assets/js/lib/dataTables.min.js'));
    }

    public function test_datatables_init_tem_locale_ptbr(): void
    {
        $content = file_get_contents(public_path('assets/js/datatables-init.js'));
        $this->assertStringContainsString('Nenhum registro encontrado', $content);
        $this->assertStringContainsString('Mostrando', $content);
        $this->assertStringContainsString('Buscar:', $content);
        $this->assertStringContainsString('Proximo', $content);
    }

    public function test_css_tem_estilos_datatables(): void
    {
        $content = file_get_contents(public_path('assets/css/custom.css'));
        $this->assertStringContainsString('dataTables_wrapper', $content);
        $this->assertStringContainsString('dataTables_filter', $content);
    }

    // ==========================================
    // Listagens com paginacao server-side funcionando
    // ==========================================

    public function test_fornecedores_index_funciona(): void
    {
        Fornecedor::create([
            'razao_social' => 'DataTable Test LTDA',
            'cnpj' => '33.444.555/0001-66',
            'is_ativo' => true,
        ]);

        $response = $this->actAsAdmin()
            ->get(route('tenant.fornecedores.index'));

        $response->assertStatus(200);
        $response->assertSee('DataTable Test LTDA');
    }

    public function test_servidores_index_funciona(): void
    {
        Servidor::create([
            'nome' => 'Servidor DataTable Test',
            'cpf' => '222.333.444-55',
            'matricula' => 'DT-001',
            'cargo' => 'Analista',
            'is_ativo' => true,
        ]);

        $response = $this->actAsAdmin()
            ->get(route('tenant.servidores.index'));

        $response->assertStatus(200);
        $response->assertSee('Servidor DataTable Test');
    }

    public function test_usuarios_index_funciona(): void
    {
        $response = $this->actAsAdmin()
            ->get(route('tenant.users.index'));

        $response->assertStatus(200);
        $response->assertSee($this->admin->nome);
    }

    public function test_roles_index_funciona(): void
    {
        $response = $this->actAsAdmin()
            ->get(route('tenant.roles.index'));

        $response->assertStatus(200);
        $this->assertTrue(Role::count() > 0);
    }

    public function test_contratos_index_funciona(): void
    {
        Contrato::factory()->create();

        $response = $this->actAsAdmin()
            ->get(route('tenant.contratos.index'));

        $response->assertStatus(200);
    }
}
