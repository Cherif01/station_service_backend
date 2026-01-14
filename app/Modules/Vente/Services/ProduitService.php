<?php
namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\ApprovisionnementCuve;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\VenteLitre;
use App\Modules\Vente\Resources\ProduitResource;
use Carbon\Carbon;
use Exception;

class ProduitService
{
    /**
     * =========================
     * LISTE DES CUVES
     * =========================
     */
    public function getAll()
    {
        try {

            $produits = Cuve::visible()
                ->orderBy('libelle')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => ProduitResource::collection($produits),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des cuves.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL D’UNE CUVE
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $produit = Cuve::visible()->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new ProduitResource($produit),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Cuve introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * =========================
     * CRÉATION (CUVE)
     * =========================
     */
    // public function store(array $data)
    // {
    //     try {

    //         $produit = Cuve::create($data);

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Cuve créée avec succès.',
    //             'data'    => new ProduitResource($produit),
    //         ]);

    //     } catch (Exception $e) {

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de la création de la cuve.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function store(array $data)
    {
        try {

            // =================================================
            // 🔹 INITIALISATION STOCK
            // qt_actuelle = qt_initial à la création
            // =================================================
            if (
                array_key_exists('qt_initial', $data)
                && ! array_key_exists('qt_actuelle', $data)
            ) {
                $data['qt_actuelle'] = $data['qt_initial'];
            }

            // =================================================
            // 🔹 CRÉATION CUVE
            // =================================================
            $produit = Cuve::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Cuve créée avec succès.',
                'data'    => new ProduitResource($produit),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la cuve.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * MODIFICATION (CUVE)
     * =========================
     */
    public function update(int $id, array $data)
    {
        try {

            $produit = Cuve::visible()->findOrFail($id);
            $produit->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Cuve modifiée avec succès.',
                'data'    => new ProduitResource($produit),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de la cuve.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION (CUVE)
     * =========================
     */
    public function delete(int $id)
    {
        try {

            $produit = Cuve::visible()->findOrFail($id);
            $produit->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Cuve supprimée avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la cuve.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function calculerParCuve(int $idCuve): array
    {
        $cuve = Cuve::visible()
            ->with('station:id,libelle')
            ->find($idCuve);

        if (! $cuve) {
            return [];
        }

        $lastDate = VenteLitre::visible()
            ->where('id_cuve', $idCuve)
            ->orderBy('created_at', 'desc')
            ->value('created_at');

        $date = $lastDate
            ? Carbon::parse($lastDate)->toDateString()
            : Carbon::today()->toDateString();

        /**
         * =========================
         * 🔹 STOCK MATIN (THÉORIQUE)
         * =========================
         */
        $stockMatin = VenteLitre::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->value('qte_vendu') ?? 0;

        /**
         * =========================
         * 🔹 ENTRÉES (RÉEL)
         * =========================
         */
        $entrees = ApprovisionnementCuve::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->where('type_appro', 'approvisionnement')
            ->sum('qte_appro');

        /**
         * =========================
         * 🔹 RETOUR CUVE (RÉEL)
         * =========================
         */
        $retourCuve = ApprovisionnementCuve::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->where('type_appro', 'retour_cuve')
            ->sum('qte_appro');

        /**
         * =========================
         * 🔹 SORTIES (VENTES)
         * =========================
         */
        $sorties = LigneVente::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->sum('qte_vendu');

        $stockTheorique = $stockMatin + $entrees + $retourCuve - $sorties;

        /**
         * =========================
         * 🔹 STOCK PHYSIQUE
         * =========================
         */
        $stockPhysique = VenteLitre::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->value('qte_vendu') ?? 0;

        $ecart = $stockPhysique - $stockTheorique;

        /**
         * =========================
         * 🔹 POMPES AYANT VENDU CETTE CUVE
         * =========================
         */
        $pompes = LigneVente::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->whereHas('affectation.pompe', fn($q) => $q->visible())
            ->with([
                'affectation.pompe:id,libelle',
            ])
            ->get()
            ->filter(fn($v) => $v->affectation && $v->affectation->pompe)
            ->groupBy(fn($v) => $v->affectation->pompe->id)
            ->map(function ($group) {
                $pompe = $group->first()->affectation->pompe;

                return [
                    'id'      => $pompe->id,
                    'libelle' => $pompe->libelle,
                    'volume'  => (float) $group->sum('qte_vendu'),
                ];
            })
            ->values()
            ->toArray();

        return [
            'date'            => $date,

            'station'         => [
                'id'      => $cuve->station->id,
                'libelle' => $cuve->station->libelle,
            ],

            'cuve'            => [
                'id'      => $cuve->id,
                'libelle' => $cuve->libelle,
            ],

            'pompes'          => $pompes,

            'stock_matin'     => (float) $stockMatin,
            'entrees'         => (float) $entrees,
            'retour_cuve'     => (float) $retourCuve,
            'sorties'         => (float) $sorties,
            'stock_theorique' => (float) $stockTheorique,
            'stock_physique'  => (float) $stockPhysique,
            'ecart'           => (float) $ecart,
        ];
    }

    /**
     * =================================================
     * 🔹 STOCK JOURNALIER DE TOUTES LES CUVES VISIBLES
     * =================================================
     */
    public function calculerToutesCuves()
    {
        $data = [];

        $cuves = Cuve::visible()
            ->where('status', true)
            ->orderBy('libelle')
            ->get();

        foreach ($cuves as $cuve) {
            $ligne = $this->calculerParCuve($cuve->id);

            if (! empty($ligne)) {
                $data[] = $ligne;
            }
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Stock journalier des cuves (logique station / Excel).',
            'data'    => $data,
        ], 200);
    }

}
