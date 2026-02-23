<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected ?Tenant $tenant = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureTenantConnection();
    }

    /**
     * Redireciona a conexao 'tenant' para o banco 'testing',
     * permitindo que Models com $connection = 'tenant' funcionem nos testes.
     */
    protected function configureTenantConnection(): void
    {
        $mysqlConfig = config('database.connections.mysql');

        config([
            'database.connections.tenant.database' => $mysqlConfig['database'],
            'database.connections.tenant.host' => $mysqlConfig['host'],
            'database.connections.tenant.port' => $mysqlConfig['port'],
            'database.connections.tenant.username' => $mysqlConfig['username'],
            'database.connections.tenant.password' => $mysqlConfig['password'],
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    /**
     * Cria e registra um Tenant fake no container da aplicacao.
     * Necessario para testes que dependem de app('tenant').
     */
    protected function setUpTenant(): Tenant
    {
        $this->tenant = Tenant::factory()->create([
            'slug' => 'testing',
            'database_name' => config('database.connections.mysql.database'),
            'is_ativo' => true,
        ]);

        app()->instance('tenant', $this->tenant);

        return $this->tenant;
    }
}
