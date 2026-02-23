<?php

namespace App\Notifications;

use App\Models\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IntegridadeComprometidaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Documento $documento
    ) {}

    public function via(object $notifiable): array
    {
        if ($notifiable instanceof \App\Models\User) {
            return ['mail', 'database'];
        }

        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[ALERTA DE SEGURANCA] Integridade comprometida — ' . $this->documento->nome_arquivo)
            ->greeting('Alerta de Seguranca')
            ->line('A integridade do documento abaixo foi comprometida:')
            ->line('**Arquivo:** ' . $this->documento->nome_arquivo)
            ->line('**Hash esperado:** ' . $this->documento->hash_integridade)
            ->line('**Detectado em:** ' . now()->format('d/m/Y H:i:s'))
            ->line('')
            ->line('O download deste documento foi bloqueado automaticamente.')
            ->line('Verifique a origem da alteracao e tome as medidas cabiveis.')
            ->salutation('Atenciosamente, VigiaContratos');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo' => 'integridade_comprometida',
            'documento_id' => $this->documento->id,
            'nome_arquivo' => $this->documento->nome_arquivo,
            'detectado_em' => now()->toISOString(),
            'mensagem' => 'Integridade comprometida: ' . $this->documento->nome_arquivo,
        ];
    }
}
