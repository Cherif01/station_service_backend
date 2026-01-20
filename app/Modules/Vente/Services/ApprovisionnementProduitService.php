<?php
namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\ApprovisionnementProduit;
use App\Modules\Vente\Models\Produit;
use App\Modules\Vente\Resources\ApprovisionnementProduitResource;
use Exception;
use Illuminate\Support\Facades\DB;

class ApprovisionnementProduitService
{
    /**
     * =========================
     * LISTE DES APPROVISIONNEMENTS
     * =========================
     */
    public function getAll()
    {
        try {

            $items = ApprovisionnementProduit::visible()
                ->with('produit')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => ApprovisionnementProduitResource::collection($items),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des approvisionnements produit.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL D’UN APPROVISIONNEMENT
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $item = ApprovisionnementProduit::visible()
                ->with('produit')
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new ApprovisionnementProduitResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Approvisionnement produit introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * =========================
     * CRÉATION (TRANSACTION)
     * =========================
     */
    public function store(array $data)
    {
        try {

            DB::transaction(function () use ($data, &$appro) {

                // 🔐 Sécurité visibilité produit
                $produit = Produit::visible()
                    ->lockForUpdate()
                    ->findOrFail($data['id_produit']);

                // 1️⃣ Historique
                $appro = ApprovisionnementProduit::create($data);

                // 2️⃣ Mise à jour stock
                $produit->increment('qte_actuelle', $data['qte_appro']);
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Approvisionnement produit effectué avec succès.',
                'data'    => new ApprovisionnementProduitResource($appro),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de l’approvisionnement produit.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * MISE À JOUR (TRANSACTION)
     * =========================
     * ⚠️ Ajuste le stock selon la différence
     */
    public function update(int $id, array $data)
    {
        try {

            DB::transaction(function () use ($id, $data, &$appro) {

                $appro = ApprovisionnementProduit::visible()->findOrFail($id);

                $produit = Produit::visible()
                    ->lockForUpdate()
                    ->findOrFail($appro->id_produit);

                $ancienneQte = (float) $appro->qte_appro;
                $nouvelleQte = (float) ($data['qte_appro'] ?? $ancienneQte);
                $diff        = $nouvelleQte - $ancienneQte;

                // 1️⃣ Update historique
                $appro->update($data);

                // 2️⃣ Ajustement stock
                if ($diff !== 0.0) {
                    $produit->increment('qte_actuelle', $diff);
                }
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Approvisionnement produit modifié avec succès.',
                'data'    => new ApprovisionnementProduitResource($appro),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de l’approvisionnement produit.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION (TRANSACTION)
     * =========================
     * ⚠️ RÉTABLIT LE STOCK
     */
    public function delete(int $id)
    {
        try {

            DB::transaction(function () use ($id) {

                $appro = ApprovisionnementProduit::visible()->findOrFail($id);

                $produit = Produit::visible()
                    ->lockForUpdate()
                    ->findOrFail($appro->id_produit);

                // 🔻 Rétablir le stock
                $produit->decrement('qte_actuelle', $appro->qte_appro);

                // 🗑️ Suppression historique
                $appro->delete();
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Approvisionnement produit supprimé avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l’approvisionnement produit.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
