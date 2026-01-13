<?php

use App\Http\Middleware\SetActiveStation;
use App\Http\Middleware\SetStationDatabase;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        /**
         * 🔹 Alias des middlewares
         */
        $middleware->alias([
          
           
            // Orpailleurs
            'station.db' => SetStationDatabase::class,

            // Auth & permissions
            'auth:sanctum'  => EnsureFrontendRequestsAreStateful::class,
             'active.st'=> SetActiveStation::class,
            'abilities'     => CheckAbilities::class,
            'ability'       => CheckForAnyAbility::class,
        ]);

        /**
         * 🔹 Priorité d’exécution
         * (DB switch AVANT auth, policies, services)
         */
        $middleware->priority([
           
            SetStationDatabase::class,
            
            EnsureFrontendRequestsAreStateful::class,
            
            SetActiveStation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gestion globale des exceptions (optionnel)
    })
    ->create();
