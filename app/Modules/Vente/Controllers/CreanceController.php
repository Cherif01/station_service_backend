<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreCreanceRequest;
use App\Modules\Vente\Services\CreanceService;

class CreanceController extends Controller
{
    protected CreanceService $creanceService;

    public function __construct(CreanceService $creanceService)
    {
        $this->creanceService = $creanceService;
    }

    /**
     * =================================================
     * 🔹 LISTE DES CRÉANCES
     * =================================================
     */
    public function index()
    {
        return $this->creanceService->getListeInitVentesCreance();
    }

    /**
     * =================================================
     * 🔹 CRÉATION CRÉANCES
     * =================================================
     */
    public function store(StoreCreanceRequest $request)
    {
        return $this->creanceService->store(
            $request->validated()
        );
    }
}
