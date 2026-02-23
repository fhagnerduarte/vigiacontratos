<?php

namespace Tests\Feature\Controllers;

use App\Models\Role;
use App\Models\Secretaria;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class UsersControllerTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

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

    // ─── INDEX ─────────────────────────────────────────────

    public function test_index_exibe_listagem_de_usuarios(): void
    {
        User::factory()->count(2)->create();

        $response = $this->actAsAdmin()->get(route('tenant.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.users.index');
        $response->assertViewHas('users');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.users.index'));
        $response->assertRedirect();
    }

    public function test_index_usuario_sem_permissao_retorna_403(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');
        $response = $this->actingAs($user)->get(route('tenant.users.index'));
        $response->assertStatus(403);
    }

    // ─── CREATE ────────────────────────────────────────────

    public function test_create_exibe_formulario_com_roles_e_secretarias(): void
    {
        Secretaria::factory()->count(2)->create();

        $response = $this->actAsAdmin()->get(route('tenant.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.users.create');
        $response->assertViewHas('roles');
        $response->assertViewHas('secretarias');
    }

    // ─── STORE ─────────────────────────────────────────────

    public function test_store_cria_usuario_com_sucesso(): void
    {
        $role = Role::where('nome', 'secretario')->first();
        $secretaria = Secretaria::factory()->create();

        $dados = [
            'nome' => 'Novo Usuário de Teste',
            'email' => 'novo@prefeitura.gov.br',
            'password' => 'SenhaForte@123',
            'password_confirmation' => 'SenhaForte@123',
            'role_id' => $role->id,
            'is_ativo' => '1',
            'secretarias' => [$secretaria->id],
        ];

        $response = $this->actAsAdmin()->post(route('tenant.users.store'), $dados);

        $response->assertRedirect(route('tenant.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'nome' => 'Novo Usuário de Teste',
            'email' => 'novo@prefeitura.gov.br',
            'role_id' => $role->id,
        ], 'tenant');
    }

    public function test_store_valida_email_obrigatorio(): void
    {
        $role = Role::where('nome', 'secretario')->first();

        $response = $this->actAsAdmin()->post(route('tenant.users.store'), [
            'nome' => 'Teste',
            'email' => '',
            'password' => 'SenhaForte@123',
            'password_confirmation' => 'SenhaForte@123',
            'role_id' => $role->id,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_valida_email_unico(): void
    {
        $existente = User::factory()->create();
        $role = Role::where('nome', 'secretario')->first();

        $response = $this->actAsAdmin()->post(route('tenant.users.store'), [
            'nome' => 'Teste',
            'email' => $existente->email,
            'password' => 'SenhaForte@123',
            'password_confirmation' => 'SenhaForte@123',
            'role_id' => $role->id,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_valida_senha_confirmacao(): void
    {
        $role = Role::where('nome', 'secretario')->first();

        $response = $this->actAsAdmin()->post(route('tenant.users.store'), [
            'nome' => 'Teste',
            'email' => 'teste@prefeitura.gov.br',
            'password' => 'SenhaForte@123',
            'password_confirmation' => 'SenhaDiferente@456',
            'role_id' => $role->id,
        ]);

        $response->assertSessionHasErrors('password');
    }

    // ─── EDIT ──────────────────────────────────────────────

    public function test_edit_exibe_formulario_preenchido(): void
    {
        $user = User::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.users.edit', $user));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.users.edit');
        $response->assertViewHas('user');
        $response->assertViewHas('roles');
        $response->assertViewHas('secretarias');
    }

    // ─── UPDATE ────────────────────────────────────────────

    public function test_update_atualiza_usuario_com_sucesso(): void
    {
        $user = User::factory()->create();
        $role = Role::where('nome', 'gestor_contrato')->first();

        $response = $this->actAsAdmin()->put(route('tenant.users.update', $user), [
            'nome' => 'Nome Atualizado',
            'email' => $user->email,
            'role_id' => $role->id,
            'is_ativo' => '1',
        ]);

        $response->assertRedirect(route('tenant.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nome' => 'Nome Atualizado',
            'role_id' => $role->id,
        ], 'tenant');
    }

    public function test_update_sem_senha_mantem_senha_atual(): void
    {
        $user = User::factory()->create();
        $senhaOriginal = $user->password;

        $this->actAsAdmin()->put(route('tenant.users.update', $user), [
            'nome' => $user->nome,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'is_ativo' => '1',
        ]);

        $user->refresh();
        $this->assertEquals($senhaOriginal, $user->password);
    }

    // ─── DESTROY ───────────────────────────────────────────

    public function test_destroy_desativa_usuario(): void
    {
        $user = User::factory()->create(['is_ativo' => true]);

        $response = $this->actAsAdmin()->delete(route('tenant.users.destroy', $user));

        $response->assertRedirect(route('tenant.users.index'));

        // Controller faz update is_ativo=false (desativa, não deleta)
        $user->refresh();
        $this->assertFalse((bool) $user->is_ativo);
    }
}
