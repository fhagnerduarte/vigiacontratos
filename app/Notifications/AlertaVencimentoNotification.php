<?php

namespace App\Notifications;

use App\Models\Alerta;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AlertaVencimentoNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Alerta $alerta
    ) {}

    /**
     * Canais de entrega: email + database (RN-048).
     */
    public function via(object $notifiable): array
    {
        // Se notifiable e um User, usar ambos os canais
        if ($notifiable instanceof \App\Models\User) {
            return ['mail', 'database'];
        }

        // Se e on-demand (route notification), so email
        return ['mail'];
    }

    /**
     * Representacao em email.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $alerta = $this->alerta;
        $contrato = $alerta->contrato;
        $prioridade = $alerta->prioridade->label();
        $tipoEvento = $alerta->tipo_evento->label();

        $subject = "[{$prioridade}] Alerta de {$tipoEvento} - Contrato {$contrato->numero}";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Alerta de Vencimento Contratual')
            ->line("**Contrato:** {$contrato->numero}")
            ->line('**Objeto:** ' . Str::limit($contrato->objeto, 100))
            ->line("**Evento:** {$tipoEvento}")
            ->line("**Prioridade:** {$prioridade}")
            ->line("**Dias para vencimento:** {$alerta->dias_para_vencimento} dia(s)")
            ->line("**Data de vencimento:** {$alerta->data_vencimento->format('d/m/Y')}")
            ->line('')
            ->line($alerta->mensagem);

        if ($alerta->prioridade->value === 'urgente') {
            $mail->line('')
                ->line('**ATENCAO: Este alerta requer acao imediata.**');
        }

        $mail->line('')
            ->line('Acesse o sistema para mais detalhes e regularizacao.')
            ->salutation('Atenciosamente, VigiaContratos');

        return $mail;
    }

    /**
     * Representacao no banco de dados (notifications table).
     * Usado para exibir no sino/badge do navbar.
     */
    public function toDatabase(object $notifiable): array
    {
        $alerta = $this->alerta;
        $contrato = $alerta->contrato;

        return [
            'alerta_id' => $alerta->id,
            'contrato_id' => $contrato->id,
            'contrato_numero' => $contrato->numero,
            'tipo_evento' => $alerta->tipo_evento->value,
            'tipo_evento_label' => $alerta->tipo_evento->label(),
            'prioridade' => $alerta->prioridade->value,
            'prioridade_label' => $alerta->prioridade->label(),
            'prioridade_cor' => $alerta->prioridade->cor(),
            'prioridade_icone' => $alerta->prioridade->icone(),
            'dias_para_vencimento' => $alerta->dias_para_vencimento,
            'mensagem' => $alerta->mensagem,
            'data_vencimento' => $alerta->data_vencimento->toDateString(),
        ];
    }
}
