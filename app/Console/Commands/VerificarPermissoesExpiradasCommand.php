<?php

namespace App\Console\Commands;

use App\Models\HistoricoAlteracao;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarPermissoesExpiradasCommand extends Command
{
    protected $signature = 'permissoes:verificar-expiradas';

    protected $description = 'Remove permissoes individuais expiradas (housekeeping diario)';

    public function handle(): int
    {
        $conn = DB::connection('tenant');

        // Buscar permissoes expiradas ANTES de deletar para log de auditoria (RN-332)
        $expiradas = $conn->table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->whereNotNull('user_permissions.expires_at')
            ->where('user_permissions.expires_at', '<', now())
            ->select(
                'user_permissions.user_id',
                'user_permissions.permission_id',
                'permissions.nome as permission_nome',
                'user_permissions.expires_at',
                'user_permissions.concedido_por'
            )
            ->get();

        if ($expiradas->isEmpty()) {
            $this->info('Nenhuma permissao expirada encontrada.');

            return self::SUCCESS;
        }

        // Registrar auditoria para cada permissao expirada (RN-332)
        foreach ($expiradas as $registro) {
            HistoricoAlteracao::create([
                'auditable_type' => User::class,
                'auditable_id' => $registro->user_id,
                'campo_alterado' => 'permissao_expirada',
                'valor_anterior' => $registro->permission_nome,
                'valor_novo' => null,
                'user_id' => $registro->concedido_por ?? $registro->user_id,
                'role_nome' => 'sistema',
                'ip_address' => '127.0.0.1',
                'created_at' => now(),
            ]);
        }

        // Deletar permissoes expiradas
        $deleted = $conn->table('user_permissions')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        Log::info("Permissoes expiradas removidas: {$deleted} registro(s). Registros de auditoria criados.");
        $this->info("Removidas {$deleted} permissao(oes) expirada(s). Registros de auditoria criados.");

        return self::SUCCESS;
    }
}
