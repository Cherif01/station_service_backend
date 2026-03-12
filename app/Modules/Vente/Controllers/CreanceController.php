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
        return $this->creanceService->index();
    }

    /**
     * =================================================
     * 🔹 UNE CRÉANCE
     * =================================================
     */
    public function show(int $id)
    {
        return $this->creanceService->getOne($id);
    }

    /**
     * =================================================
     * 🔹 CRÉATION
     * =================================================
     */
    public function store(StoreCreanceRequest $request)
    {
        return $this->creanceService->store(
            $request->validated()
        );
    }

    /**
     * =================================================
     * 🔹 MISE À JOUR
     * =================================================
     */
    public function update(StoreCreanceRequest $request, int $id)
    {
        return $this->creanceService->update($id, $request->validated());
    }

    /**
     * =================================================
     * 🔹 SUPPRESSION
     * =================================================
     */
    public function destroy(int $id)
    {
        return $this->creanceService->delete($id);
    }
}
