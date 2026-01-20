<?php
namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\ApprovisionnementProduitRequest;
use App\Modules\Vente\Services\ApprovisionnementProduitService;

class ApprovisionnementProduitController extends Controller
{
    public function __construct(
        protected ApprovisionnementProduitService $service
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
    public function store(ApprovisionnementProduitRequest $request)
    {
        return $this->service->store(
            $request->validated()
        );
    }

    /**
     * =========================
     * MISE À JOUR
     * =========================
     */

    
    public function update(
        ApprovisionnementProduitRequest $request,
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
