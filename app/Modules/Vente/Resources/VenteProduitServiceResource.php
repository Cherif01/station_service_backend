<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VenteProduitServiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'qte_vendu'     => $this->qte_vendu,
            'prix_unitaire' => $this->prix_unitaire,

            // produit (flat)
            'id_produit' => $this->id_produit,
            'produit'    => $this->whenLoaded('produit', fn () => [
                'id'            => $this->produit->id,
                'libelle'       => $this->produit->libelle,
                'prix_unitaire' => $this->produit->prix_unitaire,
            ]),

            // service (flat)
            'id_service' => $this->id_service,
            'service'    => $this->whenLoaded('service', fn () => [
                'id'      => $this->service->id,
                'libelle' => $this->service->libelle,
                'prix'    => $this->service->prix,
            ]),

            'created_at' => $this->created_at,
        ];
    }
}
