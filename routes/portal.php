<?php

use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Portal\SolicitacaoLaiPublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Publico — Transparencia Ativa (LAI 12.527/2011)
|--------------------------------------------------------------------------
|
| Rotas publicas acessiveis sem autenticacao.
| Middleware ResolveTenantPublic resolve o tenant pelo slug na URL.
|
*/

Route::middleware(['web', 'tenant.public'])->prefix('{slug}/portal')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('portal.index');
    Route::get('/contratos', [PortalController::class, 'contratos'])->name('portal.contratos');
    Route::get('/contratos/{numero}', [PortalController::class, 'contratoDetalhe'])->where('numero', '.*')->name('portal.contratos.show');
    Route::get('/fornecedores', [PortalController::class, 'fornecedores'])->name('portal.fornecedores');
    Route::get('/logo', [PortalController::class, 'logo'])->name('portal.logo');
    Route::get('/dados-abertos', [PortalController::class, 'dadosAbertos'])->name('portal.dados-abertos');

    // SIC/e-SIC — Transparencia Passiva (IMP-058)
    Route::get('/lai', [SolicitacaoLaiPublicController::class, 'create'])->name('portal.lai.create');
    Route::post('/lai', [SolicitacaoLaiPublicController::class, 'store'])->name('portal.lai.store');
    Route::get('/lai/consultar', [SolicitacaoLaiPublicController::class, 'consultar'])->name('portal.lai.consultar');
    Route::get('/lai/{protocolo}', [SolicitacaoLaiPublicController::class, 'show'])->name('portal.lai.show');
});
