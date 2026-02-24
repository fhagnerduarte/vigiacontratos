<?php

namespace Tests\Feature\Commands;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class VerificarPermissoesExpiradasAuditoriaTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->admin = $this->createAdminUser();
    }

    private function countAuditoriaPermissaoExpirada(): int
    {
        return DB::connection('tenant')
            ->table('historico_alteracoes')
            ->where('campo_alterado', 'permissao_expirada')
            ->count();
    }

    public function test_command_loga_permissao_expirada_no_historico_alteracoes(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');
        $permission = Permission::where('nome', 'contrato.editar')->first();

        $user->permissions()->attach($permission->id, [
            'expires_at' => now()->subDay(),
            'concedido_por' => $this->admin->id,
            'created_at' => now()->subWeek(),
        ]);

        $this->artisan('permissoes:verificar-expiradas')
            ->assertExitCode(0);

        $this->assertDatabaseHas('historico_alteracoes', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'campo_alterado' => 'permissao_expirada',
            'valor_anterior' => $permission->nome,
        ], 'tenant');
    }

    public function test_command_sem_expiradas_nao_cria_auditoria(): void
    {
        // Limpar eventuais permissoes expiradas pre-existentes
        DB::connection('tenant')
            ->table('user_permissions')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $countAntes = $this->countAuditoriaPermissaoExpirada();

        $this->artisan('permissoes:verificar-expiradas')
            ->assertExitCode(0);

        $countDepois = $this->countAuditoriaPermissaoExpirada();

        $this->assertEquals($countAntes, $countDepois, 'Nenhum registro de auditoria deveria ser criado quando nao ha permissoes expiradas');
    }

    public function test_command_loga_multiplas_permissoes_expiradas(): void
    {
        $countAntes = $this->countAuditoriaPermissaoExpirada();

        $permissions = Permission::take(3)->get();

        foreach ($permissions as $index => $permission) {
            $user = $this->createUserWithRole('fiscal_contrato');
            $user->permissions()->attach($permission->id, [
                'expires_at' => now()->subDays($index + 1),
                'concedido_por' => $this->admin->id,
                'created_at' => now()->subWeek(),
            ]);
        }

        $this->artisan('permissoes:verificar-expiradas')
            ->assertExitCode(0);

        $countDepois = $this->countAuditoriaPermissaoExpirada();

        $this->assertEquals(3, $countDepois - $countAntes, 'Deveria criar exatamente 3 registros de auditoria para 3 permissoes expiradas');
    }

    public function test_auditoria_registra_nome_da_permissao_revogada(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');
        $permission = Permission::where('nome', 'contrato.editar')->first();

        $user->permissions()->attach($permission->id, [
            'expires_at' => now()->subDay(),
            'concedido_por' => $this->admin->id,
            'created_at' => now()->subWeek(),
        ]);

        $this->artisan('permissoes:verificar-expiradas')
            ->assertExitCode(0);

        $this->assertDatabaseHas('historico_alteracoes', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'campo_alterado' => 'permissao_expirada',
            'valor_anterior' => 'contrato.editar',
            'valor_novo' => null,
            'role_nome' => 'sistema',
        ], 'tenant');
    }
}
