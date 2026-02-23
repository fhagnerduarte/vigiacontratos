<?php

use App\Http\Controllers\AdminSaaS\AuthController;
use App\Http\Controllers\AdminSaaS\MfaController;
use App\Http\Controllers\AdminSaaS\TenantController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin-saas')->name('admin-saas.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Rotas MFA Admin (autenticado, sem exigir verificação MFA)
Route::prefix('admin-saas/mfa')->name('admin-saas.mfa.')->middleware(['auth:admin'])->group(function () {
    Route::get('setup', [MfaController::class, 'setup'])->name('setup');
    Route::post('enable', [MfaController::class, 'enable'])->name('enable');
    Route::get('verify', [MfaController::class, 'showVerify'])->name('verify');
    Route::post('verify', [MfaController::class, 'verify'])->name('verify.submit');
    Route::get('recovery', [MfaController::class, 'showRecovery'])->name('recovery');
    Route::post('recovery', [MfaController::class, 'useRecovery'])->name('recovery.submit');
});

Route::prefix('admin-saas')->name('admin-saas.')->middleware(['auth:admin', 'admin.saas'])->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('dashboard');
    Route::resource('tenants', TenantController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
    Route::post('tenants/{tenant}/deactivate', [TenantController::class, 'deactivate'])->name('tenants.deactivate');
    Route::put('tenants/{tenant}/mfa', [TenantController::class, 'updateMfaConfig'])->name('tenants.mfa.update');
});
