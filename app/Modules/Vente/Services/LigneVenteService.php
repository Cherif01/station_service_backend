<?php
namespace App\Modules\Vente\Services;

use App\Modules\Caisse\Models\Compte;
use App\Modules\Caisse\Models\OperationCompte;
use App\Modules\Settings\Models\Affectation;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\ValidationVente;
use App\Modules\Vente\Resources\LigneVenteResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class LigneVenteService
{
    /**
     * =========================
     * LISTE DES LIGNES DE VENTE
     * =========================
     */
    public function getAll(): JsonResponse
    {
        try {
            $items = LigneVente::visible()
                ->with([
                    'station',
                    'cuve',
                    'affectation.pompe.station',
                    'affectation.user',
                    'createdBy',
                    'modifiedBy',
                ])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => LigneVenteResource::collection($items),
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des lignes de vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL D’UNE LIGNE DE VENTE
     * =========================
     */
    public function getOne(int $id): JsonResponse
    {
        try {
            $item = LigneVente::visible()
                ->with([
                    'station',
                    'cuve',
                    'affectation.pompe.station',
                    'affectation.user',
                    'createdBy',
                    'modifiedBy',
                ])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new LigneVenteResource($item),
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Ligne de vente introuvable.',
            ], 404);
        }
    }

    /**
     * =========================
     * CRÉATION
     * =========================
     */
    public function store(array $data): JsonResponse
    {
        try {
            $item = LigneVente::create($data);

            return response()->json([
                'status'  => 201,
                'message' => 'Ligne de vente créée avec succès.',
                'data'    => new LigneVenteResource($item),
            ], 201);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la ligne de vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * MISE À JOUR / CLÔTURE
     * =========================
     */

  
    public function update(int $id, array $data): JsonResponse
    {
        DB::beginTransaction();

        try {

            /**
             * =================================================
             * 1. LIGNE DE VENTE VISIBLE + VERROU
             * =================================================
             */
            $item = LigneVente::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $item) {
                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'message' => 'Ligne de vente introuvable.',
                ], 404);
            }

            /**
             * =================================================
             * 2. DÉJÀ VALIDÉE ?
             * =================================================
             */
            if ((bool) $item->status === true) {
                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Cette vente est déjà validée.',
                ], 409);
            }

            /**
             * =================================================
             * 3. INDEX DE FIN
             * =================================================
             */
            $indexDebut = (float) $item->index_debut;
            $indexFin   = $data['index_fin'] ?? null;

            if ($indexFin === null) {
                DB::rollBack();

                return response()->json([
                    'status'  => 400,
                    'message' => 'Index fin requis pour la validation.',
                ], 400);
            }

            $indexFin = (float) $indexFin;

            if ($indexFin < $indexDebut) {
                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Index incohérent : index_fin < index_debut.',
                ], 409);
            }

            /**
             * =================================================
             * 4. QUANTITÉ VENDUE
             * =================================================
             */
            $qteVendu = $indexFin - $indexDebut;

            if ($qteVendu <= 0) {
                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Quantité vendue invalide.',
                ], 409);
            }

            /**
             * =================================================
             * 🔥 5. CUVE (VERROU + CONTRÔLE STOCK)
             * =================================================
             */
            $cuve = Cuve::lockForUpdate()->find($item->id_cuve);

            if (! $cuve) {
                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'message' => 'Cuve introuvable.',
                ], 404);
            }

            if ($qteVendu > $cuve->qt_actuelle) {
                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Stock insuffisant dans la cuve pour clôturer la vente.',
                ], 409);
            }

            /**
             * =================================================
             * 6. DÉDUCTION STOCK CUVE (UNE SEULE FOIS)
             * =================================================
             */
            $cuve->update([
                'qt_actuelle' => $cuve->qt_actuelle - $qteVendu,
            ]);

            /**
             * =================================================
             * 7. MISE À JOUR LIGNE DE VENTE
             * =================================================
             */
            $item->update([
                'index_fin' => $indexFin,
                'qte_vendu' => $qteVendu,
                'status'    => true, // 🔒 vente clôturée
            ]);

            /**
             * =================================================
             * 8. CRÉATION VALIDATION VENTE
             * =================================================
             */
            ValidationVente::create([
                'id_vente'    => $item->id,
                'commentaire' => $data['commentaire'] ?? null,
            ]);

            /**
             * =================================================
             * 9. DÉSACTIVATION AFFECTATION
             * =================================================
             */
            if ($item->id_affectation) {

                $affectation = Affectation::where('id', $item->id_affectation)
                    ->where('status', true)
                    ->lockForUpdate()
                    ->first();

                if (! $affectation) {
                    DB::rollBack();

                    return response()->json([
                        'status'  => 409,
                        'message' => 'Aucune affectation active trouvée pour cette vente.',
                    ], 409);
                }

                $affectation->update([
                    'status' => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente clôturée, validée et stock cuve mis à jour.',
                'data'    => new LigneVenteResource($item->fresh()),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur interne lors de la clôture de la vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    //la vrai fonction
// public function update(int $id, array $data): JsonResponse
// {
//     DB::beginTransaction();

//     try {

//         /**
//          * =================================================
//          * 1. LIGNE DE VENTE VISIBLE + VERROU
//          * =================================================
//          */
//         $item = LigneVente::visible()
//             ->lockForUpdate()
//             ->find($id);

//         if (! $item) {
//             DB::rollBack();

//             return response()->json([
//                 'status'  => 404,
//                 'message' => 'Ligne de vente introuvable.',
//             ], 404);
//         }

//         /**
//          * =================================================
//          * 2. DÉJÀ VALIDÉE ?
//          * =================================================
//          */
//         if ((bool) $item->status === true) {
//             DB::rollBack();

//             return response()->json([
//                 'status'  => 409,
//                 'message' => 'Cette vente est déjà validée.',
//             ], 409);
//         }

//         /**
//          * =================================================
//          * 3. INDEX DE FIN
//          * =================================================
//          */
//         $indexDebut = (float) $item->index_debut;
//         $indexFin   = $data['index_fin'] ?? null;

//         if ($indexFin === null) {
//             DB::rollBack();

//             return response()->json([
//                 'status'  => 400,
//                 'message' => 'Index fin requis pour la validation.',
//             ], 400);
//         }

//         $indexFin = (float) $indexFin;

//         if ($indexFin < $indexDebut) {
//             DB::rollBack();

//             return response()->json([
//                 'status'  => 409,
//                 'message' => 'Index incohérent : index_fin < index_debut.',
//             ], 409);
//         }

//         /**
//          * =================================================
//          * 4. QUANTITÉ VENDUE
//          * =================================================
//          */
//         $qteVendu = $indexFin - $indexDebut;

//         if ($qteVendu <= 0) {
//             DB::rollBack();

//             return response()->json([
//                 'status'  => 409,
//                 'message' => 'Quantité vendue invalide.',
//             ], 409);
//         }

//         /**
//          * =================================================
//          * 5. CUVE (VERROU + CONTRÔLE STOCK)
//          * =================================================
//          */
//         $cuve = Cuve::lockForUpdate()->find($item->id_cuve);

//         if (! $cuve) {
//             DB::rollBack();

//             return response()->json([
//                 'status'  => 404,
//                 'message' => 'Cuve introuvable.',
//             ], 404);
//         }

//         if ($qteVendu > $cuve->qt_actuelle) {
//             DB::rollBack();

//             return response()->json([
//                 'status'  => 409,
//                 'message' => 'Stock insuffisant dans la cuve pour clôturer la vente.',
//             ], 409);
//         }

//         /**
//          * =================================================
//          * 6. DÉDUCTION STOCK CUVE (UNE SEULE FOIS)
//          * =================================================
//          */
//         $cuve->update([
//             'qt_actuelle' => $cuve->qt_actuelle - $qteVendu,
//         ]);

//         /**
//          * =================================================
//          * 7. MISE À JOUR LIGNE DE VENTE
//          * =================================================
//          */
//         $item->update([
//             'index_fin' => $indexFin,
//             'qte_vendu' => $qteVendu,
//             'status'    => true,
//         ]);

//         /**
//          * =================================================
//          * 8. CRÉATION VALIDATION VENTE
//          * =================================================
//          */
//         ValidationVente::create([
//             'id_vente'    => $item->id,
//             'commentaire' => $data['commentaire'] ?? null,
//         ]);

//         /**
//          * =================================================
//          * 9. DÉSACTIVATION AFFECTATION
//          * =================================================
//          */
//         if ($item->id_affectation) {

//             $affectation = Affectation::where('id', $item->id_affectation)
//                 ->where('status', true)
//                 ->lockForUpdate()
//                 ->first();

//             if (! $affectation) {
//                 DB::rollBack();

//                 return response()->json([
//                     'status'  => 409,
//                     'message' => 'Aucune affectation active trouvée pour cette vente.',
//                 ], 409);
//             }

//             $affectation->update([
//                 'status' => false,
//             ]);
//         }

//         /**
//          * =================================================
//          * 🔟 ÉCRITURE COMPTABLE (ENTRÉE)
//          * =================================================
//          */
//         $puVente = (float) $cuve->pu_vente;
//         $montant = $qteVendu * $puVente;

//         $compte = Compte::where('id_station', $item->id_station)
//             ->lockForUpdate()
//             ->first();

//         if (! $compte) {
//             DB::rollBack();

//             return response()->json([
//                 'status'  => 500,
//                 'message' => 'Compte de la station introuvable.',
//             ], 500);
//         }

//         OperationCompte::create([
//             'id_compte'         => $compte->id,
//             'id_type_operation' => 1, // ENTRÉE
//             'montant'           => $montant,
//             'libelle'           => 'Vente carburant - ' . $cuve->libelle,
//             'reference'         => 'VENTE-' . $item->id,
           
//         ]);

//         DB::commit();

//         return response()->json([
//             'status'  => 200,
//             'message' => 'Vente clôturée, validée, stock et compte mis à jour.',
//             'data'    => new LigneVenteResource($item->fresh()),
//         ], 200);

//     } catch (\Throwable $e) {

//         DB::rollBack();

//         return response()->json([
//             'status'  => 500,
//             'message' => 'Erreur interne lors de la clôture de la vente.',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }



    /**
     * =========================
     * SUPPRESSION
     * =========================
     */
    // public function delete(int $id): JsonResponse
    // {
    //     try {
    //         $item = LigneVente::visible()->findOrFail($id);
    //         $item->delete();

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Ligne de vente supprimée avec succès.',
    //         ], 200);

    //     } catch (Throwable $e) {
    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de la suppression de la ligne de vente.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function delete(int $id): JsonResponse
    {
        DB::beginTransaction();

        try {

            /**
             * =================================================
             * 1. LIGNE DE VENTE VISIBLE + VERROU
             * =================================================
             */
            $item = LigneVente::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $item) {
                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'message' => 'Ligne de vente introuvable.',
                ], 404);
            }

            /**
             * =================================================
             * 2. SI VENTE VALIDÉE → RESTAURATION STOCK CUVE
             * =================================================
             */
            if ((bool) $item->status === true && $item->qte_vendu > 0) {

                $cuve = Cuve::lockForUpdate()->find($item->id_cuve);

                if (! $cuve) {
                    DB::rollBack();

                    return response()->json([
                        'status'  => 404,
                        'message' => 'Cuve introuvable pour restauration du stock.',
                    ], 404);
                }

                // 🔺 On remet le stock
                $cuve->update([
                    'qt_actuelle' => $cuve->qt_actuelle + $item->qte_vendu,
                ]);
            }

            /**
             * =================================================
             * 3. SUPPRESSION DE LA LIGNE DE VENTE
             * =================================================
             */
            $item->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente supprimée et stock cuve restauré avec succès.',
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la ligne de vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
