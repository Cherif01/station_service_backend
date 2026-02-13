<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Creance;
use App\Modules\Vente\Models\InitVente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Modules\Vente\Resources\CreanceResource;

class CreanceService
{
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
         * 1️⃣ INIT VENTE (AUTO)
         * =================================================
         */
        $initVente = InitVente::create([
            'id_client'      => $data['id_client'],
            'id_affectation' => $affectation->id,
            'status'         => false,
        ]);

        /**
         * =================================================
         * 2️⃣ CRÉATION DES CRÉANCES (TABLEAU)
         * =================================================
         */
        foreach ($data['creances'] as $item) {

            $quantite = (float) $item['quantite'];
            $prix     = (float) $item['prix_unitaire'];

            if ($quantite <= 0) {
                DB::rollBack();
                return response()->json([
                    'status'  => 400,
                    'message' => 'La quantité doit être supérieure à zéro.',
                ], 400);
            }

            Creance::create([
                'id_init_vente' => $initVente->id,
                'date'          => $item['date'] ?? null,
                'quantite'      => $quantite,
                'prix_unitaire' => $prix,
                'montant'       => $quantite * $prix,
                'commentaire'   => $item['commentaire'] ?? 'creance',
            ]);
        }

        DB::commit();

        return response()->json([
            'status'  => 200,
            'message' => 'Créances créées avec succès.',
        ], 200);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur création créance.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



public function getListeInitVentesCreance()
{
    try {

        $ventes = InitVente::visible()
            ->whereHas('creances')
            ->with([
                'client',
                'creances',
                'paiements',
                'createdBy',
                'modifiedBy',
            ])
            ->orderByDesc('id')
            ->get();

        if ($ventes->isEmpty()) {
            return response()->json([
                'status'  => 404,
                'message' => 'Aucune vente avec créance trouvée.',
                'data'    => [],
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => CreanceResource::collection($ventes),
        ], 200);

    } catch (\Throwable $e) {

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur récupération des créances.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



public function getEtatCreance(int $idInitVente): array
{
    $vente = InitVente::visible()
        ->with([
            'client',
            'creances',
            'paiements',
            'createdBy',
            'modifiedBy',
        ])
        ->find($idInitVente);

    if (! $vente) {
        return [
            'status'      => 404,
            'message'     => 'Vente introuvable.',
            'client'      => null,
            'facturation' => [],
        ];
    }

    /**
     * =============================================
     * 1️⃣ CLIENT
     * =============================================
     */
    $client = [
        'id'          => $vente->client?->id,
        'nom_complet' => $vente->client?->nom_complet,
        'telephone'   => $vente->client?->telephone,
    ];

    /**
     * =============================================
     * 2️⃣ FACTURATION
     * =============================================
     */
    $totalCreance = (float) $vente->creances->sum('montant');

    $totalPaye = (float) $vente->paiements->sum('montant_payer');

    $reste = max($totalCreance - $totalPaye, 0);

    if ($totalPaye <= 0) {
        $etat = 'non_paye';
    } elseif ($totalPaye < $totalCreance) {
        $etat = 'partiellement_paye';
    } else {
        $etat = 'totalement_paye';
    }

    return [
        'status' => 200,

        'client' => $client,

        'facturation' => [
            'total_creance' => round($totalCreance, 2),
            'total_paye'    => round($totalPaye, 2),
            'reste_a_payer' => round($reste, 2),
            'etat'          => $etat,
        ],
    ];
}



}
