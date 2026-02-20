<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ContratosController;
use App\Http\Controllers\Tenant\ExecucoesFinanceirasController;
use App\Http\Controllers\Tenant\FiscaisController;
use App\Http\Controllers\Tenant\FornecedoresController;
use App\Http\Controllers\Tenant\PermissoesController;
use App\Http\Controllers\Tenant\RolesController;
use App\Http\Controllers\Tenant\SecretariasController;
use App\Http\Controllers\Tenant\UsersController;
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

    // Gestao Contratual — Contratos
    Route::resource('contratos', ContratosController::class)
        ->names('tenant.contratos')
        ->parameters(['contratos' => 'contrato'])
        ->middleware('permission:contrato.visualizar');

    // Gestao Contratual — Fiscais (aninhado ao contrato)
    Route::post('contratos/{contrato}/fiscais', [FiscaisController::class, 'store'])
        ->name('tenant.contratos.fiscais.store')
        ->middleware('permission:fiscal.criar');

    // Gestao Contratual — Execucoes Financeiras (aninhado ao contrato)
    Route::post('contratos/{contrato}/execucoes', [ExecucoesFinanceirasController::class, 'store'])
        ->name('tenant.contratos.execucoes.store')
        ->middleware('permission:financeiro.registrar_empenho');

    // Cadastros
    Route::resource('secretarias', SecretariasController::class)
        ->except(['show'])
        ->names('tenant.secretarias')
        ->middleware('permission:secretaria.visualizar');

    Route::resource('fornecedores', FornecedoresController::class)
        ->except(['show'])
        ->names('tenant.fornecedores')
        ->parameters(['fornecedores' => 'fornecedor'])
        ->middleware('permission:fornecedor.visualizar');

    // Administracao — Usuarios
    Route::resource('usuarios', UsersController::class)
        ->except(['show'])
        ->names('tenant.users')
        ->parameters(['usuarios' => 'user'])
        ->middleware('permission:usuario.visualizar');

    // Administracao — Perfis
    Route::resource('perfis', RolesController::class)
        ->except(['show'])
        ->names('tenant.roles')
        ->parameters(['perfis' => 'role'])
        ->middleware('permission:configuracao.visualizar');

    // Administracao — Permissoes por Perfil
    Route::get('perfis/{role}/permissoes', [PermissoesController::class, 'index'])
        ->name('tenant.permissoes.index')
        ->middleware('permission:configuracao.editar');

    Route::put('perfis/{role}/permissoes', [PermissoesController::class, 'update'])
        ->name('tenant.permissoes.update')
        ->middleware('permission:configuracao.editar');
});
