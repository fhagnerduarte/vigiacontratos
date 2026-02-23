<?php

namespace Tests\Feature\Commands;

use App\Jobs\VerificarIntegridadeDocumentoBatch;
use App\Models\Documento;
use App\Models\Tenant;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class VerificarIntegridadeDocumentosCommandTest extends TestCase
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
        $this->artisan('documentos:verificar-integridade')
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

        $this->artisan('documentos:verificar-integridade')
            ->expectsOutputToContain('Verificando integridade em 1 tenant(s)')
            ->assertExitCode(0);
    }

    public function test_command_filtra_por_tenant_especifico(): void
    {
        Queue::fake();

        $dbName = config('database.connections.mysql.database');
        $slug = 'prefeitura-filtro-' . uniqid();

        Tenant::create([
            'nome' => 'Prefeitura Filtro',
            'slug' => $slug,
            'database_name' => $dbName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('documentos:verificar-integridade', [
            '--tenant' => $slug,
        ])
            ->expectsOutputToContain("[$slug]")
            ->assertExitCode(0);
    }

    public function test_command_tenant_inexistente_exibe_aviso(): void
    {
        $this->artisan('documentos:verificar-integridade', [
            '--tenant' => 'slug-inexistente',
        ])
            ->expectsOutput('Nenhum tenant ativo encontrado.')
            ->assertExitCode(0);
    }

    public function test_command_ignora_tenants_inativos(): void
    {
        Queue::fake();

        Tenant::create([
            'nome' => 'Tenant Inativo',
            'slug' => 'tenant-inativo-' . uniqid(),
            'database_name' => 'db_inativo_' . uniqid(),
            'is_ativo' => false,
            'plano' => 'basico',
        ]);

        $this->artisan('documentos:verificar-integridade')
            ->expectsOutput('Nenhum tenant ativo encontrado.')
            ->assertExitCode(0);
    }

    public function test_command_despacha_jobs_na_queue_integridade(): void
    {
        Queue::fake();

        $dbName = config('database.connections.mysql.database');
        $slug = 'tenant-queue-' . uniqid();

        Tenant::create([
            'nome' => 'Tenant Queue',
            'slug' => $slug,
            'database_name' => $dbName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        // Criar documentos com hash (precisam ser committed para o command ver)
        Documento::factory()->count(3)->create([
            'hash_integridade' => hash('sha256', 'conteudo'),
        ]);

        $this->artisan('documentos:verificar-integridade', [
            '--tenant' => $slug,
        ])->assertExitCode(0);

        // Verificar que jobs foram despachados com dados corretos
        Queue::assertPushed(VerificarIntegridadeDocumentoBatch::class, function ($job) use ($dbName, $slug) {
            return $job->tenantDatabaseName === $dbName
                && $job->tenantSlug === $slug
                && $job->queue === 'integridade';
        });
    }

    public function test_command_exibe_mensagem_sucesso(): void
    {
        Queue::fake();

        $dbName = config('database.connections.mysql.database');
        Tenant::create([
            'nome' => 'Tenant Sucesso',
            'slug' => 'tenant-sucesso-' . uniqid(),
            'database_name' => $dbName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->artisan('documentos:verificar-integridade')
            ->expectsOutput('Verificacao de integridade concluida com sucesso.')
            ->assertExitCode(0);
    }

    public function test_job_batch_possui_propriedades_corretas(): void
    {
        $job = new VerificarIntegridadeDocumentoBatch(
            [1, 2, 3],
            'db_teste',
            'slug-teste'
        );

        $this->assertEquals([1, 2, 3], $job->documentoIds);
        $this->assertEquals('db_teste', $job->tenantDatabaseName);
        $this->assertEquals('slug-teste', $job->tenantSlug);
        $this->assertEquals(3, $job->tries);
        $this->assertEquals([120, 300, 600], $job->backoff());
    }
}
