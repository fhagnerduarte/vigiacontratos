<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->is_ativo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('tenant.login')
                ->withErrors(['email' => 'Sua conta foi desativada. Contate o administrador.']);
        }

        $tenant = app()->bound('tenant') ? app('tenant') : null;
        if ($tenant && ! $tenant->is_ativo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(503, 'Sistema temporariamente indisponivel. A prefeitura esta com acesso suspenso.');
        }

        return $next($request);
    }
}
