<?php

namespace Tests\Feature\Controllers;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class RolesControllerTest extends TestCase
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

    public function test_index_exibe_listagem_de_perfis(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.roles.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.roles.index');
        $response->assertViewHas('roles');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.roles.index'));
        $response->assertRedirect();
    }

    // ─── CREATE ────────────────────────────────────────────

    public function test_create_exibe_formulario(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.roles.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.roles.create');
    }

    // ─── STORE ─────────────────────────────────────────────

    public function test_store_cria_perfil_com_sucesso(): void
    {
        $dados = [
            'nome' => 'perfil_customizado',
            'descricao' => 'Perfil personalizado para teste',
        ];

        $response = $this->actAsAdmin()->post(route('tenant.roles.store'), $dados);

        $response->assertRedirect(route('tenant.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'nome' => 'perfil_customizado',
            'is_padrao' => false,
        ], 'tenant');
    }

    public function test_store_valida_nome_obrigatorio(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.roles.store'), [
            'nome' => '',
            'descricao' => 'Teste',
        ]);

        $response->assertSessionHasErrors('nome');
    }

    public function test_store_valida_nome_unico(): void
    {
        $response = $this->actAsAdmin()->post(route('tenant.roles.store'), [
            'nome' => 'administrador_geral',
            'descricao' => 'Duplicado',
        ]);

        $response->assertSessionHasErrors('nome');
    }

    // ─── EDIT ──────────────────────────────────────────────

    public function test_edit_exibe_formulario_preenchido(): void
    {
        $role = Role::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.roles.edit', $role));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.roles.edit');
        $response->assertViewHas('role');
    }

    // ─── UPDATE ────────────────────────────────────────────

    public function test_update_atualiza_perfil_com_sucesso(): void
    {
        $role = Role::factory()->create(['nome' => 'perfil_teste_update']);

        $response = $this->actAsAdmin()->put(route('tenant.roles.update', $role), [
            'nome' => 'perfil_teste_update',
            'descricao' => 'Descrição atualizada',
        ]);

        $response->assertRedirect(route('tenant.roles.index'));

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'descricao' => 'Descrição atualizada',
        ], 'tenant');
    }

    // ─── DESTROY ───────────────────────────────────────────

    public function test_destroy_exclui_perfil_customizado(): void
    {
        $role = Role::factory()->create(['is_padrao' => false]);

        $response = $this->actAsAdmin()->delete(route('tenant.roles.destroy', $role));

        $response->assertRedirect(route('tenant.roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id], 'tenant');
    }

    public function test_destroy_protege_perfil_padrao(): void
    {
        $role = Role::where('is_padrao', true)->first();

        $response = $this->actAsAdmin()->delete(route('tenant.roles.destroy', $role));

        // Deve bloquear a exclusão
        $this->assertDatabaseHas('roles', ['id' => $role->id], 'tenant');
    }
}
