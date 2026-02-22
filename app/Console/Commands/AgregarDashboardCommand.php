<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\DashboardService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgregarDashboardCommand extends Command
{
    protected $signature = 'dashboard:agregar';

    protected $description = 'Agrega dados do dashboard executivo para todos os tenants ativos (RN-084, ADR-019/021)';

    public function handle(): int
    {
        $tenants = Tenant::where('is_ativo', true)->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant ativo encontrado.');

            return Command::SUCCESS;
        }

        $this->info("Agregando dados do dashboard para {$tenants->count()} tenant(s)...");

        $totalSucesso = 0;
        $failed = [];

        foreach ($tenants as $tenant) {
            $this->info("  [{$tenant->slug}] {$tenant->database_name}...");

            try {
                // Configurar conexao tenant
                config([
                    'database.connections.tenant.database' => $tenant->database_name,
                    'database.connections.tenant.host' => $tenant->database_host
                        ?? config('database.connections.tenant.host'),
                ]);
                DB::purge('tenant');
                DB::reconnect('tenant');

                // Agregar dados
                $agregado = DashboardService::agregar();

                $totalSucesso++;

                $this->info("    Score gestao: {$agregado->score_gestao} | Contratos ativos: {$agregado->total_contratos_ativos} | Risco alto: {$agregado->risco_alto}");
            } catch (\Throwable $e) {
                $this->error("    FALHA: {$e->getMessage()}");
                Log::error("Falha ao agregar dashboard para tenant {$tenant->slug}", [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $failed[] = $tenant->slug;
            }
        }

        $this->newLine();
        $this->info("Resumo: {$totalSucesso} tenant(s) agregado(s) com sucesso.");

        if (! empty($failed)) {
            $this->error('Tenants com falha: ' . implode(', ', $failed));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
