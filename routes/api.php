<?php

use App\Http\Controllers\Api\V1\AditivosController;
use App\Http\Controllers\Api\V1\AlertasController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContratosController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FornecedoresController;
use App\Http\Controllers\Api\V1\PainelRiscoController;
use App\Http\Controllers\Api\V1\SecretariasController;
use App\Http\Controllers\Api\V1\ServidoresController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1
|--------------------------------------------------------------------------
|
| Endpoints RESTful autenticados via Laravel Sanctum.
| Tenant resolvido pelo header X-Tenant-Slug ou subdominio.
|
*/

// --- Autenticacao (requer tenant, sem token) ---
Route::middleware(['api.tenant', 'throttle:api-auth'])
    ->prefix('v1')
    ->group(function () {
        Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');
    });

// --- Endpoints protegidos (token + tenant) ---
Route::middleware(['api.tenant', 'auth:sanctum', 'throttle:api'])
    ->prefix('v1')
    ->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::get('/auth/tokens', [AuthController::class, 'tokens'])->name('api.auth.tokens');
        Route::delete('/auth/tokens/{tokenId}', [AuthController::class, 'revokeToken'])->name('api.auth.revoke-token');

        // Contratos
        Route::get('/contratos', [ContratosController::class, 'index'])->name('api.contratos.index');
        Route::get('/contratos/{contrato}', [ContratosController::class, 'show'])->name('api.contratos.show');
        Route::get('/contratos/{contrato}/aditivos', [AditivosController::class, 'porContrato'])->name('api.contratos.aditivos');

        // Aditivos
        Route::get('/aditivos', [AditivosController::class, 'index'])->name('api.aditivos.index');
        Route::get('/aditivos/{aditivo}', [AditivosController::class, 'show'])->name('api.aditivos.show');

        // Fornecedores
        Route::get('/fornecedores', [FornecedoresController::class, 'index'])->name('api.fornecedores.index');
        Route::get('/fornecedores/{fornecedor}', [FornecedoresController::class, 'show'])->name('api.fornecedores.show');

        // Secretarias
        Route::get('/secretarias', [SecretariasController::class, 'index'])->name('api.secretarias.index');
        Route::get('/secretarias/{secretaria}', [SecretariasController::class, 'show'])->name('api.secretarias.show');

        // Servidores
        Route::get('/servidores', [ServidoresController::class, 'index'])->name('api.servidores.index');
        Route::get('/servidores/{servidor}', [ServidoresController::class, 'show'])->name('api.servidores.show');

        // Alertas
        Route::get('/alertas', [AlertasController::class, 'index'])->name('api.alertas.index');
        Route::get('/alertas/{alerta}', [AlertasController::class, 'show'])->name('api.alertas.show');

        // Dashboard
        Route::get('/dashboard/indicadores', [DashboardController::class, 'indicadores'])->name('api.dashboard.indicadores');

        // Painel de Risco
        Route::get('/painel-risco/indicadores', [PainelRiscoController::class, 'indicadores'])->name('api.painel-risco.indicadores');
        Route::get('/painel-risco/ranking', [PainelRiscoController::class, 'ranking'])->name('api.painel-risco.ranking');
    });
