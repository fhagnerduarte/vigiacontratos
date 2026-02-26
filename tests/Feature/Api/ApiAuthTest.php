<?php

namespace Tests\Feature\Api;

use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ApiAuthTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    protected User $adminUser;
    protected Tenant $testTenant;
    protected string $testEmail;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        $this->testTenant = $this->setUpTenant();
        $this->testEmail = 'api_' . uniqid() . '@test.com';
        $this->adminUser = $this->createAdminUser([
            'email' => $this->testEmail,
            'password' => Hash::make('password123'),
        ]);
    }

    // --- Login ---

    public function test_login_com_credenciais_validas_retorna_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testEmail,
            'password' => 'password123',
            'device_name' => 'phpunit',
        ], ['X-Tenant-Slug' => 'testing']);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'nome', 'email', 'role'],
            ]);
    }

    public function test_login_com_credenciais_invalidas_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testEmail,
            'password' => 'senha_errada',
            'device_name' => 'phpunit',
        ], ['X-Tenant-Slug' => 'testing']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_sem_device_name_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testEmail,
            'password' => 'password123',
        ], ['X-Tenant-Slug' => 'testing']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device_name']);
    }

    public function test_login_usuario_inativo_retorna_422(): void
    {
        $this->adminUser->update(['is_ativo' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testEmail,
            'password' => 'password123',
            'device_name' => 'phpunit',
        ], ['X-Tenant-Slug' => 'testing']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_com_abilities_especificas(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testEmail,
            'password' => 'password123',
            'device_name' => 'phpunit',
            'abilities' => ['contrato.visualizar', 'alerta.visualizar'],
        ], ['X-Tenant-Slug' => 'testing']);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user']);
    }

    // --- Logout ---

    public function test_logout_revoga_token(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/v1/auth/logout', [], [
            'X-Tenant-Slug' => 'testing',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Token revogado com sucesso.']);
    }

    // --- Me ---

    public function test_me_retorna_dados_do_usuario_autenticado(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/v1/auth/me', [
            'X-Tenant-Slug' => 'testing',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'nome', 'email', 'role', 'secretarias', 'is_perfil_estrategico', 'mfa_enabled',
            ]);
    }

    // --- Token Management ---

    public function test_listar_tokens_do_usuario(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/v1/auth/tokens', [
            'X-Tenant-Slug' => 'testing',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['tokens']);
    }

    public function test_revogar_token_especifico(): void
    {
        $token = $this->adminUser->createToken('test-token');

        Sanctum::actingAs($this->adminUser);

        $response = $this->deleteJson("/api/v1/auth/tokens/{$token->accessToken->id}", [], [
            'X-Tenant-Slug' => 'testing',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Token revogado com sucesso.']);
    }

    public function test_revogar_token_inexistente_retorna_404(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->deleteJson('/api/v1/auth/tokens/99999', [], [
            'X-Tenant-Slug' => 'testing',
        ]);

        $response->assertStatus(404);
    }

    // --- Tenant Resolution ---

    public function test_request_sem_tenant_header_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testEmail,
            'password' => 'password123',
            'device_name' => 'phpunit',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Header X-Tenant-Slug e obrigatorio para requisicoes da API.']);
    }

    public function test_request_com_tenant_inexistente_retorna_404(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testEmail,
            'password' => 'password123',
            'device_name' => 'phpunit',
        ], ['X-Tenant-Slug' => 'tenant-inexistente-xyz']);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Tenant nao encontrado.']);
    }

    public function test_request_com_tenant_inativo_retorna_403(): void
    {
        $this->testTenant->update(['is_ativo' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testEmail,
            'password' => 'password123',
            'device_name' => 'phpunit',
        ], ['X-Tenant-Slug' => 'testing']);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Tenant inativo.']);
    }

    // --- Autenticacao em endpoints protegidos ---

    public function test_request_sem_token_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/contratos', [
            'X-Tenant-Slug' => 'testing',
        ]);

        $response->assertStatus(401);
    }

    public function test_request_com_token_valido_acessa_endpoint(): void
    {
        $secretaria = Secretaria::factory()->create();
        $this->adminUser->secretarias()->attach($secretaria->id);

        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/v1/contratos', [
            'X-Tenant-Slug' => 'testing',
        ]);

        $response->assertStatus(200);
    }
}
