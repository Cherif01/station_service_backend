<?php

use App\Modules\Vente\Controllers\ApprovisionnementCuveController;
use App\Modules\Vente\Controllers\LigneVenteController;
use App\Modules\Vente\Controllers\PerteCuveController;
use App\Modules\Vente\Controllers\ProduitController;
use App\Modules\Vente\Controllers\ValidationVenteController;
use App\Modules\Vente\Controllers\VenteLitreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['station.db', 'auth:sanctum','active.st'])->prefix('v1/vente')->group(function () {
    Route::apiResource('cuves', ProduitController::class);
    //  Route::apiResource('ligne-ventes',LigneVenteController::class);
     Route::get(
        'statistiques/cuves/journalier',
        [ProduitController::class, 'calculerStockJournalierToutesCuves']
    );
    Route::post(
        'ligne-ventes/index-fin/{id}',
        [LigneVenteController::class, 'update']
    );
     Route::get(
        'liste/lignes-ventes',
        [LigneVenteController::class, 'index']
    );

    Route::get(
        'liste',
        [ValidationVenteController::class, 'index']
    );

    Route::post(
        'validation/ligne-ventes',
        [ValidationVenteController::class, 'store']
    );

     Route::post(
        'retour-cuves',
        [ApprovisionnementCuveController::class, 'retourcuve']
    );

    Route::apiResource('appro', ApprovisionnementCuveController::class);
    Route::apiResource('validation', ValidationVenteController::class);
    Route::apiResource('vente-par-litre', VenteLitreController::class);
    Route::apiResource('perte-cuves', PerteCuveController::class);

});
