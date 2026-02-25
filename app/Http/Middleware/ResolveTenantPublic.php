<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantPublic
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('slug');

        if (! $slug) {
            abort(404, 'Prefeitura nao encontrada.');
        }

        $tenant = Tenant::where('slug', $slug)
            ->where('is_ativo', true)
            ->first();

        if (! $tenant) {
            Log::warning('Acesso a portal publico de tenant inexistente ou inativo.', [
                'slug' => $slug,
                'ip' => $request->ip(),
            ]);
            abort(404, 'Prefeitura nao encontrada ou inativa.');
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
}
