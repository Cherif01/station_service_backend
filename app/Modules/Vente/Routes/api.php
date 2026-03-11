<?php

use App\Modules\Vente\Controllers\ApprovisionnementCuveController;
use App\Modules\Vente\Controllers\ClientController;
use App\Modules\Vente\Controllers\CreanceController;
use App\Modules\Vente\Controllers\CuveController;
use App\Modules\Vente\Controllers\InitVenteController;
use App\Modules\Vente\Controllers\LigneVenteController;
use App\Modules\Vente\Controllers\PaiementController;
use App\Modules\Vente\Controllers\PerteCuveController;
use App\Modules\Vente\Controllers\ProduitController1;

use App\Modules\Vente\Controllers\ServiceController;
use App\Modules\Vente\Controllers\ValidationVenteController;
use App\Modules\Vente\Controllers\VenteLitreController;
use App\Modules\Vente\Controllers\VenteProduitServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['station.db', 'auth:sanctum', 'active.st'])->prefix('v1/vente')->group(function () {
   
    //  Route::apiResource('ligne-ventes',LigneVenteController::class);
    Route::get(
        'statistiques/cuves/journalier',
        [CuveController::class, 'calculerStockJournalierToutesCuves']
    );

      Route::get(
        '/statistiques/cuves/periode',
        [CuveController::class, 'calculerToutesCuvesEntreDates']
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
    Route::get(
        'releve-pompes',
        [LigneVenteController::class, 'index1']
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
    Route::apiResource('mesure-cuves', VenteLitreController::class);
    Route::apiResource('perte-cuves', PerteCuveController::class);
    Route::apiResource('produits', ProduitController1::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('paiements', PaiementController::class);
    Route::apiResource('clients', ClientController::class);
     Route::apiResource('creances', CreanceController::class);
    Route::apiResource('init-ventes', InitVenteController::class);
    Route::apiResource('initiation', LigneVenteController::class);
  Route::apiResource(
    'vente-produits-services',
    VenteProduitServiceController::class
);



});
