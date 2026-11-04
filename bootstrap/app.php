<?php

use App\Http\Middleware\EnsureChildAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['child.access' => EnsureChildAccess::class]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.home'));

        // Hinter dem Plesk-/Apache-Proxy von netcup kommt HTTPS per X-Forwarded-Proto an.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
