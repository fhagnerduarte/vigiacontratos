<?php

namespace Tests\Feature\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class PermissoesControllerTest extends TestCase
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

    // ─── INDEX ──────────────────────────────────────────────

    public function test_index_renderiza_matriz_de_permissoes(): void
    {
        $role = Role::where('nome', 'secretario')->first()
            ?? Role::factory()->create(['nome' => 'secretario']);

        $response = $this->actAsAdmin()->get(
            route('tenant.permissoes.index', $role)
        );

        $response->assertStatus(200);
        $response->assertViewIs('tenant.permissoes.index');
        $response->assertViewHas('role');
        $response->assertViewHas('permissions');
        $response->assertViewHas('rolePermissionIds');
    }

    // ─── UPDATE ─────────────────────────────────────────────

    public function test_update_sincroniza_permissoes_do_role(): void
    {
        $role = Role::factory()->create(['nome' => 'perfil_teste_sync']);
        $permissions = Permission::take(3)->pluck('id')->toArray();

        $response = $this->actAsAdmin()->put(
            route('tenant.permissoes.update', $role),
            ['permissions' => $permissions]
        );

        $response->assertRedirect(route('tenant.permissoes.index', $role));
        $response->assertSessionHas('success');

        // Verifica que as permissoes foram sincronizadas
        $this->assertCount(3, $role->fresh()->permissions);
    }

    public function test_update_com_array_vazio_remove_permissoes(): void
    {
        $role = Role::factory()->create(['nome' => 'perfil_teste_vazio']);
        $permissions = Permission::take(2)->pluck('id')->toArray();
        $role->permissions()->sync($permissions);

        $this->assertCount(2, $role->fresh()->permissions);

        $response = $this->actAsAdmin()->put(
            route('tenant.permissoes.update', $role),
            ['permissions' => []]
        );

        $response->assertRedirect(route('tenant.permissoes.index', $role));
        $this->assertCount(0, $role->fresh()->permissions);
    }

    public function test_exige_permissao_configuracao_editar(): void
    {
        $role = Role::factory()->create(['nome' => 'perfil_alvo']);
        $roleSemPermissao = Role::factory()->create(['nome' => 'sem_config']);
        $user = User::factory()->create(['role_id' => $roleSemPermissao->id, 'is_ativo' => true]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.permissoes.index', $role));

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            'Esperado 403 ou 404, recebido: ' . $response->getStatusCode()
        );
    }

    public function test_valida_permission_id_existe(): void
    {
        $role = Role::factory()->create(['nome' => 'perfil_teste_val']);

        $response = $this->actAsAdmin()->put(
            route('tenant.permissoes.update', $role),
            ['permissions' => [99999]]
        );

        $response->assertSessionHasErrors(['permissions.0']);
    }
}
