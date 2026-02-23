<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\MfaController;
use App\Http\Controllers\Tenant\PainelRiscoController;
use App\Http\Controllers\Tenant\AditivosController;
use App\Http\Controllers\Tenant\ContratosController;
use App\Http\Controllers\Tenant\DocumentosController;
use App\Http\Controllers\Tenant\ExecucoesFinanceirasController;
use App\Http\Controllers\Tenant\FiscaisController;
use App\Http\Controllers\Tenant\FornecedoresController;
use App\Http\Controllers\Tenant\PermissoesController;
use App\Http\Controllers\Tenant\RolesController;
use App\Http\Controllers\Tenant\SecretariasController;
use App\Http\Controllers\Tenant\ServidoresController;
use App\Http\Controllers\Tenant\AlertasController;
use App\Http\Controllers\Tenant\AuditoriaController;
use App\Http\Controllers\Tenant\RelatoriosController;
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

// Rotas MFA (autenticado, sem exigir verificação MFA)
Route::middleware(['tenant', 'auth'])->prefix('mfa')->name('tenant.mfa.')->group(function () {
    Route::get('setup', [MfaController::class, 'setup'])->name('setup');
    Route::post('enable', [MfaController::class, 'enable'])->name('enable');
    Route::get('verify', [MfaController::class, 'showVerify'])->name('verify');
    Route::post('verify', [MfaController::class, 'verify'])->name('verify.submit');
    Route::get('recovery', [MfaController::class, 'showRecovery'])->name('recovery');
    Route::post('recovery', [MfaController::class, 'useRecovery'])->name('recovery.submit');
    Route::post('disable', [MfaController::class, 'disable'])->name('disable');
});

