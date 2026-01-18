<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreInitVenteRequest;
use App\Modules\Vente\Requests\UpdateInitVenteRequest;
use App\Modules\Vente\Services\InitVenteService;

class InitVenteController extends Controller
{
    protected InitVenteService $service;

    public function __construct(InitVenteService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return $this->service->index();
    }

    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    public function store(StoreInitVenteRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(UpdateInitVenteRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function etat(int $id)
    {
        return $this->service->getEtatVente($id);
    }
}
