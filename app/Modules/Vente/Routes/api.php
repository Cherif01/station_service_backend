<?php

use App\Modules\Vente\Controllers\ApprovisionnementCuveController;
use App\Modules\Vente\Controllers\ClientController;
use App\Modules\Vente\Controllers\CreanceController;
use App\Modules\Vente\Controllers\CuveController;
use App\Modules\Vente\Controllers\InitVenteController;
use App\Modules\Vente\Controllers\JaugeageController;
use App\Modules\Vente\Controllers\LigneVenteController;
use App\Modules\Vente\Controllers\PaiementController;
use App\Modules\Vente\Controllers\PerteCuveController;


use Illuminate\Support\Facades\Route;

Route::middleware(['station.db', 'auth:sanctum', 'active.st'])->prefix('v1/vente')->group(function () {
   
    

    Route::get('/carburant/stock-journalier', [CuveController::class, 'stockEntreDates']);

    Route::get('/journalier/carburant', [LigneVenteController::class, 'venteJournaliere']);

   
    Route::get(
        'releve-pompes',
        [LigneVenteController::class, 'index1']
    );

   

    Route::post(
        'retour-cuves',
        [ApprovisionnementCuveController::class, 'retourcuve']
    );

    Route::apiResource('appro', ApprovisionnementCuveController::class);

    Route::apiResource('mesure-cuves', JaugeageController::class)
        ->only(['index', 'show', 'store', 'destroy']);
    Route::apiResource('perte-cuves', PerteCuveController::class);
  
    Route::apiResource('paiements', PaiementController::class);
    Route::apiResource('clients', ClientController::class);
     Route::apiResource('creances', CreanceController::class);

    Route::apiResource('initiation', LigneVenteController::class);



});
