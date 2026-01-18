<?php
namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\ApprovisionnementCuve;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Resources\ApprovisionnementCuveResource;
use Exception;
use Illuminate\Support\Facades\DB;

class ApprovisionnementCuveService
{
    /**
     * =========================
     * LISTE DES APPROVISIONNEMENTS
     * =========================
     */
    public function getAll()
    {
        try {

            $items = ApprovisionnementCuve::visible()
                ->with('cuve')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => ApprovisionnementCuveResource::collection($items),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des approvisionnements.',
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

            $item = ApprovisionnementCuve::visible()
                ->with('cuve')
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new ApprovisionnementCuveResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Approvisionnement introuvable.',
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

                // 🔐 Sécurité visibilité cuve
                $cuve = Cuve::visible()->findOrFail($data['id_cuve']);

                // 1️⃣ Création historique
                $appro = ApprovisionnementCuve::create($data);

                // 2️⃣ Mise à jour stock
                $cuve->increment('qt_actuelle', $data['qte_appro']);
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Approvisionnement effectué avec succès.',
                'data'    => new ApprovisionnementCuveResource($appro),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de l’approvisionnement.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function retourcuve(array $data)
    {
        try {

            DB::transaction(function () use ($data, &$retour) {

                /**
                 * =================================================
                 * 🔐 SÉCURITÉ : CUVE VISIBLE + VERROU
                 * =================================================
                 */
                $cuve = Cuve::visible()
                    ->lockForUpdate()
                    ->findOrFail($data['id_cuve']);

                /**
                 * =================================================
                 * 1️⃣ CRÉATION HISTORIQUE (TYPE = retour_cuve)
                 * =================================================
                 */
                $retour = ApprovisionnementCuve::create([
                    'id_cuve'     => $data['id_cuve'],
                    'qte_appro'   => $data['qte_appro'],
                    'pu_unitaire' => $data['pu_unitaire'] ?? 0,
                    'type_appro'  => 'retour_cuve',

                ]);

                /**
                 * =================================================
                 * 2️⃣ AJUSTEMENT STOCK (+)
                 * =================================================
                 */
                $cuve->increment('qt_actuelle', $data['qte_appro']);
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Retour de cuve enregistré avec succès.',
                'data'    => new ApprovisionnementCuveResource($retour),
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’enregistrement du retour de cuve.',
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

                $appro = ApprovisionnementCuve::visible()->findOrFail($id);

                $cuve = Cuve::visible()->findOrFail($appro->id_cuve);

                // 🔢 Différence de quantité
                $ancienneQte = (float) $appro->qte_appro;
                $nouvelleQte = (float) ($data['qte_appro'] ?? $ancienneQte);
                $diff        = $nouvelleQte - $ancienneQte;

                // 1️⃣ Mise à jour historique
                $appro->update($data);

                // 2️⃣ Ajustement stock
                if ($diff !== 0.0) {
                    $cuve->increment('qt_actuelle', $diff);
                }
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Approvisionnement modifié avec succès.',
                'data'    => new ApprovisionnementCuveResource($appro),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de l’approvisionnement.',
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

                $appro = ApprovisionnementCuve::visible()->findOrFail($id);

                $cuve = Cuve::visible()->findOrFail($appro->id_cuve);

                // 🔻 Rétablir le stock
                $cuve->decrement('qt_actuelle', $appro->qte_appro);

                // 🗑️ Suppression historique
                $appro->delete();
            });

            return response()->json([
                'status'  => 200,
                'message' => 'Approvisionnement supprimé avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l’approvisionnement.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
