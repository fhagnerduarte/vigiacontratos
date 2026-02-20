<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Acesso nao autorizado.');
        }

        if (! $user->hasPermission($permission)) {
            abort(403, 'Voce nao possui permissao para acessar este recurso.');
        }

        return $next($request);
    }
}
