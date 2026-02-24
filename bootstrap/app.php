<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin-saas.php'));
        },
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('dashboard:agregar')
            ->dailyAt('05:30')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/dashboard-agregacao.log'));

        $schedule->command('alertas:verificar-vencimentos')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/alertas-verificacao.log'));

        $schedule->command('permissoes:verificar-expiradas')
            ->dailyAt('01:00')
            ->withoutOverlapping();

        $schedule->command('documentos:verificar-integridade')
            ->weeklyOn(0, '02:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/integridade-documentos.log'));
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\SetTenantConnection::class,
            'admin.saas' => \App\Http\Middleware\EnsureAdminSaaS::class,
            'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
            'mfa.verified' => \App\Http\Middleware\EnsureMfaVerified::class,
            'force.https' => \App\Http\Middleware\ForceHttps::class,
            'user.active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\SetTenantConnection::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin-saas/*')) {
                return route('admin-saas.login');
            }

            return route('tenant.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
