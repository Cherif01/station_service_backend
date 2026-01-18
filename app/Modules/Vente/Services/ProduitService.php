<?php
namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\ApprovisionnementCuve;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\PerteCuve;
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

// public function calculerToutesCuvesEntreDates(string $dateDebut, string $dateFin)
// {
//     $data = [];

//     $start = Carbon::parse($dateDebut)->startOfDay();
//     $end   = Carbon::parse($dateFin)->endOfDay();

//     $cuves = Cuve::visible()
//         ->where('status', true)
//         ->with('station:id,libelle')
//         ->orderBy('libelle')
//         ->get();

//     foreach ($cuves as $cuve) {

//         /**
//          * =========================
//          * 🔹 APPROVISIONNEMENTS (LIGNES)
//          * =========================
//          */
//         $approvisionnements = ApprovisionnementCuve::visible()
//             ->where('id_cuve', $cuve->id)
//             ->whereBetween('created_at', [$start, $end])
//             ->orderBy('created_at', 'asc')
//             ->get()
//             ->map(fn ($a) => [
//                 'date'        => $a->created_at?->toDateString(),
//                 'qte_appro'   => (float) $a->qte_appro,
//                 'pu_unitaire' => (float) ($a->pu_unitaire ?? 0),
//                 'type_appro'  => $a->type_appro,
//             ])
//             ->toArray();

//         /**
//          * =========================
//          * 🔹 PERTES (LIGNES)
//          * =========================
//          */
//         $pertes = PerteCuve::visible()
//             ->where('id_cuve', $cuve->id)
//             ->whereBetween('created_at', [$start, $end])
//             ->orderBy('created_at', 'asc')
//             ->get()
//             ->map(fn ($p) => [
//                 'date'      => $p->created_at?->toDateString(),
//                 'qte_perte' => (float) $p->qte_perte,
//                 'motif'     => $p->motif,
//             ])
//             ->toArray();

//         /**
//          * =========================
//          * 🔹 JAUGEAGE (VENTE_LITRE) + ÉCART LIGNE/LIGNE
//          * =========================
//          */
//         $jaugeagesBruts = VenteLitre::visible()
//             ->where('id_cuve', $cuve->id)
//             ->whereBetween('created_at', [$start, $end])
//             ->orderBy('created_at', 'asc')
//             ->get();

//         $jaugeages = [];
//         $previousStock = null;

//         foreach ($jaugeagesBruts as $j) {

//             $stock = (float) $j->qte_vendu;

//             $ecart = $previousStock === null
//                 ? 0
//                 : $stock - $previousStock;

//             $jaugeages[] = [
//                 'date'  => $j->created_at?->toDateString(),
//                 'stock' => $stock,
//                 'ecart' => (float) $ecart,
//             ];

//             $previousStock = $stock;
//         }

//         /**
//          * =========================
//          * 🔹 SYNTHÈSE (MÊME IDÉE QUE TON JOURNALIER)
//          * =========================
//          */
//         $stockMatin = $jaugeages[0]['stock'] ?? 0;

//         $entrees = collect($approvisionnements)
//             ->where('type_appro', 'approvisionnement')
//             ->sum('qte_appro');

//         $retourCuve = collect($approvisionnements)
//             ->where('type_appro', 'retour_cuve')
//             ->sum('qte_appro');

//         $perteCuve = collect($pertes)->sum('qte_perte');

//         $sorties = LigneVente::visible()
//             ->where('id_cuve', $cuve->id)
//             ->whereBetween('created_at', [$start, $end])
//             ->sum('qte_vendu');

//         $stockTheorique = $stockMatin + $entrees + $retourCuve - $sorties - $perteCuve;

//         $stockPhysique = ! empty($jaugeages)
//             ? (float) $jaugeages[count($jaugeages) - 1]['stock']
//             : 0;

//         $data[$cuve->libelle] = [
//             'date' => $start->toDateString() . ' → ' . $end->toDateString(),

//             'station' => [
//                 'id'      => $cuve->station->id,
//                 'libelle' => $cuve->station->libelle,
//             ],

//             'cuve' => [
//                 'id'      => $cuve->id,
//                 'libelle' => $cuve->libelle,
//             ],

//             // 🔹 LIGNES
//             'approvisionnement_cuve' => $approvisionnements,
//             'perte_cuve'             => $pertes,
//             'jaugeage'               => $jaugeages,

//             // 🔹 SYNTHÈSE
//             'valeur_en_litre'     => (float) $stockMatin,
//             'entrees'         => (float) $entrees,
//             'retour_cuve'     => (float) $retourCuve,
//             'sorties'         => (float) $sorties,
//             'stock_theorique' => (float) $stockTheorique,
//             'stock_physique'  => (float) $stockPhysique,
//             'ecart'           => (float) ($stockPhysique - $stockTheorique),

//             'pompes' => [],
//         ];
//     }

