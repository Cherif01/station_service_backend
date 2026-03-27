<?php

namespace App\Modules\Vente\Services;

use App\Modules\Caisse\Models\Compte;
use App\Modules\Vente\Models\Paiement;
use App\Modules\Vente\Resources\PaiementResource;
use Illuminate\Support\Facades\DB;

class PaiementService
{
    /**
     * =================================================
     * 🔹 LISTE DES PAIEMENTS
     * =================================================
     */
    public function index()
    {
        $paiements = Paiement::visible()
            ->with(['creance', 'creance.client', 'compte', 'createdBy'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => PaiementResource::collection($paiements),
        ], 200);
    }

    /**
     * =================================================
     * 🔹 UN PAIEMENT
     * =================================================
     */
    public function getOne(int $id)
    {
        $paiement = Paiement::visible()
            ->with(['creance', 'creance.client', 'compte', 'createdBy'])
            ->find($id);

        if (! $paiement) {
            return response()->json([
                'status'  => 404,
                'message' => 'Paiement introuvable.',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => new PaiementResource($paiement),
        ], 200);
    }

    /**
     * =================================================
     * 🔹 CRÉATION
     * =================================================
     */
    // public function store(array $data)
    // {
    //     DB::beginTransaction();

    //     try {

    //         /**
    //          * =============================================
    //          * 1️⃣ VÉRIFICATION ÉTAT DE LA CRÉANCE
    //          * =============================================
    //          */
    //         $etatCreance = app(CreanceService::class)
    //             ->getEtatCreance($data['id_creance']);

    //         if ($etatCreance['status'] !== 200) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'status'  => 404,
    //                 'message' => 'Créance introuvable.',
    //             ], 404);
    //         }

    //         $facturation = $etatCreance['facturation'];

    //         /**
    //          * =============================================
    //          * 2️⃣ CRÉANCE DÉJÀ SOLDÉE
    //          * =============================================
    //          */
    //         if ($facturation['etat'] === 'totalement_paye') {
    //             DB::rollBack();
    //             return response()->json([
    //                 'status'  => 422,
    //                 'message' => 'Cette créance est déjà totalement payée.',
    //             ], 422);
    //         }

    //         /**
    //          * =============================================
    //          * 3️⃣ MONTANT INVALIDE
    //          * =============================================
    //          */
    //         if ($data['montant_payer'] <= 0) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'status'  => 422,
    //                 'message' => 'Le montant du paiement doit être supérieur à zéro.',
    //             ], 422);
    //         }

    //         if ($data['montant_payer'] > $facturation['reste_a_payer']) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'status'  => 422,
    //                 'message' => 'Le montant dépasse le reste à payer (' . $facturation['reste_a_payer'] . ').',
    //             ], 422);
    //         }

    //         /**
    //          * =============================================
    //          * 4️⃣ CRÉATION DU PAIEMENT
    //          * =============================================
    //          */
    //         $paiement = Paiement::create($data);

    //         DB::commit();

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Paiement enregistré avec succès.',
    //             'data'    => new PaiementResource(
    //                 $paiement->load(['creance', 'creance.client', 'compte', 'createdBy'])
    //             ),
    //         ], 200);

    //     } catch (\Throwable $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de l\'enregistrement du paiement.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function store(array $data)
{
    DB::beginTransaction();

    try {

        /**
         * =============================================
         * 1️⃣ VÉRIFICATION ÉTAT DE LA CRÉANCE
         * =============================================
         */
        $etatCreance = app(CreanceService::class)
            ->getEtatCreance($data['id_creance']);

        if ($etatCreance['status'] !== 200) {
            DB::rollBack();
            return response()->json([
                'status'  => 404,
                'message' => 'Créance introuvable.',
            ], 404);
        }

        $facturation = $etatCreance['facturation'];

        /**
         * =============================================
         * 2️⃣ CRÉANCE DÉJÀ SOLDÉE
         * =============================================
         */
        if ($facturation['etat'] === 'totalement_paye') {
            DB::rollBack();
            return response()->json([
                'status'  => 422,
                'message' => 'Cette créance est déjà totalement payée.',
            ], 422);
        }

        /**
         * =============================================
         * 3️⃣ MONTANT INVALIDE
         * =============================================
         */
        if ($data['montant_payer'] <= 0) {
            DB::rollBack();
            return response()->json([
                'status'  => 422,
                'message' => 'Le montant du paiement doit être supérieur à zéro.',
            ], 422);
        }

        if ($data['montant_payer'] > $facturation['reste_a_payer']) {
            DB::rollBack();
            return response()->json([
                'status'  => 422,
                'message' => 'Le montant dépasse le reste à payer (' . $facturation['reste_a_payer'] . ').',
            ], 422);
        }

        /**
         * =============================================
         * 4️⃣ RÉCUPÉRATION DU PREMIER COMPTE DE LA STATION
         * =============================================
         */
        $id_station = auth()->user()->id_station;

        $compte = Compte::where('id_station', $id_station)
            ->orderBy('id', 'asc')
            ->first();

        if (! $compte) {
            DB::rollBack();
            return response()->json([
                'status'  => 404,
                'message' => 'Aucun compte trouvé pour cette station.',
            ], 404);
        }

        $data['id_compte'] = $compte->id;

        /**
         * =============================================
         * 5️⃣ CRÉATION DU PAIEMENT
         * =============================================
         */
        $paiement = Paiement::create($data);

        DB::commit();

        return response()->json([
            'status'  => 200,
            'message' => 'Paiement enregistré avec succès.',
            'data'    => new PaiementResource(
                $paiement->load(['creance', 'creance.client', 'compte', 'createdBy'])
            ),
        ], 200);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de l\'enregistrement du paiement.',
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

            $paiement = Paiement::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $paiement) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Paiement introuvable.',
                ], 404);
            }

            $paiement->update($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Paiement mis à jour.',
                'data'    => new PaiementResource(
                    $paiement->load(['creance', 'creance.client', 'compte', 'createdBy'])
                ),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour du paiement.',
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

            $paiement = Paiement::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $paiement) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Paiement introuvable.',
                ], 404);
            }

            $paiement->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Paiement supprimé.',
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du paiement.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * 🔹 TOTAL PAYÉ POUR LA STATION ACTIVE
     * =================================================
     */
    public function getTotalMontantPayeStation(): float
    {
        return (float) Paiement::whereHas('creance', function ($q) {
            $q->visible();
        })->sum('montant_payer');
    }
}
