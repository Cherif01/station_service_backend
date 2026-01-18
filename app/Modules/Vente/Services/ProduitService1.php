<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Produit;
use App\Modules\Vente\Resources\ProduitResource;
use Illuminate\Support\Facades\DB;

class ProduitService1
{
    public function index()
    {
        $produits = Produit::visible()->get();

        return response()->json([
            'status' => 200,
            'data'   => ProduitResource::collection($produits),
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
            'data'   => new ProduitResource($produit),
        ], 200);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {

            // qte_actuelle = qte_initiale si non fourni (intelligent)
            if (! isset($data['qte_actuelle'])) {
                $data['qte_actuelle'] = $data['qte_initiale'] ?? 0;
            }

            $produit = Produit::create($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Produit créé avec succès',
                'data'    => new ProduitResource($produit),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur création produit',
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
                'data'    => new ProduitResource($produit),
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
