<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarPermissoesExpiradasCommand extends Command
{
    protected $signature = 'permissoes:verificar-expiradas';

    protected $description = 'Remove permissoes individuais expiradas (housekeeping diario)';

    public function handle(): int
    {
        $deleted = DB::connection('tenant')
            ->table('user_permissions')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        if ($deleted > 0) {
            Log::info("Permissoes expiradas removidas: {$deleted} registro(s).");
            $this->info("Removidas {$deleted} permissao(oes) expirada(s).");
        } else {
            $this->info('Nenhuma permissao expirada encontrada.');
        }

        return self::SUCCESS;
    }
}
