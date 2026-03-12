<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'montant_payer' => (float) $this->montant_payer,
            'mode_paiement' => $this->mode_paiement,

            // créance (léger)
            'creance' => $this->whenLoaded('creance', fn () => $this->creance ? [
                'id'      => $this->creance->id,
                'montant' => (float) $this->creance->montant,
                'client'  => $this->creance->relationLoaded('client') ? [
                    'id'          => $this->creance->client?->id,
                    'nom_complet' => $this->creance->client?->nom_complet,
                ] : null,
            ] : null),

            'created_by' => $this->whenLoaded(
                'createdBy',
                fn () => $this->createdBy?->name
            ),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
