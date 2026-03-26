<?php
namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\ApprovisionnementCuve;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\JaugeageCuve;
use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\PerteCuve;
use App\Modules\Vente\Resources\CuveResource;
use Carbon\Carbon;
use Exception;

class CuveService
{
    /**
     * =========================
     * LISTE DES CUVES
     * =========================
     */
    public function getAll()
    {
        try {

            $produits = Cuve::query()
                ->orderBy('libelle')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => CuveResource::collection($produits),
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
     * DÉTAIL CUVE
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $produit = Cuve::findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new CuveResource($produit),
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
     * CRÉATION CUVE
     * =========================
     */
    public function store(array $data)
    {
        try {

            $produit = Cuve::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Cuve créée avec succès.',
                'data'    => new CuveResource($produit),
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
     * MODIFICATION CUVE
     * =========================
     */
    public function update(int $id, array $data)
    {
        try {

            $produit = Cuve::findOrFail($id);
            $produit->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Cuve modifiée avec succès.',
                'data'    => new CuveResource($produit),
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
     * SUPPRESSION CUVE
     * =========================
     */
    public function delete(int $id)
    {
        try {

            $produit = Cuve::findOrFail($id);
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

    /**
     * =========================
     * CALCUL STOCK PAR CUVE
     * =========================
     */
    public function calculerParCuve(int $idCuve): array
    {
        $cuve = Cuve::find($idCuve);

        if (! $cuve) {
            return [];
        }

        $lastDate = JaugeageCuve::visible()
            ->where('id_cuve', $idCuve)
            ->orderByDesc('created_at')
            ->value('created_at');

        $date = $lastDate
            ? Carbon::parse($lastDate)->toDateString()
            : Carbon::today()->toDateString();

        $stockMatin = JaugeageCuve::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->orderBy('created_at')
            ->value('volume_mesure') ?? 0;

        $entrees = ApprovisionnementCuve::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->where('type_appro', 'approvisionnement')
            ->sum('qte_appro');

        $retourCuve = ApprovisionnementCuve::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->where('type_appro', 'retour_cuve')
            ->sum('qte_appro');

        $sorties = LigneVente::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->sum('qte_vendu');

        $stockTheorique = $stockMatin + $entrees + $retourCuve - $sorties;

        $stockPhysique = JaugeageCuve::visible()
            ->where('id_cuve', $idCuve)
            ->whereDate('created_at', $date)
            ->orderByDesc('created_at')
            ->value('volume_mesure') ?? 0;

        $ecart = $stockPhysique - $stockTheorique;

        return [
            'date'            => $date,

            'cuve'            => [
                'id'       => $cuve->id,
                'libelle'  => $cuve->libelle,
                'pu_vente' => (float) $cuve->pu_vente,
            ],

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
     * =========================
     * STOCK DE TOUTES LES CUVES
     * =========================
     */
    // public function calculerToutesCuves()
    // {
    //     $data = [];

    //     $cuves = Cuve::query()
    //         ->orderBy('libelle')
    //         ->get();

    //     foreach ($cuves as $cuve) {

    //         $stockTheorique = LigneVente::visible()
    //             ->where('id_cuve', $cuve->id)
    //             ->sum('qte_vendu');

    //         $stockPhysique = JaugeageCuve::visible()
    //             ->where('id_cuve', $cuve->id)
    //             ->orderByDesc('created_at')
    //             ->value('volume_mesure') ?? 0;

    //         $ecart = $stockPhysique - $stockTheorique;

    //         $data[] = [
    //             'cuve' => [
    //                 'id'       => $cuve->id,
    //                 'libelle'  => $cuve->libelle,
    //                 'pu_vente' => (float) $cuve->pu_vente,
    //             ],

    //             'stock_theorique' => (float) $stockTheorique,
    //             'stock_physique'  => (float) $stockPhysique,
    //             'ecart'           => (float) $ecart,
    //         ];
    //     }

    //     return response()->json([
    //         'status' => 200,
    //         'data'   => $data,
    //     ]);
    // }

    // /**
    //  * =========================
    //  * STOCK ENTRE DEUX DATES
    //  * =========================
    //  */
    // public function calculerToutesCuvesEntreDates(string $dateDebut, string $dateFin)
    // {
    //     $data = [];

    //     $start = Carbon::parse($dateDebut)->startOfDay();
    //     $end   = Carbon::parse($dateFin)->endOfDay();

    //     $cuves = Cuve::query()
    //         ->orderBy('libelle')
    //         ->get();

    //     foreach ($cuves as $cuve) {

    //         $stockTheorique = LigneVente::visible()
    //             ->where('id_cuve', $cuve->id)
    //             ->whereBetween('created_at', [$start, $end])
    //             ->sum('qte_vendu');

    //         $stockPhysique = JaugeageCuve::visible()
    //             ->where('id_cuve', $cuve->id)
    //             ->whereBetween('created_at', [$start, $end])
    //             ->orderByDesc('created_at')
    //             ->value('volume_mesure') ?? 0;

    //         $ecart = $stockPhysique - $stockTheorique;

    //         $data[] = [
    //             'date' => $start->toDateString() . ' → ' . $end->toDateString(),

    //             'cuve' => [
    //                 'id'       => $cuve->id,
    //                 'libelle'  => $cuve->libelle,
    //                 'pu_vente' => (float) $cuve->pu_vente,
    //             ],

    //             'stock_theorique' => (float) $stockTheorique,
    //             'stock_physique'  => (float) $stockPhysique,
    //             'ecart'           => (float) $ecart,
    //         ];
    //     }

    //     return response()->json([
    //         'status' => 200,
    //         'data'   => $data,
    //     ]);
    // }

    /**
     * =========================
     * STOCK JOURNALIER PAR CUVE
     * (toutes les cuves pour une date)
     * =========================
     */
/**
 * =========================
 * STOCK ENTRE DEUX DATES
 * (toutes les cuves)
 * =========================
 */
/**
 * =========================
 * STOCK JOUR PAR JOUR
 * ENTRE DEUX DATES
 * =========================
 */
    public function calculerStockEntreDates(?string $dateDebut = null, ?string $dateFin = null)
    {
        try {

            $debut = $dateDebut
                ? Carbon::parse($dateDebut)->startOfDay()
                : Carbon::today()->startOfDay();

            $fin = $dateFin
                ? Carbon::parse($dateFin)->endOfDay()
                : Carbon::today()->endOfDay();

            $cuves = Cuve::query()
                ->orderBy('libelle')
                ->get();

            $data = [];

            // On itère jour par jour
            $current = $debut->copy();

            while ($current->lte($fin)) {

                $date        = $current->toDateString();
                $lignesCuves = [];

                foreach ($cuves as $cuve) {

                    $stockDebut = JaugeageCuve::visible()
                        ->where('id_cuve', $cuve->id)
                        ->whereDate('created_at', $date)
                        ->orderBy('created_at')
                        ->value('volume_mesure');

                    if (is_null($stockDebut)) {
                        $stockDebut = JaugeageCuve::visible()
                            ->where('id_cuve', $cuve->id)
                            ->where('created_at', '<', $current->copy()->startOfDay())
                            ->orderByDesc('created_at')
                            ->value('volume_mesure') ?? 0;
                    }

                    $entrees = ApprovisionnementCuve::visible()
                        ->where('id_cuve', $cuve->id)
                        ->whereDate('created_at', $date)
                        ->where('type_appro', 'approvisionnement')
                        ->sum('qte_appro');

                    $retourCuve = LigneVente::visible()
                        ->where('id_cuve', $cuve->id)
                        ->whereDate('created_at', $date)
                        ->where('status', true)
                        ->sum('retour_cuve');

                    $sorties = LigneVente::visible()
                        ->where('id_cuve', $cuve->id)
                        ->whereDate('created_at', $date)
                        ->where('status', true)
                        ->sum('qte_vendu');

                    $perteCuve = PerteCuve::visible()
                        ->where('id_cuve', $cuve->id)
                        ->whereDate('created_at', $date)
                        ->sum('quantite_perdue');

                    $stockFinTheorique = $entrees + $retourCuve - $sorties - $perteCuve;

                    $stockJauge = JaugeageCuve::visible()
                        ->where('id_cuve', $cuve->id)
                        ->whereDate('created_at', $date)
                        ->orderByDesc('created_at')
                        ->value('volume_mesure');

                    if (is_null($stockJauge)) {
                        $stockJauge = JaugeageCuve::visible()
                            ->where('id_cuve', $cuve->id)
                            ->orderByDesc('created_at')
                            ->value('volume_mesure') ?? 0;
                    }

                    $ecart = $stockJauge - ($stockDebut + $stockFinTheorique);

                    $lignesCuves[] = [
                        'carburant'       => [
                            'id'       => $cuve->id,
                            'libelle'  => $cuve->libelle,
                            'pu_vente' => (float) $cuve->pu_vente,
                        ],
                        'stock_debut'     => (float) $stockDebut,
                        'entrees'         => (float) $entrees,
                        'retour_cuve'     => (float) $retourCuve,
                        'sorties'         => (float) $sorties,
                        'perte_cuve'      => (float) $perteCuve,
                        'stock_theorique' => (float) $stockFinTheorique,
                        'stock_jauge'     => (float) $stockJauge,
                        'ecart'           => (float) $ecart,
                    ];
                }

                $data[] = [
                    'date'  => $date,
                    'cuves' => $lignesCuves,
                ];

                $current->addDay();
            }

            return response()->json([
                'status'     => 200,
                'date_debut' => $debut->toDateString(),
                'date_fin'   => $fin->toDateString(),
                'data'       => $data,
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors du calcul du stock.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
