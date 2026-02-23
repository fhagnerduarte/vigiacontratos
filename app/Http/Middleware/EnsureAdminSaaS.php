<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSaaS
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin-saas.login');
        }

        if (!$admin->is_ativo) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin-saas.login')
                ->withErrors(['email' => 'Sua conta foi desativada.']);
        }

        // MFA obrigatório para admin — verificar se habilitado e verificado na sessão
        if ($admin->isMfaEnabled() && !$request->session()->get('mfa_verified')) {
            return redirect()->route('admin-saas.mfa.verify');
        }

        // MFA obrigatório mas não configurado — redirecionar para setup
        if ($admin->isMfaRequired() && !$admin->isMfaEnabled()) {
            return redirect()->route('admin-saas.mfa.setup');
        }

        return $next($request);
    }
}
