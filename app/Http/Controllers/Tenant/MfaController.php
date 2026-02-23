<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MfaController extends Controller
{
    public function __construct(
        protected MfaService $mfaService,
    ) {}

    public function setup(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        // Verificar se o tenant permite MFA
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        if ($tenant && !$tenant->isMfaHabilitado()) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'MFA não está habilitado para esta organização.');
        }

        if ($user->isMfaEnabled()) {
            return redirect()->route('tenant.dashboard')
                ->with('info', 'MFA já está ativado.');
        }

        if (!$user->isMfaSupported()) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'Seu perfil não suporta MFA.');
        }

        $secret = $request->session()->get('mfa_setup_secret');
        if (!$secret) {
            $secret = $this->mfaService->generateSecret();
            $request->session()->put('mfa_setup_secret', $secret);
        }

        $qrCodeDataUri = $this->mfaService->generateQrCodeDataUri($user, $secret);

        return view('auth.mfa.setup', [
            'secret' => $secret,
            'qrCodeDataUri' => $qrCodeDataUri,
        ]);
    }

    public function enable(Request $request): RedirectResponse|View
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'O código é obrigatório.',
            'code.size' => 'O código deve ter 6 dígitos.',
        ]);

        $user = Auth::user();
        $secret = $request->session()->get('mfa_setup_secret');

        if (!$secret) {
            return redirect()->route('tenant.mfa.setup')
                ->with('error', 'Sessão expirada. Tente novamente.');
        }

        $recoveryCodes = $this->mfaService->enableMfa($user, $secret, $request->code);

        if ($recoveryCodes === false) {
            return redirect()->route('tenant.mfa.setup')
                ->with('error', 'Código inválido. Verifique e tente novamente.');
        }

        $request->session()->forget('mfa_setup_secret');
        $request->session()->put('mfa_verified', true);

        return view('auth.mfa.recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    public function showVerify(): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isMfaEnabled()) {
            return redirect()->route('tenant.dashboard');
        }

        if (session('mfa_verified')) {
            return redirect()->route('tenant.dashboard');
        }

        return view('auth.mfa.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'O código é obrigatório.',
            'code.size' => 'O código deve ter 6 dígitos.',
        ]);

        $user = Auth::user();

        if ($this->mfaService->verifyCode($user->mfa_secret, $request->code)) {
            $request->session()->put('mfa_verified', true);
            $request->session()->forget('mfa_pending');

            return redirect()->intended(route('tenant.dashboard'));
        }

        return back()->with('error', 'Código inválido. Tente novamente.');
    }

    public function showRecovery(): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isMfaEnabled()) {
            return redirect()->route('tenant.dashboard');
        }

        if (session('mfa_verified')) {
            return redirect()->route('tenant.dashboard');
        }

        return view('auth.mfa.recovery');
    }

    public function useRecovery(Request $request): RedirectResponse
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
        ], [
            'recovery_code.required' => 'O código de recuperação é obrigatório.',
        ]);

        $user = Auth::user();

        if ($this->mfaService->useRecoveryCode($user, $request->recovery_code)) {
            $request->session()->put('mfa_verified', true);
            $request->session()->forget('mfa_pending');

            $remaining = $this->mfaService->getRemainingRecoveryCodes($user);

            $message = 'Verificação concluída.';
            if ($remaining <= 2) {
                $message .= " Atenção: você tem apenas {$remaining} código(s) de recuperação restante(s).";
            }

            return redirect()->intended(route('tenant.dashboard'))
                ->with('warning', $message);
        }

        return back()->with('error', 'Código de recuperação inválido.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ], [
            'password.required' => 'A senha é obrigatória.',
            'code.required' => 'O código é obrigatório.',
            'code.size' => 'O código deve ter 6 dígitos.',
        ]);

        $user = Auth::user();

        if ($user->isMfaRequired()) {
            return back()->with('error', 'MFA é obrigatório para seu perfil e não pode ser desativado.');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Senha incorreta.');
        }

        if (!$this->mfaService->verifyCode($user->mfa_secret, $request->code)) {
            return back()->with('error', 'Código MFA inválido.');
        }

        $this->mfaService->disableMfa($user);

        return redirect()->route('tenant.dashboard')
            ->with('success', 'Autenticação em dois fatores desativada.');
    }
}