// Rotas protegidas (autenticado + MFA verificado)
Route::middleware(['tenant', 'auth', 'mfa.verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('tenant.dashboard');
    Route::post('dashboard/atualizar', [DashboardController::class, 'atualizar'])
        ->name('tenant.dashboard.atualizar')
        ->middleware('permission:dashboard.atualizar');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('tenant.logout');

    // Monitoramento — Painel de Risco (RN-143)
    Route::get('painel-risco', [PainelRiscoController::class, 'index'])
        ->name('tenant.painel-risco.index')
        ->middleware('permission:painel-risco.visualizar');

    Route::get('painel-risco/exportar-tce', [PainelRiscoController::class, 'exportarRelatorioTCE'])
        ->name('tenant.painel-risco.exportar-tce')
        ->middleware('permission:painel-risco.exportar');

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

    // Gestao Contratual — Aditivos
    Route::get('aditivos', [AditivosController::class, 'index'])
        ->name('tenant.aditivos.index')
        ->middleware('permission:aditivo.visualizar');

    Route::get('contratos/{contrato}/aditivos/create', [AditivosController::class, 'create'])
        ->name('tenant.contratos.aditivos.create')
        ->middleware('permission:aditivo.criar');

    Route::post('contratos/{contrato}/aditivos', [AditivosController::class, 'store'])
        ->name('tenant.contratos.aditivos.store')
        ->middleware('permission:aditivo.criar');

    Route::get('aditivos/{aditivo}', [AditivosController::class, 'show'])
        ->name('tenant.aditivos.show')
        ->middleware('permission:aditivo.visualizar');

    Route::post('aditivos/{aditivo}/aprovar', [AditivosController::class, 'aprovar'])
        ->name('tenant.aditivos.aprovar')
        ->middleware('permission:aditivo.aprovar');

    Route::post('aditivos/{aditivo}/reprovar', [AditivosController::class, 'reprovar'])
        ->name('tenant.aditivos.reprovar')
        ->middleware('permission:aditivo.aprovar');

    Route::post('aditivos/{aditivo}/cancelar', [AditivosController::class, 'cancelar'])
        ->name('tenant.aditivos.cancelar')
        ->middleware('permission:aditivo.aprovar');

    // Gestao Contratual — Documentos
    Route::get('documentos', [DocumentosController::class, 'index'])
        ->name('tenant.documentos.index')
        ->middleware('permission:documento.visualizar');

    Route::post('contratos/{contrato}/documentos', [DocumentosController::class, 'store'])
        ->name('tenant.contratos.documentos.store')
        ->middleware('permission:documento.criar');

    Route::get('documentos/{documento}/download', [DocumentosController::class, 'download'])
        ->name('tenant.documentos.download')
        ->middleware('permission:documento.visualizar');

    Route::delete('documentos/{documento}', [DocumentosController::class, 'destroy'])
        ->name('tenant.documentos.destroy')
        ->middleware('permission:documento.excluir');

    Route::post('documentos/{documento}/verificar-integridade', [DocumentosController::class, 'verificarIntegridade'])
        ->name('tenant.documentos.verificar-integridade')
        ->middleware('permission:auditoria.verificar_integridade');

    // Administracao — Auditoria
    // IMPORTANTE: rotas estaticas antes de {tipo}/{id} parametrizado
    Route::post('auditoria/exportar/pdf', [AuditoriaController::class, 'exportarPdf'])
        ->name('tenant.auditoria.exportar.pdf')
        ->middleware(['permission:auditoria.exportar', 'throttle:exportacoes']);

    Route::post('auditoria/exportar/csv', [AuditoriaController::class, 'exportarCsv'])
        ->name('tenant.auditoria.exportar.csv')
        ->middleware(['permission:auditoria.exportar', 'throttle:exportacoes']);

    Route::get('auditoria', [AuditoriaController::class, 'index'])
        ->name('tenant.auditoria.index')
        ->middleware('permission:auditoria.visualizar');

    Route::get('auditoria/{tipo}/{id}', [AuditoriaController::class, 'show'])
        ->name('tenant.auditoria.show')
        ->middleware('permission:auditoria.visualizar')
        ->where('tipo', 'alteracao|login|acesso_documento');

    // Monitoramento — Alertas
    // IMPORTANTE: rotas estaticas antes de {alerta} para evitar conflito
    Route::get('alertas/configuracoes', [AlertasController::class, 'configuracoes'])
        ->name('tenant.alertas.configuracoes')
        ->middleware('permission:configuracao.editar');

    Route::post('alertas/configuracoes', [AlertasController::class, 'salvarConfiguracoes'])
        ->name('tenant.alertas.salvar-configuracoes')
        ->middleware('permission:configuracao.editar');

    Route::get('alertas', [AlertasController::class, 'index'])
        ->name('tenant.alertas.index')
        ->middleware('permission:alerta.visualizar');

    Route::get('alertas/{alerta}', [AlertasController::class, 'show'])
        ->name('tenant.alertas.show')
        ->middleware('permission:alerta.visualizar');

    Route::post('alertas/{alerta}/resolver', [AlertasController::class, 'resolver'])
        ->name('tenant.alertas.resolver')
        ->middleware('permission:alerta.resolver');

    // Notificacoes — Marcar como lida (AJAX)
    Route::post('notificacoes/{notification}/marcar-lida', function (\Illuminate\Http\Request $request, string $notification) {
        $request->user()->notifications()->where('id', $notification)->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    })->name('tenant.notificacoes.marcar-lida');

    // Monitoramento — Relatorios (RN-133, RN-222, RN-225)
    Route::get('relatorios', [RelatoriosController::class, 'index'])
        ->name('tenant.relatorios.index')
        ->middleware('permission:relatorio.visualizar');

    // IMPORTANTE: rotas estaticas antes de {contrato} parametrizado
    Route::get('relatorios/auditoria', [RelatoriosController::class, 'auditoriaFiltros'])
        ->name('tenant.relatorios.auditoria')
        ->middleware('permission:relatorio.gerar');

    Route::post('relatorios/auditoria/pdf', [RelatoriosController::class, 'auditoriaPdf'])
        ->name('tenant.relatorios.auditoria.pdf')
        ->middleware(['permission:relatorio.gerar', 'throttle:exportacoes']);

    Route::post('relatorios/auditoria/csv', [RelatoriosController::class, 'auditoriaCsv'])
        ->name('tenant.relatorios.auditoria.csv')
        ->middleware(['permission:relatorio.gerar', 'throttle:exportacoes']);

    Route::get('relatorios/conformidade-documental', [RelatoriosController::class, 'conformidadeDocumentalPdf'])
        ->name('tenant.relatorios.conformidade-documental')
        ->middleware(['permission:relatorio.gerar', 'throttle:exportacoes']);

    // RN-057: Efetividade mensal dos alertas
    Route::get('relatorios/efetividade-mensal', [RelatoriosController::class, 'efetividadeMensal'])
        ->name('tenant.relatorios.efetividade-mensal')
        ->middleware('permission:relatorio.gerar');

    Route::post('relatorios/efetividade-mensal/pdf', [RelatoriosController::class, 'efetividadeMensalPdf'])
        ->name('tenant.relatorios.efetividade-mensal.pdf')
        ->middleware(['permission:relatorio.gerar', 'throttle:exportacoes']);

    Route::get('relatorios/efetividade-mensal/excel', [RelatoriosController::class, 'efetividadeMensalExcel'])
        ->name('tenant.relatorios.efetividade-mensal.excel')
        ->middleware(['permission:relatorio.gerar', 'throttle:exportacoes']);

    Route::get('contratos/{contrato}/relatorio-documentos', [RelatoriosController::class, 'documentosContratoPdf'])
        ->name('tenant.relatorios.documentos-contrato')
        ->middleware(['permission:documento.download', 'throttle:exportacoes']);

    // Exportacoes Excel
    Route::get('exportar/contratos', [RelatoriosController::class, 'contratosExcel'])
        ->name('tenant.exportar.contratos')
        ->middleware(['permission:contrato.visualizar', 'throttle:exportacoes']);

    Route::get('exportar/alertas', [RelatoriosController::class, 'alertasExcel'])
        ->name('tenant.exportar.alertas')
        ->middleware(['permission:alerta.visualizar', 'throttle:exportacoes']);

    Route::get('exportar/fornecedores', [RelatoriosController::class, 'fornecedoresExcel'])
        ->name('tenant.exportar.fornecedores')
        ->middleware(['permission:fornecedor.visualizar', 'throttle:exportacoes']);

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

    Route::resource('servidores', ServidoresController::class)
        ->except(['show'])
        ->names('tenant.servidores')
        ->parameters(['servidores' => 'servidor'])
        ->middleware('permission:servidor.visualizar');

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
