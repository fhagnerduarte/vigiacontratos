<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\ClassificacaoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerificarDesclassificacaoCommand extends Command
{
    protected $signature = 'lai:verificar-desclassificacao';

    protected $description = 'Verifica e desclassifica contratos cujo prazo de sigilo expirou (LAI art. 24)';

    public function handle(): int
    {
        $tenants = Tenant::where('is_ativo', true)->get();

        if ($tenants->isEmpty()) {
            $this->info('Nenhum tenant ativo encontrado.');

            return self::SUCCESS;
        }

        $totalDesclassificados = 0;

        foreach ($tenants as $tenant) {
            $this->info("Processando tenant: {$tenant->nome} ({$tenant->slug})");

            config([
                'database.connections.tenant.database' => $tenant->database_name,
                'database.connections.tenant.host' => $tenant->database_host ?? config('database.connections.tenant.host'),
            ]);

            DB::purge('tenant');
            DB::reconnect('tenant');

            $desclassificados = ClassificacaoService::verificarDesclassificacaoAutomatica();
            $totalDesclassificados += $desclassificados;

            if ($desclassificados > 0) {
                $this->info("  → {$desclassificados} contrato(s) desclassificado(s)");
            }
        }

        $this->info("Total desclassificados: {$totalDesclassificados}");

        return self::SUCCESS;
    }
}
