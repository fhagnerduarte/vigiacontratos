<?php

use App\Http\Controllers\AdminSaaS\AuthController;
use App\Http\Controllers\AdminSaaS\TenantController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin-saas')->name('admin-saas.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

Route::prefix('admin-saas')->name('admin-saas.')->middleware(['auth:admin', 'admin.saas'])->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('dashboard');
    Route::resource('tenants', TenantController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
    Route::post('tenants/{tenant}/deactivate', [TenantController::class, 'deactivate'])->name('tenants.deactivate');
});
