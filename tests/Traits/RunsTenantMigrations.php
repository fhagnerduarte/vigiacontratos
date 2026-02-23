<?php

namespace Tests\Traits;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait RunsTenantMigrations
{
    use DatabaseTransactions;

    protected static bool $migrationsRan = false;

    protected function setUpTraits(): array
    {
        $this->ensureTenantConnectionConfigured();

        if (! static::$migrationsRan) {
            $this->runAllMigrations();
            static::$migrationsRan = true;
        }

        return parent::setUpTraits();
    }

    protected function ensureTenantConnectionConfigured(): void
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
     * Dropa todas as tabelas, roda migrations master, remove tabelas conflitantes
     * (users/sessions/password_reset_tokens do Laravel default), e roda migrations tenant.
     */
    protected function runAllMigrations(): void
    {
        Schema::connection('mysql')->dropAllTables();

        // Master: cria tenants, admin_users, admin_login_logs, cache, jobs, users (default Laravel)
        Artisan::call('migrate', [
            '--database' => 'mysql',
            '--force' => true,
        ]);

        // Remove tabelas do master que conflitam com as tenant (schema diferente)
        Schema::connection('mysql')->dropIfExists('password_reset_tokens');
        Schema::connection('mysql')->dropIfExists('sessions');
        Schema::connection('mysql')->dropIfExists('users');

        // Tenant: cria users (com 'nome'), roles, secretarias, contratos, etc.
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
        ]);
    }

    protected function connectionsToTransact(): array
    {
        return ['mysql', 'tenant'];
    }
}
