<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\PublicacaoAutomaticaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublicarAutomaticoCommand extends Command
{
    protected $signature = 'lai:publicar-automatico';

    protected $description = 'Publica automaticamente contratos publicos no portal de transparencia (LAI art. 8)';

    public function handle(): int
    {
        $tenants = Tenant::where('is_ativo', true)->get();

        if ($tenants->isEmpty()) {
            $this->info('Nenhum tenant ativo encontrado.');

            return self::SUCCESS;
        }

        $totalPublicados = 0;

        foreach ($tenants as $tenant) {
            $this->info("Processando tenant: {$tenant->nome} ({$tenant->slug})");

            config([
                'database.connections.tenant.database' => $tenant->database_name,
                'database.connections.tenant.host' => $tenant->database_host ?? config('database.connections.tenant.host'),
            ]);

            DB::purge('tenant');
            DB::reconnect('tenant');

            $resultado = PublicacaoAutomaticaService::publicar();
            $totalPublicados += $resultado['publicados'];

            $this->info("  → {$resultado['publicados']} contrato(s) publicado(s), {$resultado['ja_publicados']} ja publicado(s)");
        }

        $this->info("Total publicados: {$totalPublicados}");

        return self::SUCCESS;
    }
}
