<?php
namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\InitVente;
use App\Modules\Vente\Resources\InitVenteResource;
use Illuminate\Support\Facades\DB;

class InitVenteService
{
    public function index()
    {
        $ventes = InitVente::visible()
            ->with([
                'client',
                'lignes.produit',
                'lignes.service',
                'paiements',
                'createdBy',
                'modifiedBy',
            ])
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => InitVenteResource::collection($ventes),
        ], 200);
    }

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
                'message' => 'Vente introuvable',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => new InitVenteResource($vente),
        ], 200);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {

            $vente = InitVente::create($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente initialisée',
                'data'    => new InitVenteResource(
                    $vente->load(['client', 'createdBy'])
                ),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur création vente',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(int $id, array $data)
    {
        DB::beginTransaction();
        try {

            $vente = InitVente::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $vente) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Vente introuvable',
                ], 404);
            }

            $vente->update($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente mise à jour',
                'data'    => new InitVenteResource(
                    $vente->load(['client', 'createdBy', 'modifiedBy'])
                ),
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur mise à jour vente',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * 🔹 ÉTAT DÉTAILLÉ D’UNE VENTE (SANS JSON)
     * =================================================
     */
    public function getEtatVente(int $id): array
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
            return [
                'status'      => 404,
                'message'     => 'Vente introuvable.',
                'client'      => null,
                'elements'    => [],
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
         * 2️⃣ PRODUITS & SERVICES
         * =============================================
         */
        $elements     = [];
        $totalFacture = 0;

        foreach ($vente->lignes as $ligne) {

            // 🔹 PRODUIT
            if ($ligne->produit) {

                $montant       = $ligne->qte_vendu * $ligne->prix_unitaire;
                $totalFacture += $montant;

                $elements[] = [
                    'type'          => 'produit',
                    'id'            => $ligne->produit->id,
                    'libelle'       => $ligne->produit->libelle,
                    'qte_vendu'     => $ligne->qte_vendu,
                    'prix_unitaire' => $ligne->prix_unitaire,
                    'montant'       => $montant,
                ];
            }

            // 🔹 SERVICE
            if ($ligne->service) {

                $montant       = $ligne->prix_unitaire;
                $totalFacture += $montant;

                $elements[] = [
                    'type'          => 'service',
                    'id'            => $ligne->service->id,
                    'libelle'       => $ligne->service->libelle,
                    'prix_unitaire' => $ligne->prix_unitaire,
                    'montant'       => $montant,
                ];
            }
        }

        /**
         * =============================================
         * 3️⃣ FACTURATION
         * =============================================
         */
        $totalPaye = $vente->paiements->sum('montant_payer');
        $reste     = max($totalFacture - $totalPaye, 0);

        if ($totalPaye <= 0) {
            $etat = 'non_paye';
        } elseif ($totalPaye < $totalFacture) {
            $etat = 'partiellement_paye';
        } else {
            $etat = 'totalement_paye';
        }

        return [
            'status'      => 200,

            'client'      => $client,

            'elements'    => $elements,

            'facturation' => [
                'total_facture' => $totalFacture,
                'total_paye'    => $totalPaye,
                'reste_a_payer' => $reste,
                'etat'          => $etat,
            ],
        ];
    }

}
