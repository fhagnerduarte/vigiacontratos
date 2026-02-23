<?php

namespace App\Console\Commands;

use App\Jobs\VerificarIntegridadeDocumentoBatch;
use App\Models\Documento;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarIntegridadeDocumentosCommand extends Command
{
    protected $signature = 'documentos:verificar-integridade
                            {--tenant= : Slug do tenant especifico (opcional, executa em todos se omitido)}
                            {--force : Reverificar documentos ja verificados}';

    protected $description = 'Verifica a integridade SHA-256 de todos os documentos ativos em batches de 100 (RN-221)';

    public function handle(): int
    {
        $tenantQuery = Tenant::where('is_ativo', true);

        if ($this->option('tenant')) {
            $tenantQuery->where('slug', $this->option('tenant'));
        }

        $tenants = $tenantQuery->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant ativo encontrado.');

            return Command::SUCCESS;
        }

        $this->info("Verificando integridade em {$tenants->count()} tenant(s)...");
        $failed = [];

        foreach ($tenants as $tenant) {
            $this->info("  [{$tenant->slug}] {$tenant->database_name}...");

            try {
                config([
                    'database.connections.tenant.database' => $tenant->database_name,
                    'database.connections.tenant.host' => $tenant->database_host
                        ?? config('database.connections.tenant.host'),
                ]);
                DB::purge('tenant');
                DB::reconnect('tenant');

                $ids = Documento::whereNotNull('hash_integridade')
                    ->whereNull('deleted_at')
                    ->pluck('id')
                    ->toArray();

                $total = count($ids);
                $batches = array_chunk($ids, 100);
                $totalBatches = count($batches);

                $this->info("    {$total} documento(s) — {$totalBatches} batch(es)...");

                foreach ($batches as $batchIds) {
                    VerificarIntegridadeDocumentoBatch::dispatch(
                        $batchIds,
                        $tenant->database_name,
                        $tenant->slug
                    );
                }

                $this->info("    {$totalBatches} job(s) despachados com sucesso.");
            } catch (\Throwable $e) {
                $this->error("    FALHA: {$e->getMessage()}");
                Log::error("Falha ao iniciar verificacao de integridade para tenant {$tenant->slug}", [
                    'exception' => $e->getMessage(),
                ]);
                $failed[] = $tenant->slug;
            }
        }

        if (! empty($failed)) {
            $this->error('Tenants com falha: ' . implode(', ', $failed));

            return Command::FAILURE;
        }

        $this->info('Verificacao de integridade concluida com sucesso.');

        return Command::SUCCESS;
    }
}
