<?php

use App\Modules\Settings\Controllers\ParametrageStationController;
use App\Modules\Settings\Controllers\PaysController;
use App\Modules\Settings\Controllers\PompeController;
use App\Modules\Settings\Controllers\SettingController;
use App\Modules\Settings\Controllers\VilleController;
use App\Modules\Settings\Controllers\StationController;
use Illuminate\Support\Facades\Route;
Route::middleware(['station.db','auth:sanctum','active.st' ])->prefix('v1/settings')->group(function () {
    Route::apiResource('pays',PaysController::class);
    Route::apiResource('villes', VilleController::class);
    Route::apiResource('stations', StationController::class);
    Route::apiResource('params', ParametrageStationController::class);
    Route::apiResource('pompes', PompeController::class);
    Route::apiResource('configs', SettingController::class);
    Route::get('pompes-dispo',[PompeController::class,'pompesDisponibles']);
    Route::get('/activate/stations/{id}/', [StationController::class, 'activateStation']);

    
  

    
   
});
