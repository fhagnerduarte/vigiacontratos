<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\AlertaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarVencimentosCommand extends Command
{
    protected $signature = 'alertas:verificar-vencimentos';

    protected $description = 'Verifica vencimentos de contratos e gera alertas automaticamente (RN-014, RN-044)';

    public function handle(): int
    {
        $tenants = Tenant::where('is_ativo', true)->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant ativo encontrado.');
            return Command::SUCCESS;
        }

        $this->info("Verificando vencimentos em {$tenants->count()} tenant(s)...");

        $totalAlertas = 0;
        $totalVencidos = 0;
        $totalReenvios = 0;
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

                // Executar motor de monitoramento
                $resultado = AlertaService::verificarVencimentos();

                $totalAlertas += $resultado['alertas_gerados'];
                $totalVencidos += $resultado['contratos_vencidos'];
                $totalReenvios += $resultado['notificacoes_reenvio'];

                $this->info("    Alertas: {$resultado['alertas_gerados']} | Vencidos: {$resultado['contratos_vencidos']} | Reenvios: {$resultado['notificacoes_reenvio']}");
            } catch (\Throwable $e) {
                $this->error("    FALHA: {$e->getMessage()}");
                Log::error("Falha ao verificar vencimentos para tenant {$tenant->slug}", [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $failed[] = $tenant->slug;
            }
        }

        $this->newLine();
        $this->info("Resumo: {$totalAlertas} alertas gerados, {$totalVencidos} contratos marcados vencidos, {$totalReenvios} reenvios.");

        if (!empty($failed)) {
            $this->error('Tenants com falha: ' . implode(', ', $failed));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
