<?php

namespace Tests\Feature\Commands;

use App\Models\Tenant;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AgregarDashboardCommandTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();

        // Remover todos os tenants existentes para isolar os testes
        Tenant::query()->delete();
    }

    public function test_command_sem_tenants_ativos_exibe_aviso(): void
    {
        $this->artisan('dashboard:agregar')
            ->expectsOutput('Nenhum tenant ativo encontrado.')
            ->assertExitCode(0);
    }

    public function test_command_com_tenant_ativo_executa_com_sucesso(): void
    {
        $dbName = config('database.connections.mysql.database');
        Tenant::create([
            'nome' => 'Tenant Dashboard',
            'slug' => 'tenant-dashboard-' . uniqid(),
            'database_name' => $dbName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('dashboard:agregar')
            ->expectsOutputToContain('Agregando dados do dashboard para 1 tenant(s)')
            ->assertExitCode(0);
    }

    public function test_command_ignora_tenants_inativos(): void
    {
        Tenant::create([
            'nome' => 'Tenant Inativo',
            'slug' => 'tenant-inativo-dash-' . uniqid(),
            'database_name' => 'db_inativo_' . uniqid(),
            'is_ativo' => false,
            'plano' => 'basico',
        ]);

        $this->artisan('dashboard:agregar')
            ->expectsOutput('Nenhum tenant ativo encontrado.')
            ->assertExitCode(0);
    }

    public function test_command_exibe_resumo_de_resultados(): void
    {
        $dbName = config('database.connections.mysql.database');
        Tenant::create([
            'nome' => 'Tenant Resumo',
            'slug' => 'tenant-resumo-dash-' . uniqid(),
            'database_name' => $dbName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('dashboard:agregar')
            ->expectsOutputToContain('Resumo:')
            ->assertExitCode(0);
    }
}
