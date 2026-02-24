<?php

namespace Tests\Feature\Commands;

use App\Models\Tenant;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class MigrarStorageLegadoCommandTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_dry_run_exibe_modo_simulacao(): void
    {
        // Command com DB::purge quebra transacao — testar via output
        $this->artisan('storage:migrar-legado', ['--dry-run' => true])
            ->expectsOutputToContain('DRY-RUN')
            ->assertSuccessful();
    }

    public function test_command_processa_tenant_ativo(): void
    {
        $tenant = Tenant::where('is_ativo', true)->first();

        $this->artisan('storage:migrar-legado', ['--dry-run' => true])
            ->expectsOutputToContain($tenant->slug)
            ->assertSuccessful();
    }

    public function test_tenant_especifico_filtra_corretamente(): void
    {
        $tenant = Tenant::where('is_ativo', true)->first();

        $this->artisan('storage:migrar-legado', [
            '--tenant' => $tenant->slug,
            '--dry-run' => true,
        ])
            ->expectsOutputToContain($tenant->slug)
            ->assertSuccessful();
    }

    public function test_tenant_inexistente_retorna_aviso(): void
    {
        $this->artisan('storage:migrar-legado', [
            '--tenant' => 'tenant-inexistente-xyz',
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('Nenhum tenant ativo')
            ->assertSuccessful();
    }
}
