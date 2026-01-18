<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StorePaiementRequest;
use App\Modules\Vente\Requests\UpdatePaiementRequest;
use App\Modules\Vente\Services\PaiementService;

class PaiementController extends Controller
{
    protected PaiementService $service;

    public function __construct(PaiementService $service)
    {
        $this->service = $service;
    }

    /**
     * =================================================
     * 🔹 LISTE
     * =================================================
     */
    public function index()
    {
        return $this->service->index();
    }

    /**
     * =================================================
     * 🔹 UN PAIEMENT
     * =================================================
     */
    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    /**
     * =================================================
     * 🔹 CRÉATION
     * =================================================
     */
    public function store(StorePaiementRequest $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * =================================================
     * 🔹 MISE À JOUR
     * =================================================
     */
    public function update(UpdatePaiementRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    /**
     * =================================================
     * 🔹 SUPPRESSION
     * =================================================
     */
    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
