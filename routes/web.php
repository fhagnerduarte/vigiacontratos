<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\FornecedoresController;
use App\Http\Controllers\Tenant\SecretariasController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas do Tenant
|--------------------------------------------------------------------------
| Todas as rotas usam o middleware 'tenant' (SetTenantConnection)
| para resolver o banco de dados do tenant via subdomínio.
*/

// Rotas de autenticação (guest)
Route::middleware(['tenant', 'guest'])->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('tenant.login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('tenant.password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('tenant.password.email');
});

// Rotas protegidas (autenticado)
Route::middleware(['tenant', 'auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('tenant.dashboard');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('tenant.logout');

    Route::resource('secretarias', SecretariasController::class)
        ->except(['show'])
        ->names('tenant.secretarias');

    Route::resource('fornecedores', FornecedoresController::class)
        ->except(['show'])
        ->names('tenant.fornecedores')
        ->parameters(['fornecedores' => 'fornecedor']);
});
