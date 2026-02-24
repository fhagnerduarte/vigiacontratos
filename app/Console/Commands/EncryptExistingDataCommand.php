<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EncryptExistingDataCommand extends Command
{
    protected $signature = 'data:encrypt-existing
        {--tenant= : Slug do tenant especifico}
        {--all-tenants : Processar todos os tenants ativos}
        {--dry-run : Apenas simular sem alterar dados}';

    protected $description = 'Criptografa dados sensiveis existentes em plaintext (Fornecedor, Fiscal)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Modo DRY-RUN ativado — nenhum dado sera alterado.');
        }

        $tenants = $this->resolverTenants();

        if ($tenants->isEmpty()) {
            $this->error('Nenhum tenant encontrado.');

            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            $this->info("Processando tenant: {$tenant->nome} ({$tenant->slug})");
            $this->configurarTenant($tenant);

            $this->encryptarFornecedores($dryRun);
            $this->encryptarFiscais($dryRun);

            $this->info("LoginLog/AdminLoginLog: tabelas imutaveis — apenas novos registros serao criptografados.\n");
        }

        $this->info($dryRun ? 'Simulacao concluida.' : 'Criptografia concluida com sucesso.');

        return self::SUCCESS;
    }

    private function resolverTenants()
    {
        if ($slug = $this->option('tenant')) {
            $tenant = Tenant::where('slug', $slug)->where('is_ativo', true)->first();

            return $tenant ? collect([$tenant]) : collect();
        }

        if ($this->option('all-tenants')) {
            return Tenant::where('is_ativo', true)->get();
        }

        $this->error('Informe --tenant=<slug> ou --all-tenants');

        return collect();
    }

    private function configurarTenant(Tenant $tenant): void
    {
        config([
            'database.connections.tenant.database' => $tenant->database_name,
            'database.connections.tenant.host' => $tenant->database_host ?? config('database.connections.tenant.host'),
        ]);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function encryptarFornecedores(bool $dryRun): void
    {
        $total = 0;
        $skipped = 0;
        $campos = ['email', 'telefone', 'representante_legal'];

        DB::connection('tenant')->table('fornecedores')
            ->orderBy('id')
            ->chunk(100, function ($fornecedores) use ($dryRun, &$total, &$skipped, $campos) {
                foreach ($fornecedores as $row) {
                    if ($this->isEncrypted($row->email)) {
                        $skipped++;

                        continue;
                    }

                    if (! $dryRun) {
                        $updates = [];
                        foreach ($campos as $campo) {
                            if (! empty($row->$campo) && ! $this->isEncrypted($row->$campo)) {
                                $updates[$campo] = Crypt::encryptString($row->$campo);
                            }
                        }

                        if (! empty($updates)) {
                            DB::connection('tenant')->table('fornecedores')
                                ->where('id', $row->id)
                                ->update($updates);
                        }
                    }

                    $total++;
                }
            });

        $this->line("  Fornecedores: {$total} criptografados, {$skipped} ja estavam criptografados.");
    }

    private function encryptarFiscais(bool $dryRun): void
    {
        $total = 0;
        $skipped = 0;

        DB::connection('tenant')->table('fiscais')
            ->orderBy('id')
            ->chunk(100, function ($fiscais) use ($dryRun, &$total, &$skipped) {
                foreach ($fiscais as $row) {
                    if ($this->isEncrypted($row->email)) {
                        $skipped++;

                        continue;
                    }

                    if (! $dryRun) {
                        $updates = [];
                        if (! empty($row->email) && ! $this->isEncrypted($row->email)) {
                            $updates['email'] = Crypt::encryptString($row->email);
                        }

                        if (! empty($updates)) {
                            DB::connection('tenant')->table('fiscais')
                                ->where('id', $row->id)
                                ->update($updates);
                        }
                    }

                    $total++;
                }
            });

        $this->line("  Fiscais: {$total} criptografados, {$skipped} ja estavam criptografados.");
    }

    private function isEncrypted(?string $value): bool
    {
        if (empty($value)) {
            return true;
        }

        // Valores criptografados pelo Laravel sao base64-encoded JSON que comeca com 'eyJ'
        return str_starts_with($value, 'eyJ');
    }
}
