<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantCreateCommand extends Command
{
    protected $signature = 'tenant:create
        {nome : Nome da prefeitura}
        {slug : Slug unico para o subdominio}';

    protected $description = 'Provisiona um novo tenant: cria banco, aplica migrations e registra no master';

    public function handle(): int
    {
        $nome = $this->argument('nome');
        $slug = Str::slug($this->argument('slug'));
        $databaseName = 'vigiacontratos_' . str_replace('-', '_', $slug);

        if (Tenant::where('slug', $slug)->exists()) {
            $this->error("Tenant com slug '{$slug}' já existe.");
            return Command::FAILURE;
        }

        if (Tenant::where('database_name', $databaseName)->exists()) {
            $this->error("Banco '{$databaseName}' já registrado.");
            return Command::FAILURE;
        }

        $this->info("Criando tenant: {$nome} ({$slug})...");

        $this->info("  Criando banco de dados: {$databaseName}...");
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $tenant = Tenant::create([
            'nome' => $nome,
            'slug' => $slug,
            'database_name' => $databaseName,
            'is_ativo' => true,
            'plano' => 'basico',
        ]);

        $this->info("  Tenant registrado no master (ID: {$tenant->id}).");

        $this->info("  Aplicando migrations no banco do tenant...");
        config(['database.connections.tenant.database' => $databaseName]);
        DB::purge('tenant');

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        $this->info($this->laravel->make('Illuminate\Contracts\Console\Kernel')->output() ?: '    Migrations aplicadas.');

        $this->info("  Aplicando seeders no banco do tenant...");

        $seeders = ['RoleSeeder', 'TenantUserSeeder', 'SecretariaSeeder', 'FornecedorSeeder'];

        foreach ($seeders as $seeder) {
            Artisan::call('db:seed', [
                '--class' => $seeder,
                '--database' => 'tenant',
                '--force' => true,
            ]);
            $this->info("    {$seeder} aplicado.");
        }

        $this->info("Tenant '{$nome}' criado com sucesso!");
        $this->info("  Slug: {$slug}");
        $this->info("  Banco: {$databaseName}");
        $this->info("  URL: {$slug}." . config('app.domain'));

        return Command::SUCCESS;
    }
}
