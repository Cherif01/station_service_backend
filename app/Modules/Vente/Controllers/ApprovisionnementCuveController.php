<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\ApprovisionnementCuveRequest;
use App\Modules\Vente\Requests\StoreRetourCuveRequest;
use App\Modules\Vente\Services\ApprovisionnementCuveService;

class ApprovisionnementCuveController extends Controller
{
    public function __construct(
        protected ApprovisionnementCuveService $service
    ) {}

    /**
     * =========================
     * LISTE DES APPROVISIONNEMENTS
     * =========================
     */
    public function index()
    {
        return $this->service->getAll();
    }

    /**
     * =========================
     * DÉTAIL D’UN APPROVISIONNEMENT
     * =========================
     */
    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    /**
     * =========================
     * CRÉATION
     * =========================
     */
    public function store(ApprovisionnementCuveRequest $request)
    {
        return $this->service->store(
            $request->validated()
        );
    }

      public function retourcuve(ApprovisionnementCuveRequest $request)
    {
        return $this->service->retourcuve(
            $request->validated()
        );
    }

    /**
     * =========================
     * MISE À JOUR
     * =========================
     */
    public function update(
        ApprovisionnementCuveRequest $request,
        int $id
    ) {
        return $this->service->update(
            $id,
            $request->validated()
        );
    }

    /**
     * =========================
     * SUPPRESSION
     * =========================
     */
    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
