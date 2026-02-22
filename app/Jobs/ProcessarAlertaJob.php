<?php

namespace App\Jobs;

use App\Enums\CanalNotificacao;
use App\Models\Alerta;
use App\Models\User;
use App\Notifications\AlertaVencimentoNotification;
use App\Services\NotificacaoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProcessarAlertaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximo de tentativas (RN-050).
     */
    public int $tries = 3;

    /**
     * Backoff exponencial em segundos: 60s, 300s, 900s (RN-050).
     *
     * @return array<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(
        public Alerta $alerta,
        public array $destinatarios,
        public string $tenantDatabaseName
    ) {
        $this->onQueue('alertas');
    }

    public function handle(): void
    {
        // Restaurar conexao tenant no worker
        config(['database.connections.tenant.database' => $this->tenantDatabaseName]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        $alerta = $this->alerta->fresh(['contrato.fornecedor', 'contrato.secretaria']);

        if (!$alerta || $alerta->status->value === 'resolvido') {
            return; // Alerta ja resolvido, nao enviar
        }

        $tentativa = $this->attempts();

        foreach ($this->destinatarios as $dest) {
            try {
                // Canal: sistema (database notification) — so se tiver User
                if (isset($dest['user']) && $dest['user'] instanceof User) {
                    $dest['user']->notify(new AlertaVencimentoNotification($alerta));

                    NotificacaoService::registrarLog(
                        $alerta,
                        CanalNotificacao::Sistema,
                        $dest['email'],
                        true,
                        $tentativa,
                        'Notificacao interna enviada'
                    );
                }

                // Canal: email
                if (!empty($dest['email'])) {
                    Notification::route('mail', $dest['email'])
                        ->notify(new AlertaVencimentoNotification($alerta));

                    NotificacaoService::registrarLog(
                        $alerta,
                        CanalNotificacao::Email,
                        $dest['email'],
                        true,
                        $tentativa,
                        'Email enviado'
                    );
                }
            } catch (\Throwable $e) {
                Log::warning("Falha ao enviar notificacao de alerta #{$alerta->id} para {$dest['email']}", [
                    'tentativa' => $tentativa,
                    'error' => $e->getMessage(),
                ]);

                $canal = (isset($dest['user']) && $dest['user'] instanceof User)
                    ? CanalNotificacao::Sistema
                    : CanalNotificacao::Email;

                NotificacaoService::registrarLog(
                    $alerta,
                    $canal,
                    $dest['email'] ?? 'desconhecido',
                    false,
                    $tentativa,
                    $e->getMessage()
                );
            }
        }

        // Atualizar status do alerta
        NotificacaoService::atualizarStatusAlerta($alerta);
    }

    /**
     * Tratamento de falha final apos todas as tentativas.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Falha definitiva ao processar alerta #{$this->alerta->id}", [
            'exception' => $exception->getMessage(),
        ]);
    }
}
