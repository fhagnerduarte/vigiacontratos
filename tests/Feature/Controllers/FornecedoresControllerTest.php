<?php

namespace Tests\Feature\Controllers;

use App\Models\Fornecedor;
use Database\Factories\FornecedorFactory;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class FornecedoresControllerTest extends TestCase
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

    public function test_index_exibe_listagem_de_fornecedores(): void
    {
        Fornecedor::factory()->count(3)->create();

        $response = $this->actAsAdmin()->get(route('tenant.fornecedores.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.fornecedores.index');
        $response->assertViewHas('fornecedores');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.fornecedores.index'));
        $response->assertRedirect();
    }

    public function test_index_usuario_sem_permissao_retorna_403(): void
    {
        $role = \App\Models\Role::factory()->create(['nome' => 'role_sem_permissao']);
        $user = \App\Models\User::factory()->create(['role_id' => $role->id]);
        $response = $this->actingAs($user)->get(route('tenant.fornecedores.index'));
        $response->assertStatus(403);
    }

    // ─── CREATE ────────────────────────────────────────────

    public function test_create_exibe_formulario(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.fornecedores.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.fornecedores.create');
    }

    // ─── STORE ─────────────────────────────────────────────

    public function test_store_cria_fornecedor_com_sucesso(): void
    {
        $cnpj = FornecedorFactory::gerarCnpjValido();

        $dados = [
            'razao_social' => 'Empresa Teste LTDA',
            'nome_fantasia' => 'Empresa Teste',
            'cnpj' => $cnpj,
            'representante_legal' => 'José da Silva',
            'email' => 'contato@empresateste.com.br',
            'telefone' => '(11) 3456-7890',
            'endereco' => 'Rua das Flores, 123',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'cep' => '01234-567',
        ];

        $response = $this->actAsAdmin()->post(route('tenant.fornecedores.store'), $dados);

        $response->assertRedirect(route('tenant.fornecedores.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fornecedores', [
            'razao_social' => 'Empresa Teste LTDA',
        ], 'tenant');
    }

    public function test_store_valida_cnpj_obrigatorio(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.fornecedores.store'), [
            'razao_social' => 'Empresa Teste',
            'cnpj' => '',
        ]);

        $response->assertSessionHasErrors('cnpj');
    }

    public function test_store_valida_cnpj_invalido(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.fornecedores.store'), [
            'razao_social' => 'Empresa Teste',
            'nome_fantasia' => 'Teste',
            'cnpj' => '11.111.111/1111-11',
            'email' => 'test@test.com',
        ]);

        $response->assertSessionHasErrors('cnpj');
    }

    public function test_store_valida_cnpj_unico(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->actAsAdmin()->post(route('tenant.fornecedores.store'), [
            'razao_social' => 'Outra Empresa',
            'nome_fantasia' => 'Outra',
            'cnpj' => $fornecedor->cnpj,
            'email' => 'outra@test.com',
        ]);

        $response->assertSessionHasErrors('cnpj');
    }

    public function test_store_valida_razao_social_obrigatoria(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.fornecedores.store'), [
            'razao_social' => '',
            'cnpj' => FornecedorFactory::gerarCnpjValido(),
        ]);

        $response->assertSessionHasErrors('razao_social');
    }

    // ─── EDIT ──────────────────────────────────────────────

    public function test_edit_exibe_formulario_preenchido(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.fornecedores.edit', $fornecedor));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.fornecedores.edit');
        $response->assertViewHas('fornecedor');
    }

    // ─── UPDATE ────────────────────────────────────────────

    public function test_update_atualiza_fornecedor_com_sucesso(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->actAsAdmin()->put(route('tenant.fornecedores.update', $fornecedor), [
            'razao_social' => 'Empresa Atualizada LTDA',
            'nome_fantasia' => $fornecedor->nome_fantasia,
            'cnpj' => $fornecedor->cnpj,
            'representante_legal' => 'Novo Representante',
            'email' => $fornecedor->email,
            'telefone' => $fornecedor->telefone,
            'endereco' => $fornecedor->endereco,
            'cidade' => $fornecedor->cidade,
            'uf' => $fornecedor->uf,
            'cep' => $fornecedor->cep,
        ]);

        $response->assertRedirect(route('tenant.fornecedores.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fornecedores', [
            'id' => $fornecedor->id,
            'razao_social' => 'Empresa Atualizada LTDA',
        ], 'tenant');
    }

    public function test_update_permite_manter_cnpj_proprio(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->actAsAdmin()->put(route('tenant.fornecedores.update', $fornecedor), [
            'razao_social' => $fornecedor->razao_social,
            'nome_fantasia' => $fornecedor->nome_fantasia,
            'cnpj' => $fornecedor->cnpj,
            'email' => $fornecedor->email,
        ]);

        $response->assertSessionDoesntHaveErrors('cnpj');
    }

    // ─── DESTROY ───────────────────────────────────────────

    public function test_destroy_exclui_fornecedor(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->actAsAdmin()->delete(route('tenant.fornecedores.destroy', $fornecedor));

        $response->assertRedirect(route('tenant.fornecedores.index'));
    }
}
