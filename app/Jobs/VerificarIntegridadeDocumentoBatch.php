<?php

namespace App\Jobs;

use App\Models\Documento;
use App\Services\DocumentoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarIntegridadeDocumentoBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        return [120, 300, 600];
    }

    public function __construct(
        public array $documentoIds,
        public string $tenantDatabaseName,
        public string $tenantSlug
    ) {
        $this->onQueue('integridade');
    }

    public function handle(): void
    {
        config(['database.connections.tenant.database' => $this->tenantDatabaseName]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        $documentos = Documento::whereIn('id', $this->documentoIds)
            ->whereNull('deleted_at')
            ->get();

        foreach ($documentos as $documento) {
            try {
                DocumentoService::verificarIntegridade($documento);
            } catch (\Throwable $e) {
                Log::warning("Falha ao verificar integridade do documento #{$documento->id}", [
                    'tenant' => $this->tenantSlug,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Batch de verificacao de integridade falhou", [
            'tenant' => $this->tenantSlug,
            'ids' => $this->documentoIds,
            'exception' => $exception->getMessage(),
        ]);
    }
}
