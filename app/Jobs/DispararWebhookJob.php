<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispararWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(
        public Webhook $webhook,
        public string $evento,
        public array $dados,
    ) {}

    public function handle(): void
    {
        $payload = json_encode([
            'event' => $this->evento,
            'data' => $this->dados,
            'timestamp' => now()->toIso8601String(),
        ]);

        $assinatura = WebhookService::gerarAssinatura($payload, $this->webhook->secret);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $assinatura,
                    'X-Webhook-Event' => $this->evento,
                ])
                ->withBody($payload, 'application/json')
                ->post($this->webhook->url);

            $this->webhook->update([
                'ultimo_disparo_em' => now(),
                'ultimo_status_code' => $response->status(),
                'falhas_consecutivas' => $response->successful() ? 0 : $this->webhook->falhas_consecutivas + 1,
            ]);

            if (! $response->successful()) {
                Log::warning("Webhook {$this->webhook->id} retornou status {$response->status()}", [
                    'url' => $this->webhook->url,
                    'evento' => $this->evento,
                ]);

                $this->release($this->backoff[$this->attempts() - 1] ?? 300);
            }
        } catch (\Exception $e) {
            $this->webhook->update([
                'ultimo_disparo_em' => now(),
                'falhas_consecutivas' => $this->webhook->falhas_consecutivas + 1,
            ]);

            Log::error("Webhook {$this->webhook->id} falhou: {$e->getMessage()}", [
                'url' => $this->webhook->url,
                'evento' => $this->evento,
            ]);

            if ($this->webhook->falhas_consecutivas >= 10) {
                $this->webhook->update(['is_ativo' => false]);
                Log::warning("Webhook {$this->webhook->id} desativado apos 10 falhas consecutivas.");

                return;
            }

            $this->release($this->backoff[$this->attempts() - 1] ?? 300);
        }
    }
}
