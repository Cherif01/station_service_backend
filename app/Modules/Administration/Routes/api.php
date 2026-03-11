<?php

use App\Modules\Administration\Controllers\UserController;
use App\Modules\Settings\Controllers\AffectationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admin')->middleware('station.db')->group(function () {
    Route::post('/login', [UserController::class, 'login']);

    Route::middleware(['auth:sanctum','active.st'])->group(function () {
        Route::apiResource('users', UserController::class);
        Route::get('pompiste-dispo',[UserController::class,'pompisteDisp']);
        Route::apiResource('affectations', AffectationController::class);
    });
});
