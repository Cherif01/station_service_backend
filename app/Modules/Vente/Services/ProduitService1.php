<?php
namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Produit;
use App\Modules\Vente\Resources\ProduitResource;
use App\Modules\Vente\Resources\ProduitResources;
use Illuminate\Support\Facades\DB;

class ProduitService1
{
    public function index()
    {
        $produits = Produit::visible()->get();

        return response()->json([
            'status' => 200,
            'data'   => ProduitResources::collection($produits),
        ], 200);
    }

    public function getOne(int $id)
    {
        $produit = Produit::visible()->find($id);

        if (! $produit) {
            return response()->json([
                'status'  => 404,
                'message' => 'Produit introuvable',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => new ProduitResources($produit),
        ], 200);
    }

    public function store(array $data)
    {
        DB::beginTransaction();

        try {

            /**
             * =================================================
             * 🔹 STATION ACTIVE (OBLIGATOIRE)
             * =================================================
             */
            $stationActiveId = request()->attributes->get('station_active_id');

            if (! $stationActiveId) {
                DB::rollBack();

                return response()->json([
                    'status'  => 400,
                    'message' => 'Aucune station active détectée.',
                ], 400);
            }

            /**
             * =================================================
             * 🔹 QUANTITÉ ACTUELLE PAR DÉFAUT
             * =================================================
             */
            if (! isset($data['qte_actuelle'])) {
                $data['qte_actuelle'] = $data['qte_initiale'] ?? 0;
            }

            /**
             * =================================================
             * 🔹 FORCER LA STATION (SÉCURITÉ)
             * =================================================
             */
            $data['id_station'] = $stationActiveId;

            /**
             * =================================================
             * 🔹 CRÉATION PRODUIT
             * =================================================
             */
            $produit = Produit::create($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Produit créé avec succès.',
                'data'    => new ProduitResources($produit),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur création produit.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(int $id, array $data)
    {
        DB::beginTransaction();
        try {

            $produit = Produit::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $produit) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Produit introuvable',
                ], 404);
            }

            $produit->update($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Produit mis à jour',
                'data'    => new ProduitResources($produit),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur mise à jour produit',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(int $id)
    {
        DB::beginTransaction();
        try {

            $produit = Produit::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $produit) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Produit introuvable',
                ], 404);
            }

            // désactivation intelligente
            $produit->update(['status' => false]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Produit désactivé',
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur suppression produit',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
