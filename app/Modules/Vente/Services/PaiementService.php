<?php
namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Paiement;
use App\Modules\Vente\Resources\PaiementResource;
use App\Modules\Vente\Services\InitVenteService;
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
            ->with([
                'vente',
                'createdBy', // 🔹 IMPORTANT
            ])
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
            ->with([
                'vente',
                'createdBy', // 🔹 IMPORTANT
            ])
            ->find($id);

        if (! $paiement) {
            return response()->json([
                'status'  => 404,
                'message' => 'Paiement introuvable',
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

    //         $paiement = Paiement::create($data);

    //         DB::commit();

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Paiement enregistré',
    //             'data'    => new PaiementResource(
    //                 $paiement->load([
    //                     'vente',
    //                     'createdBy', // 🔹 IMPORTANT
    //                 ])
    //             ),
    //         ], 200);

    //     } catch (\Throwable $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de l’enregistrement du paiement',
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
             * 1️⃣ VÉRIFICATION ÉTAT DE LA VENTE
             * =============================================
             */
            $etatVente = app(InitVenteService::class)
                ->getEtatVente($data['id_init_vente']);

            if ($etatVente['status'] !== 200) {
                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'message' => 'Vente introuvable.',
                ], 404);
            }

            $facturation = $etatVente['facturation'];

            /**
             * =============================================
             * 2️⃣ VENTE DÉJÀ SOLDÉE
             * =============================================
             */
            if ($facturation['etat'] === 'totalement_paye') {
                DB::rollBack();

                return response()->json([
                    'status'  => 422,
                    'message' => 'Cette vente est déjà totalement payée.',
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
                    'message' => 'Le montant dépasse le reste à payer.',
                ], 422);
            }

            /**
             * =============================================
             * 4️⃣ CRÉATION DU PAIEMENT
             * =============================================
             */
            $paiement = Paiement::create($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Paiement enregistré avec succès.',
                'data'    => new PaiementResource(
                    $paiement->load([
                        'vente',
                        'createdBy',
                    ])
                ),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’enregistrement du paiement.',
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
                    'message' => 'Paiement introuvable',
                ], 404);
            }

            $paiement->update($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Paiement mis à jour',
                'data'    => new PaiementResource(
                    $paiement->load([
                        'vente',
                        'createdBy', // 🔹 IMPORTANT
                    ])
                ),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur mise à jour paiement',
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
                    'message' => 'Paiement introuvable',
                ], 404);
            }

            $paiement->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Paiement supprimé',
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur suppression paiement',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getTotalMontantPayeStation(): float
    {
        return (float) Paiement::whereHas('vente', function ($q) {
            $q->visible(); // scope station côté InitVente
        })
            ->sum('montant_payer');
    }

}
