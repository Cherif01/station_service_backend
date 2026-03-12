<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreProduitRequest;
use App\Modules\Vente\Requests\UpdateProduitRequest;
use App\Modules\Vente\Services\CuveService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CuveController extends Controller
{
    public function __construct(
        protected CuveService $service
    ) {}

    /**
     * =========================
     * LISTE DES CUVES
     * =========================
     */
    public function index(): JsonResponse
    {
        return $this->service->getAll();
    }

    /**
     * =========================
     * DÉTAIL CUVE
     * =========================
     */
    public function show(int $id): JsonResponse
    {
        return $this->service->getOne($id);
    }

    /**
     * =========================
     * CRÉATION CUVE
     * =========================
     */
    public function store(StoreProduitRequest $request): JsonResponse
    {
        return $this->service->store($request->validated());
    }

    /**
     * =========================
     * MODIFICATION CUVE
     * =========================
     */
    public function update(UpdateProduitRequest $request, int $id): JsonResponse
    {
        return $this->service->update($id, $request->validated());
    }

    /**
     * =========================
     * SUPPRESSION CUVE
     * =========================
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * =================================================
     * 🔹 STOCK JOURNALIER DE TOUTES LES CUVES
     * =================================================
     */
    // public function calculerStockJournalierToutesCuves(): JsonResponse
    // {
    //     return $this->service->calculerToutesCuves();
    // }

    // /**
    //  * =================================================
    //  * 🔹 STOCK DES CUVES ENTRE DEUX DATES
    //  * =================================================
    //  */
    // public function calculerToutesCuvesEntreDates(Request $request): JsonResponse
    // {
    //     return $this->service->calculerToutesCuvesEntreDates(
    //         $request->query('date_debut'),
    //         $request->query('date_fin')
    //     );
    // }



     /**
     * Stock journalier de toutes les cuves
     * GET /cuves/stock-journalier?date=2026-03-12
     */
    public function stockJournalier(Request $request): JsonResponse
    {
        $date = $request->query('date');
        return $this->service->calculerStockJournalier($date);
    }
}