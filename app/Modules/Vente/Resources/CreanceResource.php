<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Vente\Services\CreanceService;

class CreanceResource extends JsonResource
{
    public function toArray($request): array
    {
        /**
         * =================================================
         * 🔹 ÉTAT MÉTIER CENTRAL
         * =================================================
         */
        $etatCreance = app(CreanceService::class)
            ->getEtatCreance($this->id);

        return [

            // =============================
            // 🔹 IDENTITÉ
            // =============================
            'id'          => $this->id,
            'commentaire' => $this->commentaire,

            // =============================
            // 🔹 CLIENT
            // =============================
            'client' => $etatCreance['client'] ?? null,

            // =============================
            // 🔹 POMPE
            // =============================
            'pompe' => $this->whenLoaded('pompe', fn () => $this->pompe ? [
                'id'        => $this->pompe->id,
                'reference' => $this->pompe->reference ?? null,
            ] : null),

            // =============================
            // 🔹 FACTURATION (SYNTHÈSE)
            // =============================
            'facturation' => $etatCreance['facturation'] ?? [
                'total_creance' => 0,
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
                fn () => $this->createdBy?->name
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
