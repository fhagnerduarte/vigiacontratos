<?php

namespace App\Console\Commands;

use App\Models\Fiscal;
use App\Models\Fornecedor;
use App\Models\Servidor;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LGPDService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LgpdProcessarCommand extends Command
{
    protected $signature = 'lgpd:processar
        {--tenant= : Slug do tenant (obrigatorio para contexto de banco)}
        {--entidade= : Tipo de entidade (fornecedor, fiscal, servidor, usuario)}
        {--id= : ID especifico da entidade}
        {--solicitante= : Nome ou protocolo do solicitante (obrigatorio)}
        {--justificativa= : Justificativa da solicitacao}
        {--dry-run : Simular sem executar}';

    protected $description = 'Processa solicitacoes de anonimizacao LGPD (ADR-057, RN-213)';

    public function handle(): int
    {
        $solicitante = $this->option('solicitante');
        if (!$solicitante) {
            $this->error('A opcao --solicitante e obrigatoria.');

            return Command::FAILURE;
        }

        $entidadeTipo = $this->option('entidade');
        $entidadeId = $this->option('id');

        if (!$entidadeTipo || !$entidadeId) {
            $this->error('As opcoes --entidade e --id sao obrigatorias.');

            return Command::FAILURE;
        }

        $modelClass = match ($entidadeTipo) {
            'fornecedor' => Fornecedor::class,
            'fiscal' => Fiscal::class,
            'servidor' => Servidor::class,
            'usuario' => User::class,
            default => null,
        };

        if (!$modelClass) {
            $this->error("Entidade invalida: {$entidadeTipo}. Use: fornecedor, fiscal, servidor, usuario.");

            return Command::FAILURE;
        }

        // Configurar tenant se especificado
        $tenantSlug = $this->option('tenant');
        if ($tenantSlug) {
            $tenant = Tenant::where('slug', $tenantSlug)->where('is_ativo', true)->first();

            if (!$tenant) {
                $this->error("Tenant nao encontrado ou inativo: {$tenantSlug}");

                return Command::FAILURE;
            }

            $this->configurarTenant($tenant);
        }

        // Buscar entidade
        $entidade = $modelClass::find($entidadeId);

        if (!$entidade) {
            $this->error("{$entidadeTipo} #{$entidadeId} nao encontrado.");

            return Command::FAILURE;
        }

        // Verificar se ja foi anonimizada
        if (LGPDService::jaAnonimizado($entidade)) {
            $this->warn("{$entidadeTipo} #{$entidadeId} ja foi anonimizado anteriormente.");

            return Command::SUCCESS;
        }

        $dryRun = $this->option('dry-run');
        $justificativa = $this->option('justificativa');

        if ($dryRun) {
            $campos = LGPDService::camposAnonimizaveis($modelClass);
            $this->info("[DRY-RUN] Seria anonimizado: {$entidadeTipo} #{$entidadeId}");
            $this->info("[DRY-RUN] Campos: " . implode(', ', $campos));
            $this->info("[DRY-RUN] Solicitante: {$solicitante}");

            return Command::SUCCESS;
        }

        try {
            $method = match ($entidadeTipo) {
                'fornecedor' => 'anonimizarFornecedor',
                'fiscal' => 'anonimizarFiscal',
                'servidor' => 'anonimizarServidor',
                'usuario' => 'anonimizarUsuario',
            };

            $log = LGPDService::$method($entidade, $solicitante, $justificativa);

            $this->info("Anonimizado com sucesso: {$entidadeTipo} #{$entidadeId}");
            $this->info("Log LGPD: #{$log->id} | Campos: " . implode(', ', $log->campos_anonimizados ?? []));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Erro ao anonimizar: {$e->getMessage()}");

            Log::error('LGPD: Falha na anonimizacao via CLI', [
                'entidade' => $entidadeTipo,
                'id' => $entidadeId,
                'exception' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }

    private function configurarTenant(Tenant $tenant): void
    {
        config([
            'database.connections.tenant.database' => $tenant->database_name,
            'database.connections.tenant.host' => $tenant->database_host
                ?? config('database.connections.tenant.host'),
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
        app()->instance('tenant', $tenant);
    }
}
