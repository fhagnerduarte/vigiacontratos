<?php

namespace App\Console\Commands;

use App\Models\Documento;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MigrarStorageLegadoCommand extends Command
{
    protected $signature = 'storage:migrar-legado
        {--tenant= : Slug do tenant especifico}
        {--dry-run : Simular sem mover arquivos}';

    protected $description = 'Migra documentos com path legado (sem prefixo tenant) para o formato isolado por tenant';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $tenantSlug = $this->option('tenant');

        if ($dryRun) {
            $this->warn('Modo DRY-RUN: nenhum arquivo sera movido.');
        }

        $query = Tenant::where('is_ativo', true);
        if ($tenantSlug) {
            $query->where('slug', $tenantSlug);
        }
        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant ativo encontrado.');

            return Command::SUCCESS;
        }

        $totalMigrados = 0;
        $totalErros = 0;
        $totalJaCorretos = 0;

        foreach ($tenants as $tenant) {
            $this->info("Processando tenant [{$tenant->slug}]...");

            try {
                config([
                    'database.connections.tenant.database' => $tenant->database_name,
                    'database.connections.tenant.host' => $tenant->database_host ?? config('database.connections.tenant.host'),
                ]);
                DB::purge('tenant');
                DB::reconnect('tenant');

                // Buscar documentos com path legado (sem prefixo tenant)
                $documentosLegados = Documento::on('tenant')
                    ->where('caminho', 'like', 'documentos/%')
                    ->whereNull('deleted_at')
                    ->get();

                if ($documentosLegados->isEmpty()) {
                    $this->info("  Nenhum documento legado encontrado.");

                    continue;
                }

                $this->info("  Encontrados {$documentosLegados->count()} documento(s) legado(s).");

                foreach ($documentosLegados as $documento) {
                    $caminhoAntigo = $documento->caminho;
                    $caminhoNovo = "{$tenant->slug}/{$caminhoAntigo}";

                    if ($dryRun) {
                        $this->line("    [DRY-RUN] {$caminhoAntigo} → {$caminhoNovo}");
                        $totalMigrados++;

                        continue;
                    }

                    try {
                        $disk = Storage::disk('local');

                        if (!$disk->exists($caminhoAntigo)) {
                            $this->warn("    Arquivo nao encontrado: {$caminhoAntigo}");
                            $totalErros++;

                            continue;
                        }

                        // Copiar arquivo para novo caminho
                        $disk->copy($caminhoAntigo, $caminhoNovo);

                        // Atualizar caminho no banco via query builder (bypass model immutability triggers)
                        DB::connection('tenant')
                            ->table('documentos')
                            ->where('id', $documento->id)
                            ->update(['caminho' => $caminhoNovo]);

                        // Remover arquivo antigo apos confirmacao
                        $disk->delete($caminhoAntigo);

                        $this->line("    Migrado: {$caminhoAntigo} → {$caminhoNovo}");
                        $totalMigrados++;

                        Log::info('Storage legado: documento migrado', [
                            'documento_id' => $documento->id,
                            'caminho_antigo' => $caminhoAntigo,
                            'caminho_novo' => $caminhoNovo,
                            'tenant' => $tenant->slug,
                        ]);
                    } catch (\Throwable $e) {
                        $this->error("    Erro ao migrar documento #{$documento->id}: {$e->getMessage()}");
                        $totalErros++;

                        Log::error('Storage legado: falha na migracao', [
                            'documento_id' => $documento->id,
                            'caminho' => $caminhoAntigo,
                            'tenant' => $tenant->slug,
                            'erro' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                $this->error("  Falha ao conectar ao tenant [{$tenant->slug}]: {$e->getMessage()}");
                $totalErros++;
            }
        }

        $this->newLine();
        $this->info("Resultado: {$totalMigrados} migrado(s), {$totalErros} erro(s).");

        if ($dryRun) {
            $this->warn('Execute sem --dry-run para aplicar as migracoes.');
        }

        return $totalErros > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
