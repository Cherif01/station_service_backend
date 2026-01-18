<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'montant_payer' => $this->montant_payer,
            'mode_paiement' => $this->mode_paiement,

            'id_init_vente' => $this->id_init_vente,

            // vente (léger)
            'vente' => $this->whenLoaded('vente', fn () => [
                'id'        => $this->vente->id,
                'reference' => $this->vente->reference,
            ]),

            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
        ];
    }
}
