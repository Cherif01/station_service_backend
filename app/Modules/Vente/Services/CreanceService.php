<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Creance;
use App\Modules\Vente\Resources\CreanceResource;
use Illuminate\Support\Facades\DB;

class CreanceService
{
    /**
     * =================================================
     * 🔹 LISTE DES CRÉANCES
     * =================================================
     */
    public function index()
    {
        $creances = Creance::visible()
            ->with(['client', 'pompe', 'paiements', 'paiements.createdBy', 'createdBy', 'modifiedBy'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => CreanceResource::collection($creances),
        ], 200);
    }

    /**
     * =================================================
     * 🔹 UNE CRÉANCE
     * =================================================
     */
    public function getOne(int $id)
    {
        $creance = Creance::visible()
            ->with(['client', 'pompe', 'paiements', 'paiements.createdBy', 'createdBy', 'modifiedBy'])
            ->find($id);

        if (! $creance) {
            return response()->json([
                'status'  => 404,
                'message' => 'Créance introuvable.',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => new CreanceResource($creance),
        ], 200);
    }

    /**
     * =================================================
     * 🔹 CRÉATION
     * =================================================
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {

            $creance = Creance::create($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Créance enregistrée avec succès.',
                'data'    => new CreanceResource(
                    $creance->load(['client', 'pompe', 'paiements', 'createdBy'])
                ),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la créance.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * 🔹 MISE À JOUR
     * =================================================
     */
    public function update(int $id, array $data)
    {
        DB::beginTransaction();

        try {

            $creance = Creance::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $creance) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Créance introuvable.',
                ], 404);
            }

            $creance->update($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Créance mise à jour.',
                'data'    => new CreanceResource(
                    $creance->load(['client', 'pompe', 'paiements', 'createdBy', 'modifiedBy'])
                ),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour de la créance.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * 🔹 SUPPRESSION
     * =================================================
     */
    public function delete(int $id)
    {
        DB::beginTransaction();

        try {

            $creance = Creance::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $creance) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Créance introuvable.',
                ], 404);
            }

            $creance->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Créance supprimée.',
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la créance.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * 🔹 ÉTAT MÉTIER D'UNE CRÉANCE
     * =================================================
     */
    public function getEtatCreance(int $idCreance): array
    {
        $creance = Creance::visible()
            ->with(['client', 'paiements'])
            ->find($idCreance);

        if (! $creance) {
            return [
                'status'      => 404,
                'message'     => 'Créance introuvable.',
                'client'      => null,
                'facturation' => [],
            ];
        }

        $client = [
            'id'          => $creance->client?->id,
            'nom_complet' => $creance->client?->nom_complet,
            'telephone'   => $creance->client?->telephone,
        ];

        $totalCreance = (float) $creance->montant;
        $totalPaye    = (float) $creance->paiements->sum('montant_payer');
        $reste        = max($totalCreance - $totalPaye, 0);

        if ($totalPaye <= 0) {
            $etat = 'non_paye';
        } elseif ($totalPaye < $totalCreance) {
            $etat = 'partiellement_paye';
        } else {
            $etat = 'totalement_paye';
        }

        return [
            'status'      => 200,
            'client'      => $client,
            'facturation' => [
                'total_creance' => round($totalCreance, 2),
                'total_paye'    => round($totalPaye, 2),
                'reste_a_payer' => round($reste, 2),
                'etat'          => $etat,
            ],
        ];
    }
}
