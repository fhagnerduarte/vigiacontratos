<?php

namespace App\Http\Controllers\AdminSaaS;

use App\Http\Controllers\Controller;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MfaController extends Controller
{
    public function __construct(
        protected MfaService $mfaService,
    ) {}

    public function setup(Request $request): View|RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->isMfaEnabled()) {
            return redirect()->route('admin-saas.dashboard')
                ->with('info', 'MFA já está ativado.');
        }

        $secret = $request->session()->get('admin_mfa_setup_secret');
        if (!$secret) {
            $secret = $this->mfaService->generateSecret();
            $request->session()->put('admin_mfa_setup_secret', $secret);
        }

        $qrCodeDataUri = $this->mfaService->generateQrCodeDataUri($admin, $secret);

        return view('admin-saas.auth.mfa.setup', [
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

        $admin = Auth::guard('admin')->user();
        $secret = $request->session()->get('admin_mfa_setup_secret');

        if (!$secret) {
            return redirect()->route('admin-saas.mfa.setup')
                ->with('error', 'Sessão expirada. Tente novamente.');
        }

        $recoveryCodes = $this->mfaService->enableMfa($admin, $secret, $request->code);

        if ($recoveryCodes === false) {
            return redirect()->route('admin-saas.mfa.setup')
                ->with('error', 'Código inválido. Verifique e tente novamente.');
        }

        $request->session()->forget('admin_mfa_setup_secret');
        $request->session()->put('mfa_verified', true);

        return view('admin-saas.auth.mfa.recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    public function showVerify(): View|RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin-saas.login');
        }

        if (!$admin->isMfaEnabled()) {
            return redirect()->route('admin-saas.mfa.setup');
        }

        if (session('mfa_verified')) {
            return redirect()->route('admin-saas.dashboard');
        }

        return view('admin-saas.auth.mfa.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'O código é obrigatório.',
            'code.size' => 'O código deve ter 6 dígitos.',
        ]);

        $admin = Auth::guard('admin')->user();

        if ($this->mfaService->verifyCode($admin->mfa_secret, $request->code)) {
            $request->session()->put('mfa_verified', true);
            $request->session()->forget('mfa_pending');

            return redirect()->intended(route('admin-saas.dashboard'));
        }

        return back()->with('error', 'Código inválido. Tente novamente.');
    }

    public function showRecovery(): View|RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin || !$admin->isMfaEnabled()) {
            return redirect()->route('admin-saas.login');
        }

        if (session('mfa_verified')) {
            return redirect()->route('admin-saas.dashboard');
        }

        return view('admin-saas.auth.mfa.recovery');
    }

    public function useRecovery(Request $request): RedirectResponse
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
        ], [
            'recovery_code.required' => 'O código de recuperação é obrigatório.',
        ]);

        $admin = Auth::guard('admin')->user();

        if ($this->mfaService->useRecoveryCode($admin, $request->recovery_code)) {
            $request->session()->put('mfa_verified', true);
            $request->session()->forget('mfa_pending');

            $remaining = $this->mfaService->getRemainingRecoveryCodes($admin);

            $message = 'Verificação concluída.';
            if ($remaining <= 2) {
                $message .= " Atenção: você tem apenas {$remaining} código(s) de recuperação restante(s).";
            }

            return redirect()->intended(route('admin-saas.dashboard'))
                ->with('warning', $message);
        }

        return back()->with('error', 'Código de recuperação inválido.');
    }
}
