<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Controllers\DashboardController;

Route::middleware(['station.db','auth:sanctum','active.st'])
    ->prefix('v1/dashboard')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index']);
         Route::post('/rapport', [DashboardController::class, 'rapport']);

    });
