<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantService
{
    public function createTenant(array $data): Tenant
    {
        $slug = Str::slug($data['slug']);
        $databaseName = 'vigiacontratos_' . str_replace('-', '_', $slug);

        DB::statement(
            "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        $tenant = Tenant::create([
            'nome' => $data['nome'],
            'slug' => $slug,
            'database_name' => $databaseName,
            'database_host' => $data['database_host'] ?? null,
            'is_ativo' => true,
            'plano' => $data['plano'] ?? 'basico',
        ]);

        $this->configureTenantConnection($tenant);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        $seeders = [
            'RoleSeeder',
            'PermissionSeeder',
            'RolePermissionSeeder',
            'TenantUserSeeder',
            'SecretariaSeeder',
            'FornecedorSeeder',
            'ServidorSeeder',
            'ConfiguracaoLimiteAditivoSeeder',
            'ConfiguracaoAlertaSeeder',
            'ConfiguracaoChecklistDocumentoSeeder',
            'ContratoSeeder',
            'DocumentoSeeder',
            'AditivoSeeder',
            'ExecucaoFinanceiraSeeder',
            'OcorrenciaRelatorioFiscalSeeder',
            'ContratoConformidadeFaseSeeder',
        ];

        foreach ($seeders as $seeder) {
            Artisan::call('db:seed', [
                '--class' => $seeder,
                '--database' => 'tenant',
                '--force' => true,
            ]);
        }

        return $tenant;
    }

    public function activateTenant(Tenant $tenant): void
    {
        $tenant->update(['is_ativo' => true]);
    }

    public function deactivateTenant(Tenant $tenant): void
    {
        $tenant->update(['is_ativo' => false]);
    }

    public function configureTenantConnection(Tenant $tenant): void
    {
        config([
            'database.connections.tenant.database' => $tenant->database_name,
            'database.connections.tenant.host' => $tenant->database_host
                ?? config('database.connections.tenant.host'),
        ]);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
