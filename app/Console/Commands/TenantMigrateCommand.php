<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TenantMigrateCommand extends Command
{
    protected $signature = 'tenant:migrate
        {--seed : Executar seeders após migrations}
        {--fresh : Dropar e recriar tabelas (CUIDADO)}';

    protected $description = 'Aplica migrations pendentes em todos os tenants ativos';

    public function handle(): int
    {
        $tenants = Tenant::where('is_ativo', true)->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant ativo encontrado.');
            return Command::SUCCESS;
        }

        $this->info("Aplicando migrations em {$tenants->count()} tenant(s)...");

        $failed = [];

        foreach ($tenants as $tenant) {
            $this->info("  [{$tenant->slug}] {$tenant->database_name}...");

            try {
                config([
                    'database.connections.tenant.database' => $tenant->database_name,
                    'database.connections.tenant.host' => $tenant->database_host ?? config('database.connections.tenant.host'),
                ]);
                DB::purge('tenant');

                $command = $this->option('fresh') ? 'migrate:fresh' : 'migrate';
                Artisan::call($command, [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);

                if ($this->option('seed')) {
                    Artisan::call('db:seed', [
                        '--database' => 'tenant',
                        '--force' => true,
                    ]);
                }

                $this->info("    OK");
            } catch (\Throwable $e) {
                $this->error("    FALHA: {$e->getMessage()}");
                $failed[] = $tenant->slug;
            }
        }

        if (!empty($failed)) {
            $this->error('Tenants com falha: ' . implode(', ', $failed));
            return Command::FAILURE;
        }

        $this->info('Todas as migrations aplicadas com sucesso.');
        return Command::SUCCESS;
    }
}
