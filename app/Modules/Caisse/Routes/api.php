<?php

use App\Modules\Caisse\Controllers\ChargeCategoryController;
use App\Modules\Caisse\Controllers\CompteController;
use App\Modules\Caisse\Controllers\OperationChargeController;
use App\Modules\Caisse\Controllers\OperationCompteController;
use App\Modules\Caisse\Controllers\TypeOperationController;
use Illuminate\Support\Facades\Route;

// Define API routes for Caisse module here
Route::middleware(['station.db', 'auth:sanctum', 'active.st'])
    ->prefix('v1/caisse')
    ->group(function () {

        Route::apiResource('comptes', CompteController::class);
        Route::apiResource('categori-charges', ChargeCategoryController::class);
        Route::apiResource('operations-charges', OperationChargeController::class);

        Route::apiResource('type-operations', TypeOperationController::class);

        Route::apiResource('operations', OperationCompteController::class);

        // =============================================
        // TRANSFERT INTER-STATION
        // =============================================
        Route::post(
            'operations/transfert',
            [OperationCompteController::class, 'transfer']
        );
        Route::get(
            'transfert',
            [OperationCompteController::class, 'listeTransfet']
        );

        // =============================================
        // CONFIRMATION TRANSFERT
        // =============================================
        Route::post(
            'operations/transfert/confirm',
            [OperationCompteController::class, 'confirm']
        );

        // =============================================
        // ANNULATION TRANSFERT
        // =============================================
        Route::post(
            'operations/transfert/cancel',
            [OperationCompteController::class, 'cancel']
        );

        Route::get(
            'operations-comptes/periode',
            [OperationCompteController::class, 'getAllByPeriode']
        );

        Route::get(
            'transfert-intercomptes/periode',
            [OperationCompteController::class, 'getAllTransfertsByPeriode']
        );

        

Route::get('resume-mensuel', [OperationCompteController::class, 'resumeMensuel']);

    });
