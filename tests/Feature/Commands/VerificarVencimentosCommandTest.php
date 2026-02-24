<?php

namespace Tests\Feature\Commands;

use App\Models\Tenant;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class VerificarVencimentosCommandTest extends TestCase
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

    // ─── COMMAND: alertas:verificar-vencimentos ──────────────

    public function test_command_sem_tenants_ativos_exibe_aviso(): void
    {
        $this->artisan('alertas:verificar-vencimentos')
            ->expectsOutput('Nenhum tenant ativo encontrado.')
            ->assertExitCode(0);
    }

    public function test_command_com_tenant_ativo_executa_com_sucesso(): void
    {
        Queue::fake();

        $dbName = config('database.connections.mysql.database');
        Tenant::create([
            'nome' => 'Tenant Teste',
            'slug' => 'tenant-teste-' . uniqid(),
            'database_name' => $dbName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('alertas:verificar-vencimentos')
            ->expectsOutputToContain('Verificando vencimentos em 1 tenant(s)')
            ->assertExitCode(0);
    }

    public function test_command_ignora_tenants_inativos(): void
    {
        Tenant::create([
            'nome' => 'Tenant Inativo',
            'slug' => 'tenant-inativo-' . uniqid(),
            'database_name' => 'db_inativo_' . uniqid(),
            'is_ativo' => false,
            'plano' => 'basico',
        ]);

        $this->artisan('alertas:verificar-vencimentos')
            ->expectsOutput('Nenhum tenant ativo encontrado.')
            ->assertExitCode(0);
    }

    public function test_command_exibe_resumo_de_resultados(): void
    {
        Queue::fake();

        $dbName = config('database.connections.mysql.database');
        Tenant::create([
            'nome' => 'Tenant Resumo',
            'slug' => 'tenant-resumo-' . uniqid(),
            'database_name' => $dbName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('alertas:verificar-vencimentos')
            ->expectsOutputToContain('Resumo:')
            ->assertExitCode(0);
    }

    public function test_command_exibe_informacoes_de_cada_tenant(): void
    {
        Queue::fake();

        $dbName = config('database.connections.mysql.database');
        $slug = 'prefeitura-info-' . uniqid();

        Tenant::create([
            'nome' => 'Prefeitura Info',
            'slug' => $slug,
            'database_name' => $dbName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('alertas:verificar-vencimentos')
            ->expectsOutputToContain("[{$slug}]")
            ->assertExitCode(0);
    }
}
