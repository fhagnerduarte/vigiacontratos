<?php

namespace Tests\Feature\Controllers;

use App\Models\Secretaria;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class SecretariasControllerTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    protected function authenticatedAdmin(): \App\Models\User
    {
        return $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
    }

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->authenticatedAdmin())
            ->withSession(['mfa_verified' => true]);
    }

    // ─── INDEX ─────────────────────────────────────────────

    public function test_index_exibe_listagem_de_secretarias(): void
    {
        Secretaria::factory()->count(3)->create();

        $response = $this->actAsAdmin()->get(route('tenant.secretarias.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.secretarias.index');
        $response->assertViewHas('secretarias');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.secretarias.index'));
        $response->assertRedirect();
    }

    public function test_index_usuario_sem_permissao_retorna_403(): void
    {
        $role = \App\Models\Role::factory()->create(['nome' => 'role_sem_permissao']);
        $user = \App\Models\User::factory()->create(['role_id' => $role->id]);
        $response = $this->actingAs($user)->get(route('tenant.secretarias.index'));
        $response->assertStatus(403);
    }

    // ─── CREATE ────────────────────────────────────────────

    public function test_create_exibe_formulario(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.secretarias.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.secretarias.create');
    }

    // ─── STORE ─────────────────────────────────────────────

    public function test_store_cria_secretaria_com_sucesso(): void
    {
        $dados = [
            'nome' => 'Secretaria de Educação',
            'sigla' => 'SMED',
            'responsavel' => 'João da Silva',
            'email' => 'educacao@prefeitura.gov.br',
            'telefone' => '(11) 3456-7890',
        ];

        $response = $this->actAsAdmin()->post(route('tenant.secretarias.store'), $dados);

        $response->assertRedirect(route('tenant.secretarias.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('secretarias', [
            'nome' => 'Secretaria de Educação',
            'sigla' => 'SMED',
        ], 'tenant');
    }

    public function test_store_valida_nome_obrigatorio(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.secretarias.store'), [
            'nome' => '',
            'sigla' => 'TST',
        ]);

        $response->assertSessionHasErrors('nome');
    }

    public function test_store_aceita_sigla_nullable(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.secretarias.store'), [
            'nome' => 'Secretaria Sem Sigla',
        ]);

        // sigla é nullable — não deve gerar erro de sigla
        $response->assertSessionDoesntHaveErrors('sigla');
        $response->assertRedirect(route('tenant.secretarias.index'));
    }

    // ─── EDIT ──────────────────────────────────────────────

    public function test_edit_exibe_formulario_preenchido(): void
    {
        $secretaria = Secretaria::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.secretarias.edit', $secretaria));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.secretarias.edit');
        $response->assertViewHas('secretaria');
    }

    // ─── UPDATE ────────────────────────────────────────────

    public function test_update_atualiza_secretaria_com_sucesso(): void
    {
        $secretaria = Secretaria::factory()->create();

        $response = $this->actAsAdmin()->put(route('tenant.secretarias.update', $secretaria), [
            'nome' => 'Secretaria Atualizada',
            'sigla' => $secretaria->sigla,
            'responsavel' => 'Novo Responsável',
            'email' => 'novo@prefeitura.gov.br',
            'telefone' => '(11) 9999-8888',
        ]);

        $response->assertRedirect(route('tenant.secretarias.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('secretarias', [
            'id' => $secretaria->id,
            'nome' => 'Secretaria Atualizada',
        ], 'tenant');
    }

    // ─── DESTROY ───────────────────────────────────────────

    public function test_destroy_exclui_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();

        $response = $this->actAsAdmin()->delete(route('tenant.secretarias.destroy', $secretaria));

        $response->assertRedirect(route('tenant.secretarias.index'));
        $this->assertDatabaseMissing('secretarias', ['id' => $secretaria->id], 'tenant');
    }
}
