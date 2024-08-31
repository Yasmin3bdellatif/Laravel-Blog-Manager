<?php

use App\Jobs\FetchDataJob;
use App\Jobs\ForceDeleteOldPostsJob;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Console\Scheduling\Schedule;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add Sanctum middleware
        $middleware->api([
            EnsureFrontendRequestsAreStateful::class,
        ]);

        // You can add other middleware here as well
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new ForceDeleteOldPostsJob)->everyMinute();
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new FetchDataJob)->everySixHours();
    })
    ->create();





