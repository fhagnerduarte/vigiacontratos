<?php

namespace Tests\Feature\Controllers;

use App\Models\Secretaria;
use App\Models\Servidor;
use Database\Factories\ServidorFactory;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ServidoresControllerTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    protected function actAsAdmin(): self
    {
        $user = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        return $this->actingAs($user)->withSession(['mfa_verified' => true]);
    }

    // ─── INDEX ─────────────────────────────────────────────

    public function test_index_exibe_listagem_de_servidores(): void
    {
        Servidor::factory()->count(3)->create();

        $response = $this->actAsAdmin()->get(route('tenant.servidores.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.servidores.index');
        $response->assertViewHas('servidores');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.servidores.index'));
        $response->assertRedirect();
    }

    // ─── CREATE ────────────────────────────────────────────

    public function test_create_exibe_formulario_com_secretarias(): void
    {
        Secretaria::factory()->count(2)->create();

        $response = $this->actAsAdmin()->get(route('tenant.servidores.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.servidores.create');
        $response->assertViewHas('secretarias');
    }

    // ─── STORE ─────────────────────────────────────────────

    public function test_store_cria_servidor_com_sucesso(): void
    {
        $secretaria = Secretaria::factory()->create();
        $cpf = ServidorFactory::gerarCpfValido();

        $dados = [
            'nome' => 'Maria dos Santos',
            'cpf' => $cpf,
            'matricula' => 'MAT-000001',
            'cargo' => 'Analista Administrativo',
            'secretaria_id' => $secretaria->id,
            'email' => 'maria.santos@prefeitura.gov.br',
            'telefone' => '(11) 98765-4321',
            'is_ativo' => '1',
        ];

        $response = $this->actAsAdmin()->post(route('tenant.servidores.store'), $dados);

        $response->assertRedirect(route('tenant.servidores.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('servidores', [
            'nome' => 'Maria dos Santos',
            'matricula' => 'MAT-000001',
        ], 'tenant');
    }

    public function test_store_valida_matricula_obrigatoria(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.servidores.store'), [
            'nome' => 'Teste',
            'cpf' => ServidorFactory::gerarCpfValido(),
            'matricula' => '',
            'cargo' => 'Analista',
            'secretaria_id' => Secretaria::factory()->create()->id,
        ]);

        $response->assertSessionHasErrors('matricula');
    }

    public function test_store_valida_cpf_invalido(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.servidores.store'), [
            'nome' => 'Teste',
            'cpf' => '111.111.111-11',
            'matricula' => 'MAT-999',
            'cargo' => 'Teste',
            'secretaria_id' => Secretaria::factory()->create()->id,
            'email' => 'test@test.com',
        ]);

        $response->assertSessionHasErrors('cpf');
    }

    public function test_store_valida_matricula_unica(): void
    {
        $servidor = Servidor::factory()->create();

        $response = $this->actAsAdmin()->post(route('tenant.servidores.store'), [
            'nome' => 'Outro Servidor',
            'cpf' => ServidorFactory::gerarCpfValido(),
            'matricula' => $servidor->matricula,
            'cargo' => 'Teste',
            'secretaria_id' => $servidor->secretaria_id,
            'email' => 'outro@test.com',
        ]);

        $response->assertSessionHasErrors('matricula');
    }

    public function test_store_valida_cpf_unico(): void
    {
        $servidor = Servidor::factory()->create();

        $response = $this->actAsAdmin()->post(route('tenant.servidores.store'), [
            'nome' => 'Outro Servidor',
            'cpf' => $servidor->cpf,
            'matricula' => 'MAT-UNIQUE',
            'cargo' => 'Teste',
            'secretaria_id' => $servidor->secretaria_id,
            'email' => 'outro@test.com',
        ]);

        $response->assertSessionHasErrors('cpf');
    }

    // ─── EDIT ──────────────────────────────────────────────

    public function test_edit_exibe_formulario_preenchido(): void
    {
        $servidor = Servidor::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.servidores.edit', $servidor));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.servidores.edit');
        $response->assertViewHas('servidor');
        $response->assertViewHas('secretarias');
    }

    // ─── UPDATE ────────────────────────────────────────────

    public function test_update_atualiza_servidor_com_sucesso(): void
    {
        $servidor = Servidor::factory()->create();

        $response = $this->actAsAdmin()->put(route('tenant.servidores.update', $servidor), [
            'nome' => 'Nome Atualizado',
            'cpf' => $servidor->cpf,
            'matricula' => $servidor->matricula,
            'cargo' => 'Novo Cargo',
            'secretaria_id' => $servidor->secretaria_id,
            'email' => $servidor->email,
            'telefone' => $servidor->telefone,
            'is_ativo' => '1',
        ]);

        $response->assertRedirect(route('tenant.servidores.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('servidores', [
            'id' => $servidor->id,
            'nome' => 'Nome Atualizado',
            'cargo' => 'Novo Cargo',
        ], 'tenant');
    }

    // ─── DESTROY ───────────────────────────────────────────

    public function test_destroy_exclui_servidor(): void
    {
        $servidor = Servidor::factory()->create();

        $response = $this->actAsAdmin()->delete(route('tenant.servidores.destroy', $servidor));

        $response->assertRedirect(route('tenant.servidores.index'));
        $this->assertDatabaseMissing('servidores', ['id' => $servidor->id], 'tenant');
    }
}
