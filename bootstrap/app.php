<?php

use App\Console\Commands\CheckOverdueTasks;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            \App\Http\Middleware\LogRequest::class,
        ]);

        $middleware->alias([
            'logRequest' => \App\Http\Middleware\LogRequest::class,
            'checkUserStatus' => \App\Http\Middleware\CheckUserStatus::class,
        ]);
    })
    ->withCommands([
        CheckOverdueTasks::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
