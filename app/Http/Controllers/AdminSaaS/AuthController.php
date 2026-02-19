<?php

namespace App\Http\Controllers\AdminSaaS;

use App\Http\Controllers\Controller;
use App\Models\AdminLoginLog;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin-saas.dashboard');
        }

        return view('admin-saas.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $admin = Auth::guard('admin')->user();

            AdminLoginLog::create([
                'admin_user_id' => $admin->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'success' => true,
            ]);

            $admin->update(['last_login_at' => now()]);

            $request->session()->regenerate();

            return redirect()->route('admin-saas.dashboard');
        }

        $adminUser = AdminUser::where('email', $credentials['email'])->first();
        if ($adminUser) {
            AdminLoginLog::create([
                'admin_user_id' => $adminUser->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'success' => false,
            ]);
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin-saas.login');
    }
}
