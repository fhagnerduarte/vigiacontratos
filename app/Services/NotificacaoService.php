<?php

namespace App\Services;

use App\Enums\CanalNotificacao;
use App\Enums\StatusAlerta;
use App\Models\Alerta;
use App\Models\LogNotificacao;

class NotificacaoService
{
    /**
     * Registra uma tentativa de envio no log (RN-049).
     */
    public static function registrarLog(
        Alerta $alerta,
        CanalNotificacao $canal,
        string $destinatario,
        bool $sucesso,
        int $tentativaNumero,
        ?string $respostaGateway = null
    ): LogNotificacao {
        return LogNotificacao::create([
            'alerta_id' => $alerta->id,
            'canal' => $canal->value,
            'destinatario' => $destinatario,
            'data_envio' => now(),
            'sucesso' => $sucesso,
            'resposta_gateway' => $respostaGateway,
            'tentativa_numero' => $tentativaNumero,
        ]);
    }

    /**
     * Atualiza contagem de tentativas do alerta e status.
     */
    public static function atualizarStatusAlerta(Alerta $alerta): void
    {
        $totalTentativas = $alerta->logNotificacoes()->count();
        $alerta->update(['tentativas_envio' => $totalTentativas]);

        // Se pelo menos uma notificacao foi enviada com sucesso, marcar como enviado
        $temSucesso = $alerta->logNotificacoes()
            ->where('sucesso', true)
            ->exists();

        if ($temSucesso && $alerta->status === StatusAlerta::Pendente) {
            $alerta->update(['status' => StatusAlerta::Enviado->value]);
        }
    }
}
