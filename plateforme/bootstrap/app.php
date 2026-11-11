<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureLeader;
use App\Http\Middleware\EnsureProfileCompleted;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'profile.completed' => EnsureProfileCompleted::class,
            'leader' => EnsureLeader::class,
            'admin' => EnsureAdmin::class,
        ]);
        // Vercel serves the app behind its proxy: trust it so HTTPS and client IPs are right.
        $middleware->trustProxies(at: env('VERCEL') ? '*' : null);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
