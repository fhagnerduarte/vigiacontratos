<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('tenant.dashboard');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Verificar se o tenant tem MFA habilitado
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        $mfaHabilitado = !$tenant || $tenant->isMfaHabilitado();

        // MFA habilitado — redirecionar para verificação
        if ($mfaHabilitado && $user->isMfaEnabled()) {
            $request->session()->put('mfa_pending', true);

            return redirect()->route('tenant.mfa.verify');
        }

        // MFA obrigatório mas não configurado — forçar setup
        if ($mfaHabilitado && $user->isMfaRequired()) {
            return redirect()->route('tenant.mfa.setup');
        }

        return redirect()->intended(route('tenant.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login');
    }
}
