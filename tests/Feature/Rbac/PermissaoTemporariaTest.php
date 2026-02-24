<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\User;
use App\Services\PermissaoService;
use Carbon\Carbon;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class PermissaoTemporariaTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;
    protected User $fiscal;
    protected Permission $permissao;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();

        $this->admin = $this->createAdminUser();
        $this->fiscal = $this->createUserWithRole('fiscal_contrato');

        // fiscal_contrato NAO tem 'usuario.criar' via role — base para testes
        $this->permissao = Permission::where('nome', 'usuario.criar')->first();
    }

    public function test_permissao_com_expires_at_futuro_concede_acesso(): void
    {
        PermissaoService::atribuirPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            Carbon::now()->addDays(7),
            $this->admin
        );

        $this->assertTrue($this->fiscal->hasPermission('usuario.criar'));
    }

    public function test_permissao_com_expires_at_passado_bloqueia_acesso_em_tempo_real(): void
    {
        PermissaoService::atribuirPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            Carbon::now()->subDay(),
            $this->admin
        );

        // Verificacao em tempo real no hasPermission() — sem rodar o command
        $this->assertFalse($this->fiscal->hasPermission('usuario.criar'));
    }

    public function test_permissao_sem_expires_at_e_permanente(): void
    {
        PermissaoService::atribuirPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            null,
            $this->admin
        );

        $this->assertTrue($this->fiscal->hasPermission('usuario.criar'));
    }

    public function test_atribuir_permissao_temporaria_salva_expires_at_no_banco(): void
    {
        $expiresAt = Carbon::now()->addDays(30);

        PermissaoService::atribuirPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            $expiresAt,
            $this->admin
        );

        $this->assertDatabaseHas('user_permissions', [
            'user_id'       => $this->fiscal->id,
            'permission_id' => $this->permissao->id,
        ], 'tenant');

        $pivot = $this->fiscal->permissions()
            ->where('permission_id', $this->permissao->id)
            ->first();

        $this->assertNotNull($pivot);
        $this->assertNotNull($pivot->pivot->expires_at);
    }

    public function test_command_remove_permissoes_expiradas(): void
    {
        PermissaoService::atribuirPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            Carbon::now()->subHour(),
            $this->admin
        );

        $this->assertDatabaseHas('user_permissions', [
            'user_id'       => $this->fiscal->id,
            'permission_id' => $this->permissao->id,
        ], 'tenant');

        $this->artisan('permissoes:verificar-expiradas')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('user_permissions', [
            'user_id'       => $this->fiscal->id,
            'permission_id' => $this->permissao->id,
        ], 'tenant');
    }

    public function test_command_nao_remove_permissoes_validas(): void
    {
        PermissaoService::atribuirPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            Carbon::now()->addDays(5),
            $this->admin
        );

        $this->artisan('permissoes:verificar-expiradas')
            ->assertExitCode(0);

        $this->assertDatabaseHas('user_permissions', [
            'user_id'       => $this->fiscal->id,
            'permission_id' => $this->permissao->id,
        ], 'tenant');
    }

    public function test_command_nao_remove_permissoes_sem_expiracao(): void
    {
        PermissaoService::atribuirPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            null,
            $this->admin
        );

        $this->artisan('permissoes:verificar-expiradas')
            ->assertExitCode(0);

        $this->assertDatabaseHas('user_permissions', [
            'user_id'       => $this->fiscal->id,
            'permission_id' => $this->permissao->id,
        ], 'tenant');
    }

    public function test_usuario_mantem_permissao_role_mesmo_com_individual_expirada(): void
    {
        // fiscal_contrato tem 'contrato.visualizar' via role
        PermissaoService::atribuirPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            Carbon::now()->subDay(),
            $this->admin
        );

        // Permissao do role nao e afetada pela expiracao da individual
        $this->assertTrue($this->fiscal->hasPermission('contrato.visualizar'));
        // Permissao individual expirada nao concede acesso
        $this->assertFalse($this->fiscal->hasPermission('usuario.criar'));
    }
}
