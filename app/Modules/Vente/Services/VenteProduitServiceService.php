<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\InitVente;
use App\Modules\Vente\Models\Produit;
use App\Modules\Vente\Models\Service;
use App\Modules\Vente\Models\VenteProduitService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VenteProduitServiceService
{
    /**
     * =================================================
     * 🔹 CRÉATION D’UNE VENTE COMPLÈTE
     * =================================================
     */
public function store(array $data)
{
    DB::beginTransaction();

    try {

        $user = Auth::user();

        if (! $user) {
            DB::rollBack();
            return response()->json([
                'status'  => 401,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $idStation = request()->attributes->get('station_active_id');

        if (! $idStation) {
            DB::rollBack();
            return response()->json([
                'status'  => 400,
                'message' => 'Aucune station active détectée.',
            ], 400);
        }

        $affectation = $user->activeAffectation();

        if (! $affectation) {
            DB::rollBack();
            return response()->json([
                'status'  => 403,
                'message' => 'Aucune affectation active.',
            ], 403);
        }

        /**
         * =================================================
         * 1️⃣ INIT VENTE
         * =================================================
         */
        $initVente = InitVente::create([
            'id_client'      => $data['id_client'],
            'id_affectation' => $affectation->id,
            'status'         => false,
        ]);

        /**
         * =================================================
         * 2️⃣ PRODUITS (AVEC CONTRÔLE DE STOCK)
         * =================================================
         */
        if (! empty($data['ids_produits'])) {

            foreach ($data['ids_produits'] as $item) {

                $qteVendue = (float) $item['qte_vendu'];

                if ($qteVendue <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 400,
                        'message' => 'La quantité vendue doit être supérieure à zéro.',
                    ], 400);
                }

                // 🔐 Verrouillage pessimiste
                $produit = Produit::visible()
                    ->lockForUpdate()
                    ->findOrFail($item['id']);

                // ❌ Stock insuffisant
                if ($qteVendue > (float) $produit->qte_actuelle) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 400,
                        'message' => "Stock insuffisant pour le produit « {$produit->libelle} ».",
                        'details' => [
                            'stock_disponible' => (float) $produit->qte_actuelle,
                            'quantite_demandee' => $qteVendue,
                        ],
                    ], 400);
                }

                // 1️⃣ Création ligne de vente
                VenteProduitService::create([
                    'id_init_vente' => $initVente->id,
                    'id_produit'    => $produit->id,
                    'qte_vendu'     => $qteVendue,
                    'prix_unitaire' => $produit->prix_unitaire,
                ]);

                // 2️⃣ Mise à jour stock produit
                $produit->decrement('qte_actuelle', $qteVendue);
            }
        }

        /**
         * =================================================
         * 3️⃣ SERVICES (PAS DE STOCK)
         * =================================================
         */
        if (! empty($data['ids_services'])) {

            foreach ($data['ids_services'] as $idService) {

                $service = Service::visible()
                    ->findOrFail($idService);

                VenteProduitService::create([
                    'id_init_vente' => $initVente->id,
                    'id_service'    => $service->id,
                    'qte_vendu'     => 1,
                    'prix_unitaire' => $service->prix,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'status'  => 200,
            'message' => 'Vente créée avec succès.',
            'data'    => $initVente->load([
                'client',
                'lignes.produit',
                'lignes.service',
                'createdBy',
            ]),
        ], 200);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur création vente.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    /**
     * =================================================
     * 🔹 UNE VENTE (COMPLÈTE)
     * =================================================
     */
    public function getOne(int $id)
    {
        $vente = InitVente::visible()
            ->with([
                'client',
                'lignes.produit',
                'lignes.service',
                'paiements',
                'createdBy',
                'modifiedBy',
            ])
            ->find($id);

        if (! $vente) {
            return response()->json([
                'status'  => 404,
                'message' => 'Vente introuvable.',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => $vente,
        ], 200);
    }

    /**
     * =================================================
     * 🔹 MISE À JOUR DES LIGNES DE VENTE
     * =================================================
     */
    public function update(int $id, array $data)
    {
        DB::beginTransaction();

        try {

            $vente = InitVente::lockForUpdate()->find($id);

            if (! $vente) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Vente introuvable.',
                ], 404);
            }

            /**
             * 🔹 On supprime les anciennes lignes
             */
            VenteProduitService::where('id_init_vente', $vente->id)->delete();

            /**
             * 🔹 On recrée les lignes
             */
            if (! empty($data['ids_produits'])) {
                foreach ($data['ids_produits'] as $item) {
                    VenteProduitService::create([
                        'id_init_vente' => $vente->id,
                        'id_produit'    => $item['id'],
                        'qte_vendu'     => $item['qte_vendu'],
                        'prix_unitaire' => $item['prix_unitaire'] ?? 0,
                    ]);
                }
            }

            if (! empty($data['ids_services'])) {
                foreach ($data['ids_services'] as $idService) {
                    VenteProduitService::create([
                        'id_init_vente' => $vente->id,
                        'id_service'    => $idService,
                        'qte_vendu'     => 1,
                        'prix_unitaire' => 0,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente mise à jour.',
                'data'    => $vente->load([
                    'client',
                    'lignes.produit',
                    'lignes.service',
                    'modifiedBy',
                ]),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur mise à jour vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * 🔹 SUPPRESSION D’UNE VENTE
     * =================================================
     */
    public function delete(int $id)
    {
        DB::beginTransaction();

        try {

            $vente = InitVente::lockForUpdate()->find($id);

            if (! $vente) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Vente introuvable.',
                ], 404);
            }

            /**
             * 🔹 Suppression des lignes
             */
            VenteProduitService::where('id_init_vente', $vente->id)->delete();

            /**
             * 🔹 Suppression de la vente
             */
            $vente->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente supprimée.',
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur suppression vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
