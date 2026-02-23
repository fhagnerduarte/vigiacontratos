<?php

namespace Tests\Unit\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissaoService;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class PermissaoServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_verificar_admin_sempre_true(): void
    {
        $admin = $this->createAdminUser();

        $this->assertTrue(PermissaoService::verificar($admin, 'qualquer.permissao'));
    }

    public function test_verificar_via_role(): void
    {
        $user = $this->createUserWithRole('gestor_contrato');

        // gestor_contrato tem contrato.visualizar
        $this->assertTrue(PermissaoService::verificar($user, 'contrato.visualizar'));
    }

    public function test_verificar_sem_permissao_false(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        // fiscal_contrato nao tem usuario.criar
        $this->assertFalse(PermissaoService::verificar($user, 'usuario.criar'));
    }

    public function test_atribuir_permissao_individual(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createUserWithRole('fiscal_contrato');
        $permission = Permission::where('nome', 'usuario.criar')->first();

        PermissaoService::atribuirPermissaoIndividual($user, $permission, null, $admin);

        $this->assertTrue($user->permissions()->where('permission_id', $permission->id)->exists());
    }

    public function test_atribuir_permissao_com_expiracao(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createUserWithRole('fiscal_contrato');
        $permission = Permission::where('nome', 'usuario.criar')->first();
        $expiresAt = Carbon::now()->addDays(30);

        PermissaoService::atribuirPermissaoIndividual($user, $permission, $expiresAt, $admin);

        $pivot = $user->permissions()->where('permission_id', $permission->id)->first();
        $this->assertNotNull($pivot);
        $this->assertNotNull($pivot->pivot->expires_at);
    }

    public function test_revogar_permissao_individual(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createUserWithRole('fiscal_contrato');
        $permission = Permission::where('nome', 'usuario.criar')->first();

        PermissaoService::atribuirPermissaoIndividual($user, $permission, null, $admin);
        PermissaoService::revogarPermissaoIndividual($user, $permission);

        $this->assertFalse($user->permissions()->where('permission_id', $permission->id)->exists());
    }

    public function test_permissoes_do_usuario_merge_role_e_individual(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createUserWithRole('fiscal_contrato');
        $permission = Permission::where('nome', 'usuario.criar')->first();

        PermissaoService::atribuirPermissaoIndividual($user, $permission, null, $admin);

        $permissoes = PermissaoService::permissoesDoUsuario($user);

        $this->assertContains('usuario.criar', $permissoes->toArray());
        $this->assertContains('contrato.visualizar', $permissoes->toArray());
    }

    public function test_sincronizar_permissoes_role(): void
    {
        $role = Role::factory()->create(['nome' => 'teste_sync']);
        $p1 = Permission::where('nome', 'contrato.visualizar')->first();
        $p2 = Permission::where('nome', 'contrato.criar')->first();

        PermissaoService::sincronizarPermissoesRole($role, [$p1->id, $p2->id]);

        $this->assertEquals(2, $role->permissions()->count());
    }
}
