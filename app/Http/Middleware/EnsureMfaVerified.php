<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Verificar se o tenant tem MFA habilitado
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        if ($tenant && !$tenant->isMfaHabilitado()) {
            return $next($request);
        }

        // Usuário com MFA habilitado mas não verificado na sessão
        if ($user->isMfaEnabled() && !$request->session()->get('mfa_verified')) {
            return redirect()->route('tenant.mfa.verify');
        }

        // Usuário com MFA obrigatório mas não configurado
        if (method_exists($user, 'isMfaRequired') && $user->isMfaRequired() && !$user->isMfaEnabled()) {
            return redirect()->route('tenant.mfa.setup');
        }

        return $next($request);
    }
}
