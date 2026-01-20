<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreProduit1Request;
use App\Modules\Vente\Requests\StoreProduitRequest;
use App\Modules\Vente\Requests\UpdateProduit1Request;
use App\Modules\Vente\Requests\UpdateProduitRequest;
use App\Modules\Vente\Services\ProduitService1;

class ProduitController1 extends Controller
{
    protected ProduitService1 $service;

    public function __construct(ProduitService1 $service)
    {
        $this->service = $service;
    }

    /**
     * =================================================
     * 🔹 LISTE DES PRODUITS
     * =================================================
     */
    public function index()
    {
        return $this->service->index();
    }

    /**
     * =================================================
     * 🔹 UN PRODUIT
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
    public function store(StoreProduit1Request $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * =================================================
     * 🔹 MISE À JOUR
     * =================================================
     */
    public function update(UpdateProduit1Request $request, int $id)
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
