<?php

use App\Http\Controllers\Api\V1\AditivosController;
use App\Http\Controllers\Api\V1\AlertasController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContratosController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DocumentosController;
use App\Http\Controllers\Api\V1\ExecucoesFinanceirasController;
use App\Http\Controllers\Api\V1\FornecedoresController;
use App\Http\Controllers\Api\V1\OcorrenciasController;
use App\Http\Controllers\Api\V1\PainelRiscoController;
use App\Http\Controllers\Api\V1\SecretariasController;
use App\Http\Controllers\Api\V1\ServidoresController;
use App\Http\Controllers\Api\V1\TceController;
use App\Http\Controllers\Api\V1\WebhooksController;
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

        // Contratos (CRUD + nested)
        Route::get('/contratos', [ContratosController::class, 'index'])->name('api.contratos.index');
        Route::post('/contratos', [ContratosController::class, 'store'])->name('api.contratos.store');
        Route::get('/contratos/{contrato}', [ContratosController::class, 'show'])->name('api.contratos.show');
        Route::put('/contratos/{contrato}', [ContratosController::class, 'update'])->name('api.contratos.update');
        Route::delete('/contratos/{contrato}', [ContratosController::class, 'destroy'])->name('api.contratos.destroy');
        Route::get('/contratos/{contrato}/aditivos', [AditivosController::class, 'porContrato'])->name('api.contratos.aditivos');
        Route::post('/contratos/{contrato}/aditivos', [AditivosController::class, 'store'])->name('api.contratos.aditivos.store');
        Route::get('/contratos/{contrato}/fiscais', [ContratosController::class, 'fiscais'])->name('api.contratos.fiscais');
        Route::get('/contratos/{contrato}/documentos', [ContratosController::class, 'documentos'])->name('api.contratos.documentos');
        Route::post('/contratos/{contrato}/documentos', [DocumentosController::class, 'store'])->name('api.contratos.documentos.store');
        Route::post('/contratos/{contrato}/execucoes', [ExecucoesFinanceirasController::class, 'store'])->name('api.contratos.execucoes.store');
        Route::post('/contratos/{contrato}/ocorrencias', [OcorrenciasController::class, 'store'])->name('api.contratos.ocorrencias.store');

        // Aditivos
        Route::get('/aditivos', [AditivosController::class, 'index'])->name('api.aditivos.index');
        Route::get('/aditivos/{aditivo}', [AditivosController::class, 'show'])->name('api.aditivos.show');

        // Fornecedores (CRUD)
        Route::get('/fornecedores', [FornecedoresController::class, 'index'])->name('api.fornecedores.index');
        Route::post('/fornecedores', [FornecedoresController::class, 'store'])->name('api.fornecedores.store');
        Route::get('/fornecedores/{fornecedor}', [FornecedoresController::class, 'show'])->name('api.fornecedores.show');
        Route::put('/fornecedores/{fornecedor}', [FornecedoresController::class, 'update'])->name('api.fornecedores.update');
        Route::delete('/fornecedores/{fornecedor}', [FornecedoresController::class, 'destroy'])->name('api.fornecedores.destroy');

        // Secretarias
        Route::get('/secretarias', [SecretariasController::class, 'index'])->name('api.secretarias.index');
        Route::get('/secretarias/{secretaria}', [SecretariasController::class, 'show'])->name('api.secretarias.show');

        // Servidores
        Route::get('/servidores', [ServidoresController::class, 'index'])->name('api.servidores.index');
        Route::get('/servidores/{servidor}', [ServidoresController::class, 'show'])->name('api.servidores.show');

        // Alertas
        Route::get('/alertas', [AlertasController::class, 'index'])->name('api.alertas.index');
        Route::get('/alertas/{alerta}', [AlertasController::class, 'show'])->name('api.alertas.show');
        Route::post('/alertas/{alerta}/resolver', [AlertasController::class, 'resolver'])->name('api.alertas.resolver');

        // Ocorrencias
        Route::post('/ocorrencias/{ocorrencia}/resolver', [OcorrenciasController::class, 'resolver'])->name('api.ocorrencias.resolver');

        // Dashboard
        Route::get('/dashboard/indicadores', [DashboardController::class, 'indicadores'])->name('api.dashboard.indicadores');

        // Painel de Risco
        Route::get('/painel-risco/indicadores', [PainelRiscoController::class, 'indicadores'])->name('api.painel-risco.indicadores');
        Route::get('/painel-risco/ranking', [PainelRiscoController::class, 'ranking'])->name('api.painel-risco.ranking');

        // TCE — Exportacao Estruturada (IMP-064)
        Route::get('/tce/dados', [TceController::class, 'dados'])->name('api.tce.dados');
        Route::get('/tce/validar', [TceController::class, 'validar'])->name('api.tce.validar');
        Route::post('/tce/exportar', [TceController::class, 'exportar'])->name('api.tce.exportar');
        Route::get('/tce/historico', [TceController::class, 'historico'])->name('api.tce.historico');

        // Webhooks (CRUD)
        Route::get('/webhooks', [WebhooksController::class, 'index'])->name('api.webhooks.index');
        Route::post('/webhooks', [WebhooksController::class, 'store'])->name('api.webhooks.store');
        Route::get('/webhooks/eventos', [WebhooksController::class, 'eventos'])->name('api.webhooks.eventos');
        Route::get('/webhooks/{webhook}', [WebhooksController::class, 'show'])->name('api.webhooks.show');
        Route::put('/webhooks/{webhook}', [WebhooksController::class, 'update'])->name('api.webhooks.update');
        Route::delete('/webhooks/{webhook}', [WebhooksController::class, 'destroy'])->name('api.webhooks.destroy');
    });
