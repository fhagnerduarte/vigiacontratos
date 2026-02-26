<?php

namespace App\Services;

use App\Jobs\DispararWebhookJob;
use App\Models\Webhook;
use Illuminate\Support\Str;

class WebhookService
{
    public static function disparar(string $evento, array $dados): void
    {
        $webhooks = Webhook::ativos()->paraEvento($evento)->get();

        foreach ($webhooks as $webhook) {
            DispararWebhookJob::dispatch($webhook, $evento, $dados);
        }
    }

    public static function gerarSecret(): string
    {
        return Str::random(64);
    }

    public static function gerarAssinatura(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    public static function validarAssinatura(string $payload, string $secret, string $assinatura): bool
    {
        $esperada = self::gerarAssinatura($payload, $secret);

        return hash_equals($esperada, $assinatura);
    }

    public const EVENTOS_DISPONIVEIS = [
        'contrato.criado',
        'contrato.atualizado',
        'contrato.excluido',
        'aditivo.criado',
        'fornecedor.criado',
        'fornecedor.atualizado',
        'alerta.gerado',
        'alerta.resolvido',
        'documento.uploaded',
        'ocorrencia.criada',
        'ocorrencia.resolvida',
        'execucao_financeira.criada',
        'pnp.preco.registrado',
        'pnp.comparativo.gerado',
        'dados_abertos.exportacao',
    ];
}
