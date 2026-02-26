<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTenantResolved
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $this->resolveSlug($request);

        if (! $slug) {
            return response()->json([
                'message' => 'Header X-Tenant-Slug e obrigatorio para requisicoes da API.',
            ], 422);
        }

        $tenant = Tenant::where('slug', $slug)
            ->first();

        if (! $tenant) {
            Log::warning('Tentativa de acesso a API com tenant inexistente.', [
                'slug' => $slug,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Tenant nao encontrado.',
            ], 404);
        }

        if (! $tenant->is_ativo) {
            return response()->json([
                'message' => 'Tenant inativo.',
            ], 403);
        }

        Config::set('database.connections.tenant', array_merge(
            config('database.connections.tenant'),
            [
                'database' => $tenant->database_name,
                'host' => $tenant->database_host ?? config('database.connections.tenant.host'),
            ]
        ));

        DB::purge('tenant');
        DB::reconnect('tenant');

        app()->instance('tenant', $tenant);

        Config::set('cache.prefix', 'tenant_' . $tenant->id);

        return $next($request);
    }

    private function resolveSlug(Request $request): ?string
    {
        // 1. Header X-Tenant-Slug (preferencial e obrigatorio para API)
        $headerSlug = $request->header('X-Tenant-Slug');
        if ($headerSlug) {
            return $headerSlug;
        }

        // 2. Fallback: subdominio (producao)
        $host = $request->getHost();

        // Em localhost nao ha subdominio — header e obrigatorio
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            return null;
        }

        $appDomain = config('app.domain', 'vigiacontratos.com.br');
        $domainParts = explode('.', $appDomain);
        $hostParts = explode('.', $host);

        if (count($hostParts) > count($domainParts)) {
            return $hostParts[0];
        }

        return null;
    }
}
