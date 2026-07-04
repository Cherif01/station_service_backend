<?php
namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\LigneVenteRequest;
use App\Modules\Vente\Services\LigneVenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LigneVenteController extends Controller
{
    public function __construct(
        private readonly LigneVenteService $service
    ) {}

    /**
     * Liste des ventes
     */
    public function index(): JsonResponse
    {
        return $this->service->getAll();
    }
    public function index1(): JsonResponse
    {
        return $this->service->getAll1();
    }

    /**
     * Détail d'une vente
     */
    public function show(int $id): JsonResponse
    {
        return $this->service->getOne($id);
    }

    /**
     * Création d'une vente
     */

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_station' => ['required', 'exists:stations,id'],
        ]);

        return $this->service->store($data);
    }

    /**
     * Mise à jour d'une vente
     */
    public function update(LigneVenteRequest $request, int $id): JsonResponse
    {
        return $this->service->update($id, $request->validated());
    }

    /**
     * Suppression / annulation d'une vente
     * Paramètre optionnel : raison (string) — obligatoire pour les ventes validées
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        return $this->service->delete($id, $request->only(['raison']));
    }

    /**
     * GET /lignes-vente/releve-journalier?date_debut=2026-03-01&date_fin=2026-03-12
     */
    public function venteJournaliere(Request $request): JsonResponse
    {
        return $this->service->venteJournaliere(
            $request->query('date_debut'),
            $request->query('date_fin')
        );
    }

}
