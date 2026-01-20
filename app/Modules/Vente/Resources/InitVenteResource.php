<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Vente\Services\InitVenteService;

class InitVenteResource extends JsonResource
{
    public function toArray($request): array
    {
        /**
         * =================================================
         * 🔹 ÉTAT MÉTIER CENTRAL
         * =================================================
         */
        $etatVente = app(InitVenteService::class)->getEtatVente($this->id);

        return [
            // =============================
            // 🔹 IDENTITÉ
            // =============================
            'id'        => $this->id,
            'reference' => $this->reference,
            'status'    => $this->status,

            // =============================
            // 🔹 CLIENT
            // =============================
            'client' => $etatVente['client'] ?? null,

            // =============================
            // 🔹 PRODUITS & SERVICES
            // =============================
            'elements' => $etatVente['elements'] ?? [],

            // =============================
            // 🔹 FACTURATION (SYNTHÈSE)
            // =============================
            'facturation' => $etatVente['facturation'] ?? [
                'total_facture' => 0,
                'total_paye'    => 0,
                'reste_a_payer' => 0,
                'etat'          => 'non_paye',
            ],

            // =============================
            // 🔹 PAIEMENTS (DÉTAIL)
            // =============================
            'paiements' => $this->whenLoaded('paiements', function () {
                return $this->paiements->map(fn ($p) => [
                    'id'            => $p->id,
                    'montant_payer' => (float) $p->montant_payer,
                    'mode_paiement' => $p->mode_paiement,
                    'created_by'    => $p->createdBy?->name,
                    'created_at'    => $p->created_at?->toDateTimeString(),
                ]);
            }),

            // =============================
            // 🔹 AUDIT
            // =============================
            'created_by' => $this->whenLoaded(
                'createdBy',
                fn () => $this->createdBy->name
            ),

            'modify_by' => $this->whenLoaded(
                'modifiedBy',
                fn () => $this->modifiedBy?->name
            ),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
