<?php

namespace Tests\Feature\Commands;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class VerificarPermissoesExpiradasCommandTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_sem_permissoes_expiradas_exibe_mensagem(): void
    {
        // Limpar permissoes individuais expiradas antes
        DB::connection('tenant')
            ->table('user_permissions')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $this->artisan('permissoes:verificar-expiradas')
            ->expectsOutput('Nenhuma permissao expirada encontrada.')
            ->assertExitCode(0);
    }

    public function test_com_permissoes_expiradas_remove_e_exibe_contagem(): void
    {
        $user = $this->createAdminUser();
        $permission = Permission::first();

        // Inserir permissao expirada diretamente
        DB::connection('tenant')->table('user_permissions')->insert([
            'user_id' => $user->id,
            'permission_id' => $permission->id,
            'expires_at' => now()->subDay(),
            'concedido_por' => $user->id,
            'created_at' => now(),
        ]);

        $this->artisan('permissoes:verificar-expiradas')
            ->expectsOutputToContain('Removidas 1 permissao(oes) expirada(s)')
            ->assertExitCode(0);
    }

    public function test_nao_remove_permissoes_nao_expiradas(): void
    {
        $user = $this->createAdminUser();
        $permission = Permission::first();

        // Inserir permissao que ainda nao expirou
        DB::connection('tenant')->table('user_permissions')->insert([
            'user_id' => $user->id,
            'permission_id' => $permission->id,
            'expires_at' => now()->addWeek(),
            'concedido_por' => $user->id,
            'created_at' => now(),
        ]);

        // Limpar eventuais expiradas de outros testes
        DB::connection('tenant')
            ->table('user_permissions')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $this->artisan('permissoes:verificar-expiradas')
            ->expectsOutput('Nenhuma permissao expirada encontrada.')
            ->assertExitCode(0);

        // Verificar que a permissao nao-expirada continua
        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $user->id,
            'permission_id' => $permission->id,
        ], 'tenant');
    }
}
