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
         * 🔹 RÉCUPÉRATION DE L’ÉTAT MÉTIER
         * =================================================
         */
        $etatVente = app(InitVenteService::class)->getEtatVente($this->id);

        return [
            'id'        => $this->id,
            'reference' => $this->reference,
            'status'    => $this->status,

            // client
            'client' => $etatVente['client'] ?? null,

            // produits + services
            'elements' => $etatVente['elements'] ?? [],

            // facturation
            'facturation' => $etatVente['facturation'] ?? [
                'total_facture' => 0,
                'total_paye'    => 0,
                'reste_a_payer' => 0,
                'etat'          => 'non_paye',
            ],

            // audit (flat)
            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy->name),
            'modify_by'  => $this->whenLoaded('modifiedBy', fn () => $this->modifiedBy?->name),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
