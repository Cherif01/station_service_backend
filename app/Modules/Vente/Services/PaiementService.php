<?php

namespace App\Modules\Vente\Services;

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
    public function store(array $data)
    {
        DB::beginTransaction();
        try {

            $paiement = Paiement::create($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Paiement enregistré',
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
                'message' => 'Erreur lors de l’enregistrement du paiement',
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
}
