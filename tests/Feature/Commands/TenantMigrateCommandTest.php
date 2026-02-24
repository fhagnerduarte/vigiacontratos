<?php

namespace Tests\Feature\Commands;

use App\Models\Tenant;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class TenantMigrateCommandTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();

        // Remover todos os tenants existentes para isolar
        Tenant::query()->delete();
    }

    public function test_sem_tenants_ativos_exibe_aviso(): void
    {
        $this->artisan('tenant:migrate')
            ->expectsOutput('Nenhum tenant ativo encontrado.')
            ->assertExitCode(0);
    }

    public function test_com_tenant_ativo_aplica_migrations(): void
    {
        $dbName = config('database.connections.mysql.database');
        Tenant::create([
            'nome' => 'Tenant Migrate',
            'slug' => 'tenant-migrate-' . uniqid(),
            'database_name' => $dbName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('tenant:migrate')
            ->expectsOutputToContain('Aplicando migrations em 1 tenant(s)')
            ->assertExitCode(0);
    }

    public function test_ignora_tenants_inativos(): void
    {
        Tenant::create([
            'nome' => 'Tenant Inativo',
            'slug' => 'tenant-inativo-mig-' . uniqid(),
            'database_name' => 'db_inativo_' . uniqid(),
            'is_ativo' => false,
            'plano' => 'basico',
        ]);

        $this->artisan('tenant:migrate')
            ->expectsOutput('Nenhum tenant ativo encontrado.')
            ->assertExitCode(0);
    }

    public function test_tenant_com_falha_retorna_failure(): void
    {
        Tenant::create([
            'nome' => 'Tenant Falha',
            'slug' => 'tenant-falha-' . uniqid(),
            'database_name' => 'banco_inexistente_' . uniqid(),
            'database_host' => 'host-inexistente-' . uniqid(),
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('tenant:migrate')
            ->expectsOutputToContain('FALHA')
            ->assertExitCode(1);
    }
}
