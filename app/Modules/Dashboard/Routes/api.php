<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Controllers\DashboardController;

Route::middleware(['station.db','auth:sanctum'])
    ->prefix('v1/dashboard')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index']);
         Route::get('/rapport', [DashboardController::class, 'rapport']);

    });
