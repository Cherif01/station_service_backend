<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreServiceRequest;
use App\Modules\Vente\Requests\UpdateServiceRequest;
use App\Modules\Vente\Services\ServiceService;

class ServiceController extends Controller
{
    protected ServiceService $service;

    public function __construct(ServiceService $service)
    {
        $this->service = $service;
    }

    /**
     * =================================================
     * 🔹 LISTE DES SERVICES
     * =================================================
     */
    public function index()
    {
        return $this->service->index();
    }

    /**
     * =================================================
     * 🔹 UN SERVICE
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
    public function store(StoreServiceRequest $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * =================================================
     * 🔹 MISE À JOUR
     * =================================================
     */
    public function update(UpdateServiceRequest $request, int $id)
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
