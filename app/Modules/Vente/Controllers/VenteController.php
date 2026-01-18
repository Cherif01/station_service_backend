<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreVenteProduitServiceRequest;
use App\Modules\Vente\Requests\UpdateVenteProduitServiceRequest;
use App\Modules\Vente\Services\VenteProduitServiceService;

class VenteController extends Controller
{
    protected VenteProduitServiceService $service;

    public function __construct(VenteProduitServiceService $service)
    {
        $this->service = $service;
    }

    /**
     * =================================================
     * 🔹 CRÉATION VENTE
     * =================================================
     */
    public function store(StoreVenteProduitServiceRequest $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * =================================================
     * 🔹 UNE VENTE
     * =================================================
     */
    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    /**
     * =================================================
     * 🔹 MISE À JOUR VENTE
     * =================================================
     */
    public function update(UpdateVenteProduitServiceRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    /**
     * =================================================
     * 🔹 SUPPRESSION VENTE
     * =================================================
     */
    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