//     return response()->json([
//         'status' => 200,
//         'data'   => $data,
//     ], 200);
// }
    // public function calculerToutesCuvesEntreDates(string $dateDebut, string $dateFin)
    // {
    //     $data = [];

    //     $start = Carbon::parse($dateDebut)->startOfDay();
    //     $end   = Carbon::parse($dateFin)->endOfDay();

    //     $cuves = Cuve::visible()
    //         ->where('status', true)
    //         ->with('station:id,libelle')
    //         ->orderBy('libelle')
    //         ->get();

    //     foreach ($cuves as $cuve) {

    //         /**
    //          * =========================
    //          * 🔹 APPROVISIONNEMENTS (LIGNES)
    //          * =========================
    //          */
    //         $approvisionnements = ApprovisionnementCuve::visible()
    //             ->where('id_cuve', $cuve->id)
    //             ->whereBetween('created_at', [$start, $end])
    //             ->orderBy('created_at', 'asc')
    //             ->get()
    //             ->map(fn($a) => [
    //                 'date'        => $a->created_at?->toDateString(),
    //                 'qte_appro'   => (float) $a->qte_appro,
    //                 'pu_unitaire' => (float) ($a->pu_unitaire ?? 0),
    //                 'type_appro'  => $a->type_appro,
    //             ])
    //             ->toArray();

    //         /**
    //          * =========================
    //          * 🔹 PERTES (LIGNES)
    //          * =========================
    //          */
    //         $pertes = PerteCuve::visible()
    //             ->where('id_cuve', $cuve->id)
    //             ->whereBetween('created_at', [$start, $end])
    //             ->orderBy('created_at', 'asc')
    //             ->get()
    //             ->map(fn($p) => [
    //                 'date'      => $p->created_at?->toDateString(),
    //                 'qte_perte' => (float) $p->qte_perte,
    //                 'motif'     => $p->motif,
    //             ])
    //             ->toArray();

    //         /**
    //          * =========================
    //          * 🔹 JAUGEAGE + ÉCART LIGNE / LIGNE
    //          * =========================
    //          */
    //         $jaugeagesBruts = VenteLitre::visible()
    //             ->where('id_cuve', $cuve->id)
    //             ->whereBetween('created_at', [$start, $end])
    //             ->orderBy('created_at', 'asc')
    //             ->get();

    //         $jaugeages     = [];
    //         $ecartGlobal   = 0.0;
    //         $previousStock = null;

    //         foreach ($jaugeagesBruts as $j) {

    //             $stock = (float) $j->qte_vendu;

    //             $ecartLigne = 0.0;

    //             if ($previousStock !== null) {
    //                 // 👉 logique métier validée : ancien - suivant
    //                 $ecartLigne   = $previousStock - $stock;
    //                 $ecartGlobal += $ecartLigne;
    //             }

    //             $jaugeages[] = [
    //                 'date'  => $j->created_at?->toDateString(),
    //                 'stock' => $stock,
    //                 'ecart' => (float) $ecartLigne,
    //             ];

    //             $previousStock = $stock;
    //         }

    //         /**
    //          * =========================
    //          * 🔹 SYNTHÈSE
    //          * =========================
    //          */
    //         $stockMatin = $jaugeages[0]['stock'] ?? 0;

    //         $entrees = collect($approvisionnements)
    //             ->where('type_appro', 'approvisionnement')
    //             ->sum('qte_appro');

    //         $retourCuve = collect($approvisionnements)
    //             ->where('type_appro', 'retour_cuve')
    //             ->sum('qte_appro');

    //         $perteCuve  = collect($pertes)->sum('qte_perte');

    //         $sorties = LigneVente::visible()
    //             ->where('id_cuve', $cuve->id)
    //             ->whereBetween('created_at', [$start, $end])
    //             ->sum('qte_vendu');

    //         $stockTheorique = $stockMatin + $entrees + $retourCuve - $sorties - $perteCuve;

    //         $stockPhysique = ! empty($jaugeages)
    //             ? (float) $jaugeages[count($jaugeages) - 1]['stock']
    //             : 0;

    //         /**
    //          * =========================
    //          * 🔹 STRUCTURE FINALE (PAR LIBELLÉ CUVE)
    //          * =========================
    //          */
    //         $data[$cuve->libelle] = [
    //             'date'                   => $start->toDateString() . ' → ' . $end->toDateString(),

    //             'station'                => [
    //                 'id'      => $cuve->station->id,
    //                 'libelle' => $cuve->station->libelle,
    //             ],

    //             'cuve'                   => [
    //                 'id'      => $cuve->id,
    //                 'libelle' => $cuve->libelle,
    //             ],

    //             // 🔹 LIGNES
    //             'approvisionnement_cuve' => $approvisionnements,
    //             'perte_cuve'             => $pertes,
    //             'jaugeage'               => $jaugeages,

    //             // 🔹 SYNTHÈSE
    //             'valeur_en_litre'        => (float) $stockMatin,
    //             'entrees'                => (float) $entrees,
    //             'retour_cuve'            => (float) $retourCuve,
    //             'sorties'                => (float) $sorties,
    //             'stock_theorique'        => (float) $stockTheorique,
    //             'stock_physique'         => (float) $stockPhysique,

    //             // ✅ ÉCART MÉTIER CORRECT (ligne → ligne cumulé)
    //             'ecart'                  => (float) $ecartGlobal,

    //             'pompes'                 => [],
    //         ];
    //     }

    //     return response()->json([
    //         'status' => 200,
    //         'data'   => $data,
    //     ], 200);
    // }
    public function calculerToutesCuvesEntreDates(string $dateDebut, string $dateFin)
    {
        $data = [];

        $start = Carbon::parse($dateDebut)->startOfDay();
        $end   = Carbon::parse($dateFin)->endOfDay();

        $cuves = Cuve::visible()
            ->where('status', true)
            ->with('station:id,libelle')
            ->orderBy('libelle')
            ->get();

        foreach ($cuves as $cuve) {

            /**
             * =========================
             * 🔹 APPROVISIONNEMENTS
             * =========================
             */
            $approvisionnements = ApprovisionnementCuve::visible()
                ->where('id_cuve', $cuve->id)
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($a) => [
                    'date'        => $a->created_at?->toDateString(),
                    'qte_appro'   => (float) $a->qte_appro,
                    'pu_unitaire' => (float) ($a->pu_unitaire ?? 0),
                    'type_appro'  => $a->type_appro,
                ])
                ->toArray();

            /**
             * =========================
             * 🔹 PERTES
             * =========================
             */
            $pertes = PerteCuve::visible()
                ->where('id_cuve', $cuve->id)
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($p) => [
                    'date'      => $p->created_at?->toDateString(),
                    'qte_perte' => (float) $p->qte_perte,
                    'motif'     => $p->motif,
                ])
                ->toArray();

            /**
             * =========================
             * 🔹 JAUGEAGES + ÉCART LIGNE / LIGNE
             * =========================
             */
            $jaugeagesBruts = VenteLitre::visible()
                ->where('id_cuve', $cuve->id)
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('created_at', 'asc')
                ->get();

            $jaugeages     = [];
            $ecartGlobal   = 0.0;
            $previousStock = null;

            foreach ($jaugeagesBruts as $j) {

                $stock      = (float) $j->qte_vendu;
                $ecartLigne = 0.0;

                if ($previousStock !== null) {
                    // 🔹 LOGIQUE MÉTIER : ancien - suivant
                    $ecartLigne   = $previousStock - $stock;
                    $ecartGlobal += $ecartLigne;
                }

                $jaugeages[] = [
                    'date'  => $j->created_at?->toDateString(),
                    'stock' => $stock,
                    'ecart' => (float) number_format($ecartLigne, 2, '.', ''),
                ];

                $previousStock = $stock;
            }

            // ✅ FORMAT FINAL ÉCART GLOBAL
            $ecartGlobal = number_format($ecartGlobal, 2, '.', '');

            /**
             * =========================
             * 🔹 SYNTHÈSE
             * =========================
             */
            $stockMatin = $jaugeages[0]['stock'] ?? 0;

            $entrees  = collect($approvisionnements)
                ->where('type_appro', 'approvisionnement')
                ->sum('qte_appro');

            $retourCuve = collect($approvisionnements)
                ->where('type_appro', 'retour_cuve')
                ->sum('qte_appro');

            $perteCuve = collect($pertes)->sum('qte_perte');

            $sorties = LigneVente::visible()
                ->where('id_cuve', $cuve->id)
                ->whereBetween('created_at', [$start, $end])
                ->sum('qte_vendu');

            $stockTheorique = $stockMatin + $entrees + $retourCuve - $sorties - $perteCuve;

            $stockPhysique = ! empty($jaugeages)
                ? (float) $jaugeages[count($jaugeages) - 1]['stock']
                : 0;

            /**
             * =========================
             * 🔹 STRUCTURE FINALE
             * =========================
             */
            $data[$cuve->libelle] = [
                'date'                   => $start->toDateString() . ' → ' . $end->toDateString(),

                'station'                => [
                    'id'      => $cuve->station->id,
                    'libelle' => $cuve->station->libelle,
                ],

                'cuve'                   => [
                    'id'      => $cuve->id,
                    'libelle' => $cuve->libelle,
                ],

                'approvisionnement_cuve' => $approvisionnements,
                'perte_cuve'             => $pertes,
                'jaugeage'               => $jaugeages,

                'valeur_en_litre'        => (float) $stockMatin,
                'entrees'                => (float) $entrees,
                'retour_cuve'            => (float) $retourCuve,
                'sorties'                => (float) $sorties,
                'stock_theorique'        => (float) $stockTheorique,
                'stock_physique'         => (float) $stockPhysique,

                // ✅ ÉCART FINAL FORMATÉ
                'ecart'                  => (float) $ecartGlobal,

                'pompes'                 => [],
            ];
        }

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ], 200);
    }

}
